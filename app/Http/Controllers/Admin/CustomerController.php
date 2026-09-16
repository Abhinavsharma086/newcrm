<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\GstCalculationService;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Society;
use App\Models\BurnerType;
use App\Models\MeterType;
use App\Models\Contractor;

class CustomerController extends Controller
{
    protected $gstService;

    public function __construct(GstCalculationService $gstService)
    {
        $this->gstService = $gstService;
    }

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('stage')) {
            $stage = $request->get('stage');
            if ($stage === 'Registered') {
                $query->where(function($q) {
                    $q->where('customer_stage', 'Registered')
                      ->orWhereNull('customer_stage');
                })->whereNull('lmc_date');
            } elseif ($stage === 'LMC Done') {
                $query->whereNotNull('lmc_date')->whereNull('rfc_date');
            } elseif ($stage === 'RFC Done') {
                $query->whereNotNull('rfc_date')->whereNull('conversion_date');
            } elseif ($stage === 'Converted') {
                $query->whereNotNull('conversion_date');
            } else {
                $query->where('customer_stage', $stage);
            }
        }

        $section = $request->get('section', 'registration');
        if (!in_array($section, ['registration', 'technical'])) {
            $section = 'registration';
        }

        $customers = $query->latest()->get();
        $employees = User::role('employee')->get();
        $societies = Society::orderBy('name')->get();
        return view('admin.customers.index', compact('customers', 'employees', 'societies', 'section'));
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'gstin'        => 'required|string|max:15',
            'address'      => 'nullable|string',
            'state'        => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
        ]);

        $customer = Customer::create([
            'company_name' => $validated['company_name'],
            'name'         => $validated['company_name'],
            'gstin'        => $validated['gstin'],
            'address'      => $validated['address'],
            'state'        => $validated['state'],
            'phone'        => $validated['phone'] ?? '0000000000',
            'source'       => 'manual',
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function export()
    {
        return Excel::download(new CustomersExport, 'customers_export.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $file = $request->file('file');
        $assignedTo = $request->input('assigned_to');
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            
            // Skip headers
            array_shift($rows);
            
            $successCount = 0;
            $updateCount = 0;
            
            foreach ($rows as $row) {
                $name = trim($row['D'] ?? '');
                $phone = trim($row['G'] ?? '');
                
                if (empty($name) && empty($phone)) {
                    continue;
                }
                
                $crn = trim($row['B'] ?? '');
                if (strtolower($crn) === 'none' || empty($crn)) {
                    $crn = null;
                }
                
                // Safe date parser helper
                $parseDate = function($val) {
                    if (empty($val) || strtolower($val) === 'none' || trim($val) === '') {
                        return null;
                    }
                    if (is_numeric($val)) {
                        try {
                            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                    try {
                        return date('Y-m-d', strtotime($val));
                    } catch (\Exception $e) {
                        return null;
                    }
                };

                // Create or update customer record
                $custData = [
                    'name'              => $name,
                    'phone'             => $phone,
                    'crn_no'            => $crn,
                    'sap_bp_id'         => trim($row['C'] ?? '') ?: null,
                    'society'           => trim($row['E'] ?? '') ?: null,
                    'address'           => trim($row['F'] ?? '') ?: null,
                    'city'              => trim($row['H'] ?? '') ?: 'Delhi',
                    'state'             => trim($row['I'] ?? '') ?: 'Delhi',
                    'pin'               => trim($row['J'] ?? '') ?: null,
                    'meter_no'          => trim($row['K'] ?? '') ?: null,
                    'meter_type'        => trim($row['L'] ?? '') ?: null,
                    'manufacturer'      => trim($row['M'] ?? '') ?: null,
                    'rfc_contractor'    => trim($row['N'] ?? '') ?: null,
                    'rfc_date'          => $parseDate($row['O'] ?? ''),
                    'jmr_contractor'    => trim($row['P'] ?? '') ?: null,
                    'jmr_date'          => $parseDate($row['Q'] ?? ''),
                    'mode_of_payment'   => trim($row['R'] ?? '') ?: null,
                    'payment_ref_no'    => trim($row['S'] ?? '') ?: null,
                    'payment_date'      => $parseDate($row['T'] ?? ''),
                    'reg_amount'        => floatval(trim($row['U'] ?? '0')) ?: null,
                    'burner_type'       => trim($row['V'] ?? '') ?: null,
                    'lmc_date'          => $parseDate($row['W'] ?? ''),
                    'remarks'           => trim($row['X'] ?? '') ?: null,
                    'registration_date' => $parseDate($row['Y'] ?? ''),
                    'source'            => 'manual',
                    'assigned_to'       => $assignedTo
                ];

                $existing = Customer::where('phone', $phone)->first();
                if ($existing) {
                    $existing->update($custData);
                    $updateCount++;
                } else {
                    Customer::create($custData);
                    $successCount++;
                }
            }
            
            return redirect()->route('admin.customers.index')
                ->with('success', "Import complete! Successfully imported $successCount new records and updated $updateCount existing records.");
                
        } catch (\Exception $e) {
            return redirect()->route('admin.customers.index')
                ->with('error', "Import failed: " . $e->getMessage());
        }
    }

    public function updateAssignmentSettings(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:sequence,location,workload',
        ]);
        
        \App\Models\CompanySetting::set('customer_assignment_mode', $request->input('mode'));
        
        return back()->with('success', 'Customer assignment mode updated successfully.');
    }

    public function create()
    {
        $employees   = User::role('employee')->get();
        $societies   = Society::orderBy('name')->get();
        $burnerTypes = BurnerType::where('is_active', true)->orderBy('name')->get();
        $meterTypes  = MeterType::where('is_active', true)->orderBy('name')->get();
        $contractors = Contractor::where('is_active', true)->orderBy('name')->get();
        return view('admin.customers.create', compact('employees', 'societies', 'burnerTypes', 'meterTypes', 'contractors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'nullable|email',
            'assigned_to'         => 'nullable|exists:users,id',
            'phone'               => 'required|string|max:20',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'pin'                 => 'nullable|string|max:10',
            'source'              => 'required|in:manual,whatsapp,web',
            'crn_no'              => 'nullable|string|max:100|unique:customers,crn_no',
            'sap_bp_id'           => 'nullable|string|max:100',
            'society'             => 'nullable|string|max:255',
            'gstin'               => 'nullable|string|max:15',
            'meter_no'            => 'nullable|string|max:100',
            'meter_type'          => 'nullable|string|max:100',
            'manufacturer'        => 'nullable|string|max:100',
            'contractor'          => 'nullable|string|max:100',
            'contractor_id'        => 'nullable|exists:contractors,id',
            'rfc_date'            => 'nullable|date',
            'jmr_date'            => 'nullable|date',
            'mode_of_payment'     => 'nullable|string|max:100',
            'payment_ref_no'      => 'nullable|string|max:100',
            'payment_date'        => 'nullable|date',
            'reg_amount'          => 'nullable|numeric|min:0',
            'job_card'            => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'photo'               => 'nullable|image|max:2048',
            'inside_kitchen_photo' => 'nullable|image|max:2048',
            'outside_kitchen_photo' => 'nullable|image|max:2048',
            'meter_photo_3_angles' => 'nullable|image|max:2048',
            'remarks'             => 'nullable|string',
            'registration_date'   => 'nullable|date',
            'burner_type'         => 'nullable|string|max:100',
            'lmc_date'            => 'nullable|date',
            'conversion_date'     => 'nullable|date',
            'mlc_pipe_length'     => 'nullable|numeric|min:0',
            'extra_mlc_amount'    => 'nullable|numeric|min:0',
            'mlc_pipe_no'         => 'nullable|string|max:100',
            'male_union'          => 'nullable|string|max:100',
            'female_union'        => 'nullable|string|max:100',
            'isolation_valve'     => 'nullable|string|max:100',
            'lmc_contractor'      => 'nullable|string|max:255',
            'lmc_contractor_id'   => 'nullable|exists:contractors,id',
            'customer_stage'      => 'nullable|string',
            'primary_id_number'   => 'nullable|string|max:100',
            'primary_id_file'     => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'secondary_id_type'   => 'nullable|string|max:100',
            'secondary_id_number' => 'nullable|string|max:100',
            'secondary_id_file'   => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'passport_photo'      => 'nullable|image|max:2048',
            'address_proof_file'  => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('job_card')) {
            $validated['job_card'] = $request->file('job_card')->store('job_cards', 'public');
        }
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }
        if ($request->hasFile('inside_kitchen_photo')) {
            $validated['inside_kitchen_photo'] = $request->file('inside_kitchen_photo')->store('photos/kitchen_inside', 'public');
        }
        if ($request->hasFile('outside_kitchen_photo')) {
            $validated['outside_kitchen_photo'] = $request->file('outside_kitchen_photo')->store('photos/kitchen_outside', 'public');
        }
        if ($request->hasFile('meter_photo_3_angles')) {
            $validated['meter_photo_3_angles'] = $request->file('meter_photo_3_angles')->store('photos/meter', 'public');
        }
        if ($request->hasFile('primary_id_file')) {
            $validated['primary_id_file'] = $request->file('primary_id_file')->store('kyc/primary', 'public');
        }
        if ($request->hasFile('secondary_id_file')) {
            $validated['secondary_id_file'] = $request->file('secondary_id_file')->store('kyc/secondary', 'public');
        }
        if ($request->hasFile('passport_photo')) {
            $validated['passport_photo'] = $request->file('passport_photo')->store('kyc/passport', 'public');
        }
        if ($request->hasFile('address_proof_file')) {
            $validated['address_proof_file'] = $request->file('address_proof_file')->store('kyc/address', 'public');
        }

        // Set Contractor Name directly from ID helper to prevent mismatch
        if ($request->filled('contractor_id')) {
            $validated['contractor'] = Contractor::find($request->contractor_id)->name;
        }
        if ($request->filled('lmc_contractor_id')) {
            $validated['lmc_contractor'] = Contractor::find($request->lmc_contractor_id)->name;
        }

        $customer = Customer::create($validated);
        
        // Auto Stage Calculation
        if ($request->filled('customer_stage')) {
            $customer->update(['customer_stage' => $request->customer_stage]);
        } else {
            $customer->update(['customer_stage' => $customer->auto_stage]);
        }

        return redirect()->route('admin.customers.index', ['section' => 'registration'])->with('success', 'Customer created successfully');
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $employees   = User::role('employee')->get();
        $societies   = Society::orderBy('name')->get();
        $burnerTypes = BurnerType::where('is_active', true)->orderBy('name')->get();
        $meterTypes  = MeterType::where('is_active', true)->orderBy('name')->get();
        $contractors = Contractor::where('is_active', true)->orderBy('name')->get();
        return view('admin.customers.edit', compact('customer', 'employees', 'societies', 'burnerTypes', 'meterTypes', 'contractors'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'nullable|email',
            'assigned_to'         => 'nullable|exists:users,id',
            'phone'               => 'required|string|max:20',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'pin'                 => 'nullable|string|max:10',
            'source'              => 'required|in:manual,whatsapp,web',
            'crn_no'              => 'nullable|string|max:100|unique:customers,crn_no,' . $customer->id,
            'sap_bp_id'           => 'nullable|string|max:100',
            'society'             => 'nullable|string|max:255',
            'gstin'               => 'nullable|string|max:15',
            'meter_no'            => 'nullable|string|max:100',
            'meter_type'          => 'nullable|string|max:100',
            'manufacturer'        => 'nullable|string|max:100',
            'contractor'          => 'nullable|string|max:100',
            'contractor_id'       => 'nullable|exists:contractors,id',
            'rfc_date'            => 'nullable|date',
            'jmr_date'            => 'nullable|date',
            'mode_of_payment'     => 'nullable|string|max:100',
            'payment_ref_no'      => 'nullable|string|max:100',
            'payment_date'        => 'nullable|date',
            'reg_amount'          => 'nullable|numeric|min:0',
            'job_card'            => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'photo'               => 'nullable|image|max:2048',
            'inside_kitchen_photo' => 'nullable|image|max:2048',
            'meter_photo_3_angles' => 'nullable|image|max:2048',
            'outside_kitchen_photo' => 'nullable|image|max:2048',
            'rfc_report_image'    => 'nullable|image|max:2048',
            'jmr_report_image'    => 'nullable|image|max:2048',
            'remarks'             => 'nullable|string',
            'registration_date'   => 'nullable|date',
            'burner_type'         => 'nullable|string|max:100',
            'lmc_date'            => 'nullable|date',
            'conversion_date'     => 'nullable|date',
            'mlc_pipe_length'     => 'nullable|numeric|min:0',
            'extra_mlc_amount'    => 'nullable|numeric|min:0',
            'mlc_pipe_no'         => 'nullable|string|max:100',
            'male_union'          => 'nullable|string|max:100',
            'female_union'        => 'nullable|string|max:100',
            'isolation_valve'     => 'nullable|string|max:100',
            'lmc_contractor'      => 'nullable|string|max:255',
            'lmc_contractor_id'   => 'nullable|exists:contractors,id',
            'customer_stage'      => 'nullable|string',
            'primary_id_number'   => 'nullable|string|max:100',
            'primary_id_file'     => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'secondary_id_type'   => 'nullable|string|max:100',
            'secondary_id_number' => 'nullable|string|max:100',
            'secondary_id_file'   => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'passport_photo'      => 'nullable|image|max:2048',
            'address_proof_file'  => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('job_card')) {
            $validated['job_card'] = $request->file('job_card')->store('job_cards', 'public');
        }
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }
        if ($request->hasFile('inside_kitchen_photo')) {
            $validated['inside_kitchen_photo'] = $request->file('inside_kitchen_photo')->store('photos/kitchen_inside', 'public');
        }
        if ($request->hasFile('meter_photo_3_angles')) {
            $validated['meter_photo_3_angles'] = $request->file('meter_photo_3_angles')->store('photos/meter', 'public');
        }
        if ($request->hasFile('outside_kitchen_photo')) {
            $validated['outside_kitchen_photo'] = $request->file('outside_kitchen_photo')->store('photos/kitchen_outside', 'public');
        }
        if ($request->hasFile('rfc_report_image')) {
            $validated['rfc_report_image'] = $request->file('rfc_report_image')->store('photos/rfc_reports', 'public');
        }
        if ($request->hasFile('jmr_report_image')) {
            $validated['jmr_report_image'] = $request->file('jmr_report_image')->store('photos/jmr_reports', 'public');
        }
        if ($request->hasFile('primary_id_file')) {
            $validated['primary_id_file'] = $request->file('primary_id_file')->store('kyc/primary', 'public');
        }
        if ($request->hasFile('secondary_id_file')) {
            $validated['secondary_id_file'] = $request->file('secondary_id_file')->store('kyc/secondary', 'public');
        }
        if ($request->hasFile('passport_photo')) {
            $validated['passport_photo'] = $request->file('passport_photo')->store('kyc/passport', 'public');
        }
        if ($request->hasFile('address_proof_file')) {
            $validated['address_proof_file'] = $request->file('address_proof_file')->store('kyc/address', 'public');
        }

        // Set Contractor Name directly from ID helper to prevent mismatch
        if ($request->filled('contractor_id')) {
            $validated['contractor'] = Contractor::find($request->contractor_id)->name;
        }
        if ($request->filled('lmc_contractor_id')) {
            $validated['lmc_contractor'] = Contractor::find($request->lmc_contractor_id)->name;
        }

        $customer->update($validated);
        
        // Auto Stage Calculation
        if ($request->filled('customer_stage')) {
            $customer->update(['customer_stage' => $request->customer_stage]);
        } else {
            $customer->update(['customer_stage' => $customer->auto_stage]);
        }

        $section = $request->get('section', 'registration');
        return redirect()->route('admin.customers.index', ['section' => $section])->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        $section = request('section', 'registration');
        return redirect()->route('admin.customers.index', ['section' => $section])->with('success', 'Customer deleted successfully');
    }
}
