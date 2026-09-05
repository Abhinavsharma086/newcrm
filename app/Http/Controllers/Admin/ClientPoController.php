<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPo;
use App\Models\ClientPoItem;
use App\Models\ProgressEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientPoController extends Controller
{
    public function index()
    {
        $pos = ClientPo::with('client')->latest()->get();
        return view('admin.client_pos.index', compact('pos'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        return view('admin.client_pos.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|unique:client_pos,po_number',
            'client_id' => 'required|exists:clients,id',
            'site_name' => 'nullable|string',
            'po_date' => 'required|date',
            'payment_terms' => 'nullable|string',
            'retention_percent' => 'nullable|numeric|min:0|max:100',
            'gstin' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.unit' => 'nullable|string',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $poValue = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['qty'] * $item['rate'];
                $taxAmount = $lineTotal * ($item['gst_percent'] / 100);
                $totalValue = $lineTotal + $taxAmount;
                $poValue += $totalValue;

                $itemsData[] = [
                    'description' => $item['description'],
                    'hsn_code' => $item['hsn_code'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'qty' => $item['qty'],
                    'rate' => $item['rate'],
                    'gst_percent' => $item['gst_percent'],
                    'total_value' => $totalValue,
                ];
            }

            $po = ClientPo::create([
                'po_number' => $validated['po_number'],
                'client_id' => $validated['client_id'],
                'site_name' => $validated['site_name'] ?? null,
                'po_date' => $validated['po_date'],
                'po_value' => $poValue,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'retention_percent' => $validated['retention_percent'] ?? 0.00,
                'gstin' => $validated['gstin'] ?? null,
                'status' => 'active',
            ]);

            foreach ($itemsData as $itemData) {
                $po->items()->create($itemData);
            }
        });

        return redirect()->route('admin.client-pos.index')->with('success', 'Client PO created successfully.');
    }

    public function show(Request $request, ClientPo $client_po)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $client_po->load(['client', 'items']);

        $progressQuery = ProgressEntry::where('client_po_id', $client_po->id)
            ->with(['item', 'user'])
            ->latest();

        if ($startDate) {
            $progressQuery->where('entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $progressQuery->where('entry_date', '<=', $endDate);
        }

        $progressEntries = $progressQuery->get();

        return view('admin.client_pos.show', compact('client_po', 'progressEntries', 'startDate', 'endDate'));
    }

    public function addProgress(Request $request, ClientPo $client_po)
    {
        $validated = $request->validate([
            'client_po_item_id' => 'required|exists:client_po_items,id',
            'entry_date' => 'required|date',
            'executed_qty' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        ProgressEntry::create([
            'client_po_id' => $client_po->id,
            'client_po_item_id' => $validated['client_po_item_id'],
            'entry_date' => $validated['entry_date'],
            'executed_qty' => $validated['executed_qty'],
            'entry_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.client-pos.show', $client_po)->with('success', 'Daily Progress quantity logged successfully.');
    }
}
