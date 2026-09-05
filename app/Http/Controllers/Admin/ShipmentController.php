<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('invoice.customer')->latest()->get();
        return view('admin.shipments.index', compact('shipments'));
    }

    public function create()
    {
        $invoices = Invoice::whereDoesntHave('shipment')->get();
        return view('admin.shipments.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id|unique:shipments,invoice_id',
            'courier_name' => 'nullable|string|max:100',
            'tracking_no' => 'nullable|string|max:100',
            'vehicle_no' => 'nullable|string|max:50',
            'dispatch_date' => 'required|date',
            'expected_delivery' => 'nullable|date|after_or_equal:dispatch_date',
            'notes' => 'nullable|string',
        ]);

        $validated['shipment_no'] = $this->generateShipmentNumber();
        $validated['created_by'] = auth()->id();

        Shipment::create($validated);

        return redirect()->route('admin.shipments.index')->with('success', 'Shipment created successfully');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load('invoice.customer', 'invoice.items');
        return view('admin.shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        return view('admin.shipments.edit', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'courier_name' => 'nullable|string|max:100',
            'tracking_no' => 'nullable|string|max:100',
            'vehicle_no' => 'nullable|string|max:50',
            'status' => 'required|in:dispatched,transit,delivered',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'delivered' && !$shipment->delivered_at) {
            $validated['delivered_at'] = now();
        }

        $shipment->update($validated);

        return redirect()->route('admin.shipments.show', $shipment)->with('success', 'Shipment updated successfully');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();
        return redirect()->route('admin.shipments.index')->with('success', 'Shipment deleted successfully');
    }

    private function generateShipmentNumber()
    {
        $date = date('Ymd');
        $prefix = "SHIP-{$date}-";
        
        $lastShipment = Shipment::where('shipment_no', 'like', $prefix . '%')
            ->orderBy('shipment_no', 'desc')
            ->first();
        
        if ($lastShipment) {
            $lastNumber = (int) substr($lastShipment->shipment_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
