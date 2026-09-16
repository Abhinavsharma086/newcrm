<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'email', 'phone', 'gstin', 'address',
        'city', 'state', 'pin', 'source',
        'crn_no', 'sap_bp_id', 'society', 'meter_no', 'meter_type',
        'manufacturer', 'contractor', 'contractor_id',
        'rfc_contractor', 'rfc_contractor_id', 'rfc_date',
        'jmr_contractor', 'jmr_contractor_id', 'jmr_date',
        'mode_of_payment', 'payment_ref_no', 'payment_date',
        'reg_amount', 'job_card', 'photo', 'inside_kitchen_photo', 'meter_photo_3_angles',
        'outside_kitchen_photo', 'rfc_report_image', 'jmr_report_image', 'remarks', 'registration_date',
        'burner_type', 'lmc_date', 'assigned_to',
        'conversion_date', 'mlc_pipe_length', 'extra_mlc_amount', 'customer_stage',
        'mlc_pipe_no', 'male_union', 'female_union', 'isolation_valve',
        'lmc_contractor', 'lmc_contractor_id',
        'primary_id_number', 'primary_id_file', 'secondary_id_type',
        'secondary_id_number', 'secondary_id_file', 'passport_photo',
        'address_proof_file', 'lmc_id'
    ];

    protected function casts(): array
    {
        return [
            'rfc_date'          => 'date',
            'jmr_date'          => 'date',
            'payment_date'      => 'date',
            'registration_date' => 'date',
            'lmc_date'          => 'date',
            'conversion_date'   => 'date',
            'reg_amount'        => 'decimal:2',
            'mlc_pipe_length'   => 'decimal:2',
            'extra_mlc_amount'  => 'decimal:2',
        ];
    }

    /**
     * Auto-calculate customer stage based on filled dates.
     */
    public function getAutoStageAttribute(): string
    {
        if (!empty($this->attributes['conversion_date'])) return 'Converted';
        if (!empty($this->attributes['jmr_date']))        return 'JMR Done';
        if (!empty($this->attributes['rfc_date']))        return 'RFC Done';
        if (!empty($this->attributes['lmc_date']))        return 'LMC Done';
        if (!empty($this->attributes['registration_date'])) return 'Registered';
        return 'New';
    }

    /**
     * Get stage badge color.
     */
    public function getStageBadgeColorAttribute(): string
    {
        return match ($this->auto_stage) {
            'Converted'  => 'success',
            'JMR Done'   => 'info',
            'RFC Done'   => 'primary',
            'LMC Done'   => 'warning',
            'Registered' => 'secondary',
            default      => 'light',
        };
    }

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($customer) {
            if (!empty($customer->lmc_date) && empty($customer->lmc_id)) {
                $year = $customer->lmc_date instanceof \Carbon\Carbon ? $customer->lmc_date->format('Y') : date('Y');
                $customer->lmc_id = 'LMC-' . $year . '-' . strtoupper(bin2hex(random_bytes(3)));
            }
        });

        static::creating(function ($customer) {
            if (empty($customer->assigned_to)) {
                $mode = CompanySetting::get('customer_assignment_mode', 'sequence');
                
                $employees = User::role('employee')->where('status', 'active')->orderBy('id')->get();
                if ($employees->isNotEmpty()) {
                    $assignedEmployeeId = null;
                    
                    if ($mode === 'location' && !empty($customer->society)) {
                        foreach ($employees as $employee) {
                            if (!empty($employee->assigned_societies)) {
                                $societies = array_map('trim', explode(',', strtolower($employee->assigned_societies)));
                                if (in_array(strtolower($customer->society), $societies)) {
                                    $assignedEmployeeId = $employee->id;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if ($mode === 'workload') {
                        $employeesWithTicketCount = $employees->map(function ($employee) {
                            $ticketCount = \App\Models\Ticket::where('assigned_to', $employee->id)
                                ->whereNotIn('status', ['resolved', 'closed'])
                                ->count();
                            return [
                                'id' => $employee->id,
                                'ticket_count' => $ticketCount
                            ];
                        })->sortBy('ticket_count');
                        
                        $assignedEmployeeId = $employeesWithTicketCount->first()['id'];
                    }
                    
                    if (empty($assignedEmployeeId)) {
                        $lastAssignedCustomer = self::whereNotNull('assigned_to')
                            ->orderBy('id', 'desc')
                            ->first();
                        
                        $assignedEmployeeId = $employees->first()->id;
                        
                        if ($lastAssignedCustomer) {
                            $index = $employees->pluck('id')->search($lastAssignedCustomer->assigned_to);
                            if ($index !== false) {
                                $nextIndex = ($index + 1) % $employees->count();
                                $assignedEmployeeId = $employees[$nextIndex]->id;
                            }
                        }
                    }
                    
                    $customer->assigned_to = $assignedEmployeeId;
                }
            }
            
            // Auto-assign contractor based on customer's society
            if (!empty($customer->society)) {
                $societyObj = \App\Models\Society::where('name', $customer->society)->first();
                if ($societyObj && $societyObj->contractor_id) {
                    $customer->contractor_id = $societyObj->contractor_id;
                    $customer->contractor = $societyObj->contractor->name ?? null;
                }
            }
            
            if (empty($customer->customer_stage)) {
                $customer->customer_stage = $customer->auto_stage;
            }
        });

        static::created(function ($customer) {
            if ($customer->assigned_to) {
                \App\Models\Task::create([
                    'title' => 'LMC & JMR Update: ' . $customer->name,
                    'description' => 'Complete technical installation mapping, upload job card, meter photo and update details for ' . $customer->name . '.',
                    'assigned_to' => $customer->assigned_to,
                    'created_by' => auth()->id() ?? 1,
                    'status' => 'todo',
                    'priority' => 'high',
                    'due_date' => now()->addDays(7),
                    'customer_id' => $customer->id,
                ]);
            }
        });

        static::updated(function ($customer) {
            if ($customer->assigned_to) {
                // If assigned_to changed, update existing active tasks
                if ($customer->isDirty('assigned_to')) {
                    \App\Models\Task::where('customer_id', $customer->id)
                        ->whereIn('status', ['todo', 'progress', 'review'])
                        ->update([
                            'assigned_to' => $customer->assigned_to
                        ]);
                }

                // Make sure an active task ALWAYS exists if someone is assigned
                $hasActiveTask = \App\Models\Task::where('customer_id', $customer->id)
                    ->whereIn('status', ['todo', 'progress', 'review'])
                    ->exists();

                if (!$hasActiveTask) {
                    \App\Models\Task::create([
                        'title' => 'LMC & JMR Update: ' . $customer->name,
                        'description' => 'Complete technical installation mapping, upload job card, meter photo and update details for ' . $customer->name . '.',
                        'assigned_to' => $customer->assigned_to,
                        'created_by' => auth()->id() ?? 1,
                        'status' => 'todo',
                        'priority' => 'high',
                        'due_date' => now()->addDays(7),
                        'customer_id' => $customer->id,
                    ]);
                }
            }

            // Sync task completion status with customer stage conversions
            if ($customer->isDirty('conversion_date') && !empty($customer->conversion_date)) {
                \App\Models\Task::where('customer_id', $customer->id)
                    ->whereIn('status', ['todo', 'progress', 'review'])
                    ->update([
                        'status' => 'done',
                        'completed_at' => now()
                    ]);
                    
                // Auto-deduct inventory products required by the customer
                $customer->fulfillProductRequirements();
            }

            // Sync contractor assignment when society changes
            if ($customer->isDirty('society') && !empty($customer->society)) {
                $societyObj = \App\Models\Society::where('name', $customer->society)->first();
                if ($societyObj && $societyObj->contractor_id) {
                    // Update quietly to avoid loops
                    \DB::table('customers')->where('id', $customer->id)->update([
                        'contractor_id' => $societyObj->contractor_id,
                        'contractor' => $societyObj->contractor->name ?? null
                    ]);
                }
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contractorRel()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    public function rfcContractor()
    {
        return $this->belongsTo(Contractor::class, 'rfc_contractor_id');
    }

    public function jmrContractor()
    {
        return $this->belongsTo(Contractor::class, 'jmr_contractor_id');
    }

    public function lmcContractorRel()
    {
        return $this->belongsTo(Contractor::class, 'lmc_contractor_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Auto-deduct products required for customer installation
     */
    public function fulfillProductRequirements()
    {
        $requirements = [
            'mlc_pipe_length' => 'MLC Pipe',
            'male_union'      => 'Male Union',
            'female_union'    => 'Female Union',
            'isolation_valve' => 'Isolation Valve',
        ];

        foreach ($requirements as $attribute => $productName) {
            $quantity = (float) $this->{$attribute};
            if ($quantity > 0) {
                // Find or create the product if it doesn't exist
                $product = \App\Models\Product::firstOrCreate(
                    ['name' => $productName],
                    [
                        'sku' => strtoupper(str_replace(' ', '_', $productName)) . '-' . uniqid(),
                        'category' => 'Fittings',
                        'unit' => $attribute === 'mlc_pipe_length' ? 'meters' : 'pcs',
                        'type' => 'material',
                        'inventory_type' => 'inventory',
                        'current_stock' => 0,
                    ]
                );

                // Get default warehouse
                $warehouseId = \App\Models\Warehouse::first()->id ?? 1;

                // Create stock out transaction
                \App\Models\StockTransaction::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'type' => 'out',
                    'quantity' => $quantity,
                    'reference_no' => 'CUST-CONV-' . $this->id,
                    'notes' => 'Auto-deducted for customer conversion: ' . $this->name,
                    'created_by' => auth()->id() ?? 1,
                    'approved_by' => auth()->id() ?? 1,
                    'approved_at' => now(),
                ]);

                // Deduct stock
                $product->decrement('current_stock', $quantity);
            }
        }
    }
}
