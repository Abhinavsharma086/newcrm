<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Client;
use App\Models\MaterialLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('stockTransactions')->get();
        $lowStockProducts = Product::lowStock()->get();
        $warehouses = Warehouse::all();
        $totalStockValue = Product::sum(DB::raw('current_stock * price'));
        
        return view('admin.inventory.index', compact('products', 'lowStockProducts', 'warehouses', 'totalStockValue'));
    }

    public function logs(Request $request)
    {
        $type = $request->get('type', 'inward');
        $logs = MaterialLog::where('log_type', $type)->with('product', 'supplier', 'client', 'warehouse')->latest()->get();
        $products = Product::all();
        $suppliers = Supplier::where('is_active', true)->get();
        $clients = Client::where('is_active', true)->get();
        $warehouses = Warehouse::all();
        
        return view('admin.inventory.logs', compact('logs', 'type', 'products', 'suppliers', 'clients', 'warehouses'));
    }

    public function storeLog(Request $request)
    {
        $validated = $request->validate([
            'log_date'            => 'required|date',
            'log_type'            => 'required|in:inward,outward',
            'product_id'          => 'required|exists:products,id',
            'qty'                 => 'required|numeric|min:0.01',
            'unit_rate'           => 'nullable|numeric|min:0',
            'material_type'       => 'required|string',
            'supplied_against'    => 'nullable|string',
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'client_id'           => 'nullable|exists:clients,id',
            'meter_no'            => 'nullable|string',
            'wo_invoice'          => 'nullable|string',
            'ordered_or_consumed' => 'nullable|string',
            'store_name'          => 'nullable|string',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
        ]);

        $product = Product::find($validated['product_id']);
        $validated['material_code'] = $product->material_code;
        $validated['material_description'] = $product->name;
        $validated['uom'] = $product->unit;

        if (!empty($validated['supplier_id'])) {
            $validated['supplier_name'] = optional(Supplier::find($validated['supplier_id']))->name;
        }
        if (!empty($validated['client_id'])) {
            $validated['client_name'] = optional(Client::find($validated['client_id']))->name;
        }

        // Adjust actual product current_stock
        DB::transaction(function () use ($validated, $product) {
            MaterialLog::create($validated);
            if ($validated['log_type'] === 'inward') {
                $product->increment('current_stock', $validated['qty']);
            } else {
                $product->decrement('current_stock', $validated['qty']);
            }
        });

        return redirect()->route('admin.inventory.logs', ['type' => $validated['log_type']])
                         ->with('success', 'Material log added successfully and stock updated.');
    }

    public function updateLog(Request $request, MaterialLog $log)
    {
        $validated = $request->validate([
            'log_date'            => 'required|date',
            'product_id'          => 'required|exists:products,id',
            'qty'                 => 'required|numeric|min:0.01',
            'unit_rate'           => 'nullable|numeric|min:0',
            'material_type'       => 'required|string',
            'supplied_against'    => 'nullable|string',
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'client_id'           => 'nullable|exists:clients,id',
            'meter_no'            => 'nullable|string',
            'wo_invoice'          => 'nullable|string',
            'ordered_or_consumed' => 'nullable|string',
            'store_name'          => 'nullable|string',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
        ]);

        $product = Product::find($validated['product_id']);
        $validated['material_code'] = $product->material_code;
        $validated['material_description'] = $product->name;
        $validated['uom'] = $product->unit;

        if (!empty($validated['supplier_id'])) {
            $validated['supplier_name'] = optional(Supplier::find($validated['supplier_id']))->name;
        }
        if (!empty($validated['client_id'])) {
            $validated['client_name'] = optional(Client::find($validated['client_id']))->name;
        }

        DB::transaction(function () use ($validated, $log, $product) {
            if ($log->product_id != $validated['product_id'] || $log->qty != $validated['qty']) {
                $oldProduct = Product::find($log->product_id);
                if ($log->log_type === 'inward') {
                    $oldProduct->decrement('current_stock', $log->qty);
                    $product->increment('current_stock', $validated['qty']);
                } else {
                    $oldProduct->increment('current_stock', $log->qty);
                    $product->decrement('current_stock', $validated['qty']);
                }
            }

            $log->update($validated);
        });

        return redirect()->route('admin.inventory.logs', ['type' => $log->log_type])
                         ->with('success', 'Material log updated successfully and stock adjusted.');
    }

    public function bulkStoreLog(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'log_type' => 'required|in:inward,outward',
        ]);

        $file = $request->file('file');
        $logType = $request->input('log_type');

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            
            // Skip header
            array_shift($rows);
            $successCount = 0;

            foreach ($rows as $row) {
                // We map from Excel columns
                // Inward headers: Inward Date | Material Code | Material Description | Unit Rate | UOM | Qty | Type | Supplied against | Supplier Name | Ordered | WO/Invoice | Store
                // Outward headers: Outward Date | Material Code | Material Description | Unit Rate | UOM | Qty | Type | Supplied against | Client Name | Consumed | WO/Invoice | Store
                $dateVal = trim($row['A'] ?? '');
                $materialCode = trim($row['B'] ?? '');
                $qty = floatval(trim($row['F'] ?? '0'));

                if (empty($dateVal) || empty($materialCode) || $qty <= 0) {
                    continue;
                }

                $product = Product::where('material_code', $materialCode)->first();
                if (!$product) {
                    continue;
                }

                $logDate = date('Y-m-d', strtotime($dateVal));
                
                $logData = [
                    'log_date'             => $logDate,
                    'log_type'             => $logType,
                    'product_id'           => $product->id,
                    'material_code'        => $product->material_code,
                    'material_description' => $product->name,
                    'uom'                  => $product->unit,
                    'qty'                  => $qty,
                    'unit_rate'            => floatval(trim($row['D'] ?? '0')) ?: null,
                    'material_type'        => trim($row['G'] ?? '') ?: ($product->inventory_type == 'purchase' ? 'Purchase' : 'Free Issue'),
                    'supplied_against'     => trim($row['H'] ?? '') ?: null,
                    'wo_invoice'           => trim($row['K'] ?? '') ?: null,
                    'store_name'           => trim($row['L'] ?? '') ?: null,
                ];

                if ($logType === 'inward') {
                    $logData['supplier_name'] = trim($row['I'] ?? '') ?: null;
                    $logData['ordered_or_consumed'] = trim($row['J'] ?? '') ?: null;
                } else {
                    $logData['client_name'] = trim($row['I'] ?? '') ?: null;
                    $logData['ordered_or_consumed'] = trim($row['J'] ?? '') ?: null;
                    // Auto resolve customer meter number if present
                    if ($logData['wo_invoice']) {
                        $customer = Customer::where('meter_no', $logData['wo_invoice'])->first();
                        if ($customer) {
                            $logData['meter_no'] = $customer->meter_no;
                        }
                    }
                }

                DB::transaction(function () use ($logData, $product) {
                    MaterialLog::create($logData);
                    if ($logData['log_type'] === 'inward') {
                        $product->increment('current_stock', $logData['qty']);
                    } else {
                        $product->decrement('current_stock', $logData['qty']);
                    }
                });

                $successCount++;
            }

            return back()->with('success', "Bulk entries completed! Successfully processed $successCount material transactions.");

        } catch (\Exception $e) {
            return back()->with('error', 'Bulk import failed: ' . $e->getMessage());
        }
    }

    public function reconciliation(Request $request)
    {
        // Get all outward logs belonging to Free Issue Material
        $logs = MaterialLog::where('log_type', 'outward')
            ->where(function($q) {
                $q->where('material_type', 'like', '%Free%')
                  ->orWhereHas('product', function($pq) {
                      $pq->where('inventory_type', 'free_issue');
                  });
            })
            ->with('product')
            ->get();

        // Group reconciliation records by Meter Number
        $grouped = $logs->groupBy('meter_no');

        return view('admin.inventory.reconciliation', compact('grouped'));
    }

    public function stockTransactions()
    {
        $transactions = StockTransaction::with('product', 'warehouse', 'creator', 'approver')
            ->latest()
            ->get();
        
        return view('admin.inventory.transactions', compact('transactions'));
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'in';
        $validated['created_by'] = auth()->id();

        StockTransaction::create($validated);

        return back()->with('success', 'Stock-in transaction created. Pending approval.');
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $product = Product::find($validated['product_id']);
        
        if ($product->current_stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock available.']);
        }

        $validated['type'] = 'out';
        $validated['created_by'] = auth()->id();

        StockTransaction::create($validated);

        return back()->with('success', 'Stock-out transaction created. Pending approval.');
    }

    public function approveTransaction(StockTransaction $transaction)
    {
        if ($transaction->approved_at) {
            return back()->with('error', 'Transaction already approved.');
        }

        DB::transaction(function () use ($transaction) {
            $product = $transaction->product;
            
            if ($transaction->type === 'in') {
                $product->increment('current_stock', $transaction->quantity);
            } else {
                if ($product->current_stock < $transaction->quantity) {
                    throw new \Exception('Insufficient stock for approval.');
                }
                $product->decrement('current_stock', $transaction->quantity);
            }

            $transaction->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', 'Transaction approved and stock updated.');
    }

    public function deleteTransaction(StockTransaction $transaction)
    {
        $transaction->delete();
        return back()->with('success', 'Transaction deleted successfully.');
    }
}
