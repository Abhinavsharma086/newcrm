<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $matPrefix = CompanySetting::get('material_sku_prefix', 'MAT');
        $serPrefix = CompanySetting::get('service_sku_prefix', 'SER');
        $separator = CompanySetting::get('sku_separator', '-');
        $digits = CompanySetting::get('sku_digits', 4);
        $suffix = CompanySetting::get('sku_suffix', '');
        $startNumber = CompanySetting::get('sku_start_number', 1);

        $latestProduct = Product::orderBy('id', 'desc')->first();
        $nextIdOffset = $latestProduct ? $latestProduct->id : 0;
        $runningNumber = $startNumber + $nextIdOffset;

        $paddedNumber = str_pad($runningNumber, $digits, '0', STR_PAD_LEFT);
        
        $nextMaterialSku = '';
        if ($matPrefix) $nextMaterialSku .= $matPrefix . $separator;
        $nextMaterialSku .= $paddedNumber;
        if ($suffix) $nextMaterialSku .= $separator . $suffix;

        $nextServiceSku = '';
        if ($serPrefix) $nextServiceSku .= $serPrefix . $separator;
        $nextServiceSku .= $paddedNumber;
        if ($suffix) $nextServiceSku .= $separator . $suffix;

        return view('admin.products.create', compact('nextMaterialSku', 'nextServiceSku'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'           => 'required|string|unique:products,sku',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'category'      => 'nullable|string|max:100',
            'unit'          => 'required|string|max:20',
            'type'          => 'required|in:material,service',
            'hsn_code'      => 'nullable|string|max:20',
            'material_code' => 'nullable|string|max:100',
            'inventory_type'=> 'required|in:purchase,free_issue',
            'price'         => 'required|numeric|min:0',
            'tax_rate'      => 'required|numeric|min:0|max:100',
            'reorder_level' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'image'         => 'nullable|image|max:2048',
        ]);

        $productData = $validated;
        unset($productData['image']);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $productData['image_path'] = 'uploads/products/' . $filename;
        }

        Product::create($productData);

        return redirect()->route('admin.products.index')->with('success', 'Product/Service created successfully');
    }

    public function show(Product $product)
    {
        $product->load('stockTransactions.warehouse');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku'           => 'required|string|unique:products,sku,' . $product->id,
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'category'      => 'nullable|string|max:100',
            'unit'          => 'required|string|max:20',
            'type'          => 'required|in:material,service',
            'hsn_code'      => 'nullable|string|max:20',
            'material_code' => 'nullable|string|max:100',
            'inventory_type'=> 'required|in:purchase,free_issue',
            'price'         => 'required|numeric|min:0',
            'tax_rate'      => 'required|numeric|min:0|max:100',
            'reorder_level' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
            'image'         => 'nullable|image|max:2048',
        ]);

        $productData = $validated;
        unset($productData['image']);

        if ($request->has('remove_image') && $request->remove_image == '1') {
            if ($product->image_path && file_exists(public_path($product->image_path))) {
                @unlink(public_path($product->image_path));
            }
            $productData['image_path'] = null;
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $productData['image_path'] = 'uploads/products/' . $filename;
            
            // Delete old image if exists
            if ($product->image_path && file_exists(public_path($product->image_path))) {
                @unlink(public_path($product->image_path));
            }
        }

        $product->update($productData);

        return redirect()->route('admin.products.index')->with('success', 'Product/Service updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }
}
