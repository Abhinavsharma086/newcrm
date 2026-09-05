<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\VendorInvoice;
use App\Models\VendorPo;
use App\Models\Supplier;

echo "Creating Vendor Invoice test data...\n";

$s = Supplier::firstOrCreate(['name' => 'Test Supplier ABC'], [
    'contact_person' => 'Ramesh Kumar',
    'phone' => '9876543210',
    'email' => 'contact@abcsupplier.com',
    'address' => 'Test Address, Mumbai',
    'is_active' => true
]);

$v = VendorPo::firstOrCreate(['po_number' => 'VPO-5555'], [
    'vendor_id' => $s->id,
    'vendor_name' => $s->name,
    'po_date' => now(),
    'po_value' => 50000,
    'status' => 'issued',
]);

$vi = VendorInvoice::updateOrCreate(['invoice_number' => 'VINV-9999'], [
    'vendor_po_id' => $v->id,
    'vendor_id' => $s->id,
    'invoice_date' => now(),
    'invoice_amount' => 50000,
    'gst_amount' => 9000,
    'net_payable' => 59000,
    'payment_status' => 'pending',
    'notes' => 'This is a test vendor invoice'
]);

echo "Vendor Invoice created: {$vi->invoice_number}\n";
echo "Done!\n";
