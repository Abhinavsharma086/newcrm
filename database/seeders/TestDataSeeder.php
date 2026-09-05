<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => 'Admin User',
                'email' => 'admin@hisabmittra.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'phone' => '9876543210',
                'department' => 'Management',
                'status' => 'active',
            ]);
        }

        $customer = \App\Models\Customer::first();
        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'address' => 'Test Address',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pin' => '110001',
                'source' => 'manual',
            ]);
        }

        $product = \App\Models\Product::first();
        if (!$product) {
            $product = \App\Models\Product::create([
                'sku' => 'TEST-001',
                'name' => 'Test Product',
                'price' => 100,
                'tax_rate' => 18,
                'current_stock' => 50,
            ]);
        }

        // Create a quotation with both predefined and custom products
        $quotation = \App\Models\Quotation::create([
            'quotation_no' => 'QUO-202608-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'date' => now(),
            'valid_till' => now()->addDays(15),
            'notes' => 'This is a test quotation with custom manual products.',
            'created_by' => $user->id,
            'status' => 'draft',
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ]);

        $subtotal = 0;
        $taxAmount = 0;

        // 1. Regular Product
        if ($product) {
            $lineTotal = 2 * $product->price;
            $lineTax = ($lineTotal * $product->tax_rate) / 100;
            
            $quotation->items()->create([
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 2,
                'unit_price' => $product->price,
                'tax_rate' => $product->tax_rate,
                'tax_amount' => $lineTax,
                'total' => $lineTotal + $lineTax,
            ]);
            $subtotal += $lineTotal;
            $taxAmount += $lineTax;
        }

        // 2. Custom Manual Product
        $customPrice = 1500;
        $customQty = 3;
        $customTaxRate = 0; // 0% tax for custom
        
        $customLineTotal = $customQty * $customPrice;
        $customLineTax = 0;

        $quotation->items()->create([
            'product_id' => null, // Custom product
            'description' => 'Custom Installation Service (Manual Entry)',
            'quantity' => $customQty,
            'unit_price' => $customPrice,
            'tax_rate' => $customTaxRate,
            'tax_amount' => $customLineTax,
            'total' => $customLineTotal + $customLineTax,
        ]);

        $subtotal += $customLineTotal;
        $taxAmount += $customLineTax;

        $quotation->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);

        // 3. Lead
        $lead = \App\Models\Lead::create([
            'title' => 'Test Lead Opportunity',
            'source' => 'web',
            'stage' => 'new',
            'customer_id' => $customer->id,
            'assigned_to' => $user->id,
            'notes' => 'This is a test lead'
        ]);

        // 4. Task
        $task = \App\Models\Task::create([
            'title' => 'Test Task',
            'description' => 'This is a test task for the CRM',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addDays(2),
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'customer_id' => $customer->id,
        ]);

        // 5. Ticket
        $ticket = \App\Models\Ticket::create([
            'ticket_no' => 'TKT-202608-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'title' => 'Test Support Ticket',
            'description' => 'Test issue description',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'general',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
        ]);

        // 6. Invoice
        $invoice = \App\Models\Invoice::create([
            'invoice_no' => 'INV-202608-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $quotation->subtotal,
            'cgst' => $quotation->tax_amount / 2,
            'sgst' => $quotation->tax_amount / 2,
            'igst' => 0,
            'total' => $quotation->total,
            'payment_status' => 'partial',
            'paid_amount' => $quotation->total / 2,
            'notes' => 'Test Invoice',
            'created_by' => $user->id,
        ]);

        if ($product) {
            $invoice->items()->create([
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 2,
                'unit_price' => $product->price,
                'tax_rate' => $product->tax_rate,
                'cgst' => ($lineTax / 2),
                'sgst' => ($lineTax / 2),
                'igst' => 0,
                'total' => $lineTotal + $lineTax,
            ]);
        }

        // 7. Payment
        \App\Models\Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->paid_amount,
            'payment_date' => now(),
            'payment_method' => 'bank_transfer',
            'reference_no' => 'TXN' . time(),
            'notes' => 'Test payment against invoice',
            'created_by' => $user->id,
        ]);

        $this->command->info('Test data populated successfully for Quotations, Leads, Tasks, Tickets, Invoices, and Payments.');
    }
}
