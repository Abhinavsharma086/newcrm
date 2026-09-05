<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Supplier;
use App\Models\VendorPo;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\User;

echo "Creating test data...\n";

$p = Product::updateOrCreate(['sku' => 'SKU-9999'], [
    'name' => 'Test Copper Wire',
    'price' => 1250.50,
    'current_stock' => 50,
    'category' => 'Raw Material',
    'unit' => 'kg'
]);
echo "Product created: {$p->name}\n";

$s = Supplier::updateOrCreate(['name' => 'Test Supplier ABC'], [
    'contact_person' => 'Ramesh Kumar',
    'phone' => '9876543210',
    'email' => 'contact@abcsupplier.com',
    'address' => 'Test Address, Mumbai',
    'is_active' => true
]);
echo "Supplier created: {$s->name}\n";

$v = VendorPo::updateOrCreate(['po_number' => 'VPO-5555'], [
    'vendor_id' => $s->id,
    'vendor_name' => $s->name,
    'po_date' => now(),
    'po_value' => 50000,
    'status' => 'issued',
]);
echo "Vendor PO created: {$v->po_number}\n";

$customer = Customer::first();
$user = User::first();
if ($customer && $user) {
    $l = Lead::updateOrCreate(['title' => 'Test Gas Installation Lead'], [
        'customer_id' => $customer->id,
        'assigned_to' => $user->id,
        'stage' => 'New',
        'source' => 'Website',
        'value' => 25000,
        'follow_up_date' => now()->addDays(2),
    ]);
    echo "Lead created: {$l->title}\n";
} else {
    echo "Skipped Lead (No Customer or User found)\n";
}
echo "Done!\n";
