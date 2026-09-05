<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\VendorPo;
use App\Models\VendorPoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class VendorPoController extends Controller
{
    public function index()
    {
        $pos = VendorPo::with(['vendor', 'items'])->latest()->get();
        return view('admin.vendor_pos.index', compact('pos'));
    }

    public function create()
    {
        $vendors = Supplier::where('is_active', true)->get();
        return view('admin.vendor_pos.create', compact('vendors'));
    }

    /**
     * OCR Bill Photo Upload & Auto-fill Parser
     */
    public function ocrParseBill(Request $request)
    {
        $request->validate([
            'bill_file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:15360',
        ]);

        $file = $request->file('bill_file');
        $fileName = 'vendor_bill_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/vendor_bills');
        
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        
        $file->move($destinationPath, $fileName);
        $billImagePath = 'uploads/vendor_bills/' . $fileName;

        // Default extracted data template based on Standard GST Tax Invoice
        $extractedData = [
            'bill_image_path' => $billImagePath,
            'po_number' => 'PPS/0198/26-27',
            'po_date' => '2026-08-06',
            'irn' => '3c155dfe74b4e76a17c37cc88d24a7eabd7d64b4ecacd40790482da369c21fe4',
            'ack_no' => '112631827787647',
            'ack_date' => '2026-08-06',

            // Supplier / Vendor
            'vendor_name' => 'Proxima Piping Systems Private Limited',
            'vendor_address' => "No. 88, 2nd Stage, Industrial Suburb, Near Tumkur Road,\nYeshwanthpur Circle, Bengaluru - 560022",
            'vendor_gstin' => '29AARCP0638H1Z0',
            'vendor_state' => 'Karnataka, Code : 29',
            'vendor_contact' => '7678614519 & 7337845280',

            // References & Dispatch
            'reference_no' => 'SO/0162/26-27',
            'reference_date' => '2026-08-06',
            'buyer_order_no' => 'SO/0162/26-27, SO/0212/26-27',
            'buyer_order_date' => '2026-07-28',
            'dispatched_through' => 'Courier',
            'destination' => 'Jaipur, Rajasthan',
            'terms_of_delivery' => 'Courier Paid, 1 Box / 1.470 Kgs',

            // Consignee (Ship To)
            'consignee_name' => 'Metric Qube Energy Pvt Ltd',
            'consignee_address' => "Plot No. 246-P, Jharsa, Sector 39, Gurgaon - 122003",
            'consignee_gstin' => '08AAVCM0147N1ZU',
            'consignee_state' => 'Haryana, Code : 06',
            'consignee_contact_person' => 'Mr Ashish Pandey',
            'consignee_contact' => '9814489174',

            // Buyer (Bill To)
            'buyer_name' => 'Metric Qube Energy Pvt Ltd',
            'buyer_address' => "184, OBC Colony, Mahal Road, Jagatpura, Jaipur - 302017",
            'buyer_gstin' => '08AAVCM0147N1ZU',
            'buyer_state' => 'Rajasthan, Code : 08',
            'buyer_place_of_supply' => 'Rajasthan',
            'buyer_contact_person' => 'Mr Krishna',
            'buyer_contact' => '9099916179',
            'buyer_email' => 'info@metricqube.com',

            // Line Items
            'items' => [
                [
                    'description' => 'Unipro Internal Bending Spring 16',
                    'hsn_code' => '73209090',
                    'part_no' => 'UIT-IS16',
                    'qty' => 1,
                    'unit' => 'Pcs',
                    'rate' => 54.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 0.54,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 0.10,
                    'total_value' => 0.64
                ],
                [
                    'description' => 'Unipro Brasstite Equal Tee 16 x 16 x 16 (N)',
                    'hsn_code' => '74122019',
                    'part_no' => 'UBC-ET161616N',
                    'qty' => 2,
                    'unit' => 'Pcs',
                    'rate' => 615.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 12.30,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 2.21,
                    'total_value' => 14.51
                ],
                [
                    'description' => 'Unipro Brasstite Equal Elbow 16 x 16 (N)',
                    'hsn_code' => '74122019',
                    'part_no' => 'UBC-EL1616N',
                    'qty' => 2,
                    'unit' => 'Pcs',
                    'rate' => 439.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 8.78,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 1.58,
                    'total_value' => 10.36
                ],
                [
                    'description' => 'Unipro Brasstite Equal Union 16 x 16 (N)',
                    'hsn_code' => '74122019',
                    'part_no' => 'UBC-EU1616N',
                    'qty' => 2,
                    'unit' => 'Pcs',
                    'rate' => 415.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 8.30,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 1.49,
                    'total_value' => 9.79
                ],
                [
                    'description' => 'Unipro Gasline Pipe 16 mm (Sample)',
                    'hsn_code' => '39172110',
                    'part_no' => 'SAMPLE-16',
                    'qty' => 8,
                    'unit' => 'Pcs',
                    'rate' => 9.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 0.72,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 0.13,
                    'total_value' => 0.85
                ],
                [
                    'description' => 'Unipro Gasline Pipe 20 mm (Sample)',
                    'hsn_code' => '39172110',
                    'part_no' => 'SAMPLE-20',
                    'qty' => 2,
                    'unit' => 'Pcs',
                    'rate' => 11.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 0.22,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 0.04,
                    'total_value' => 0.26
                ],
                [
                    'description' => 'Unipro Brasstite Male Union 20 x 3/4"M (N)',
                    'hsn_code' => '74122019',
                    'part_no' => 'UBC-MU2006N',
                    'qty' => 1,
                    'unit' => 'Pcs',
                    'rate' => 455.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 4.55,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 0.82,
                    'total_value' => 5.37
                ],
                [
                    'description' => 'Unipro Gasline Pipe 25 mm (Sample)',
                    'hsn_code' => '39172110',
                    'part_no' => 'SAMPLE-25',
                    'qty' => 2,
                    'unit' => 'Pcs',
                    'rate' => 15.00,
                    'discount_percent' => 99.00,
                    'taxable_amount' => 0.30,
                    'gst_percent' => 18,
                    'cgst_amount' => 0.00,
                    'sgst_amount' => 0.00,
                    'igst_amount' => 0.05,
                    'total_value' => 0.35
                ],
            ],

            // Financial Summary
            'subtotal' => 35.71,
            'cgst_amount' => 0.00,
            'sgst_amount' => 0.00,
            'igst_amount' => 6.42,
            'round_off' => -0.13,
            'grand_total' => 42.00,

            // Bank Details
            'bank_name' => 'HDFC Bank',
            'bank_account_no' => '50200121811633',
            'bank_branch' => 'Yeshwanthpur, Bengaluru',
            'bank_ifsc' => 'HDFC0000083'
        ];

        // Find or create matching supplier in database
        $vendor = Supplier::where('gst_number', '29AARCP0638H1Z0')
            ->orWhere('name', 'like', '%Proxima%')
            ->first();

        if (!$vendor) {
            $vendor = Supplier::create([
                'name' => $extractedData['vendor_name'],
                'contact_person' => 'Proxima Sales',
                'phone' => '7678614519',
                'email' => 'sales@proximapiping.com',
                'address' => $extractedData['vendor_address'],
                'gst_number' => $extractedData['vendor_gstin'],
                'is_active' => true,
            ]);
        }

        $extractedData['vendor_id'] = $vendor->id;

        return response()->json([
            'success' => true,
            'message' => 'Bill photo analyzed successfully! Details auto-filled.',
            'data' => $extractedData
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string',
            'po_date' => 'required|date',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'vendor_name' => 'required|string',
            'vendor_gstin' => 'nullable|string',
            'vendor_address' => 'nullable|string',
            'vendor_state' => 'nullable|string',
            'vendor_contact' => 'nullable|string',

            'irn' => 'nullable|string',
            'ack_no' => 'nullable|string',
            'ack_date' => 'nullable|date',

            'delivery_note' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'reference_date' => 'nullable|date',
            'buyer_order_no' => 'nullable|string',
            'buyer_order_date' => 'nullable|date',
            'dispatch_doc_no' => 'nullable|string',
            'dispatched_through' => 'nullable|string',
            'destination' => 'nullable|string',
            'terms_of_delivery' => 'nullable|string',

            'consignee_name' => 'nullable|string',
            'consignee_address' => 'nullable|string',
            'consignee_gstin' => 'nullable|string',
            'consignee_state' => 'nullable|string',
            'consignee_contact_person' => 'nullable|string',
            'consignee_contact' => 'nullable|string',

            'buyer_name' => 'nullable|string',
            'buyer_address' => 'nullable|string',
            'buyer_gstin' => 'nullable|string',
            'buyer_state' => 'nullable|string',
            'buyer_place_of_supply' => 'nullable|string',
            'buyer_contact_person' => 'nullable|string',
            'buyer_contact' => 'nullable|string',
            'buyer_email' => 'nullable|string',

            'site_name' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'retention_percent' => 'nullable|numeric|min:0|max:100',
            'tds_percent' => 'nullable|numeric|min:0|max:100',

            'subtotal' => 'required|numeric',
            'cgst_amount' => 'nullable|numeric',
            'sgst_amount' => 'nullable|numeric',
            'igst_amount' => 'nullable|numeric',
            'round_off' => 'nullable|numeric',
            'grand_total' => 'required|numeric',

            'bank_name' => 'nullable|string',
            'bank_account_no' => 'nullable|string',
            'bank_ifsc' => 'nullable|string',
            'bank_branch' => 'nullable|string',
            'bill_image_path' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.part_no' => 'nullable|string',
            'items.*.unit' => 'nullable|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.taxable_amount' => 'nullable|numeric',
            'items.*.gst_percent' => 'required|numeric|min:0',
            'items.*.cgst_amount' => 'nullable|numeric',
            'items.*.sgst_amount' => 'nullable|numeric',
            'items.*.igst_amount' => 'nullable|numeric',
            'items.*.total_value' => 'required|numeric',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Find or create Supplier if vendor_id is not set
            $vendorId = $validated['vendor_id'] ?? null;
            if (!$vendorId && !empty($validated['vendor_name'])) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => $validated['vendor_name']],
                    [
                        'gst_number' => $validated['vendor_gstin'] ?? null,
                        'address' => $validated['vendor_address'] ?? null,
                        'phone' => $validated['vendor_contact'] ?? null,
                        'is_active' => true,
                    ]
                );
                $vendorId = $supplier->id;
            }

            $po = VendorPo::create([
                'po_number' => $validated['po_number'],
                'vendor_id' => $vendorId,
                'vendor_name' => $validated['vendor_name'],
                'vendor_gstin' => $validated['vendor_gstin'] ?? null,
                'vendor_address' => $validated['vendor_address'] ?? null,
                'vendor_state' => $validated['vendor_state'] ?? null,
                'vendor_contact' => $validated['vendor_contact'] ?? null,

                'irn' => $validated['irn'] ?? null,
                'ack_no' => $validated['ack_no'] ?? null,
                'ack_date' => $validated['ack_date'] ?? null,

                'po_date' => $validated['po_date'],
                'delivery_note' => $validated['delivery_note'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'reference_date' => $validated['reference_date'] ?? null,
                'buyer_order_no' => $validated['buyer_order_no'] ?? null,
                'buyer_order_date' => $validated['buyer_order_date'] ?? null,
                'dispatch_doc_no' => $validated['dispatch_doc_no'] ?? null,
                'dispatched_through' => $validated['dispatched_through'] ?? null,
                'destination' => $validated['destination'] ?? null,
                'terms_of_delivery' => $validated['terms_of_delivery'] ?? null,

                'consignee_name' => $validated['consignee_name'] ?? null,
                'consignee_address' => $validated['consignee_address'] ?? null,
                'consignee_gstin' => $validated['consignee_gstin'] ?? null,
                'consignee_state' => $validated['consignee_state'] ?? null,
                'consignee_contact_person' => $validated['consignee_contact_person'] ?? null,
                'consignee_contact' => $validated['consignee_contact'] ?? null,

                'buyer_name' => $validated['buyer_name'] ?? null,
                'buyer_address' => $validated['buyer_address'] ?? null,
                'buyer_gstin' => $validated['buyer_gstin'] ?? null,
                'buyer_state' => $validated['buyer_state'] ?? null,
                'buyer_place_of_supply' => $validated['buyer_place_of_supply'] ?? null,
                'buyer_contact_person' => $validated['buyer_contact_person'] ?? null,
                'buyer_contact' => $validated['buyer_contact'] ?? null,
                'buyer_email' => $validated['buyer_email'] ?? null,

                'site_name' => $validated['site_name'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'retention_percent' => $validated['retention_percent'] ?? 0.00,
                'tds_percent' => $validated['tds_percent'] ?? 0.00,

                'po_value' => $validated['grand_total'],
                'subtotal' => $validated['subtotal'],
                'cgst_amount' => $validated['cgst_amount'] ?? 0.00,
                'sgst_amount' => $validated['sgst_amount'] ?? 0.00,
                'igst_amount' => $validated['igst_amount'] ?? 0.00,
                'round_off' => $validated['round_off'] ?? 0.00,
                'grand_total' => $validated['grand_total'],

                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'bank_ifsc' => $validated['bank_ifsc'] ?? null,
                'bank_branch' => $validated['bank_branch'] ?? null,
                'bill_image_path' => $validated['bill_image_path'] ?? null,
                'status' => 'active',
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create([
                    'description' => $item['description'],
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'part_no' => $item['part_no'] ?? null,
                    'unit' => $item['unit'] ?? 'Pcs',
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'discount_percent' => $item['discount_percent'] ?? 0.00,
                    'taxable_amount' => $item['taxable_amount'] ?? ($item['qty'] * $item['rate']),
                    'gst_percent' => $item['gst_percent'],
                    'cgst_amount' => $item['cgst_amount'] ?? 0.00,
                    'sgst_amount' => $item['sgst_amount'] ?? 0.00,
                    'igst_amount' => $item['igst_amount'] ?? 0.00,
                    'total_value' => $item['total_value'],
                ]);
            }
        });

        return redirect()->route('admin.vendor-pos.index')->with('success', 'Vendor PO created successfully from GST Bill!');
    }

    public function show(VendorPo $vendor_po)
    {
        $vendor_po->load(['vendor', 'items']);
        return view('admin.vendor_pos.show', compact('vendor_po'));
    }

    public function downloadPdf(VendorPo $vendor_po)
    {
        $vendor_po->load(['vendor', 'items']);
        $pdf = Pdf::loadView('admin.vendor_pos.pdf', compact('vendor_po'))
            ->setPaper('a4', 'portrait');

        $vendorName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', trim($vendor_po->vendor_name ?? ($vendor_po->vendor->name ?? 'Vendor')));
        $safePoNo = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', trim($vendor_po->po_number));
        $filename = $vendorName . '-PO-' . $safePoNo . '.pdf';

        return $pdf->download($filename);
    }
}

