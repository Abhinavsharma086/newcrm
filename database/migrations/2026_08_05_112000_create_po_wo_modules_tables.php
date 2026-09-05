<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Client POs
        Schema::create('client_pos', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('site_name')->nullable();
            $table->date('po_date');
            $table->decimal('po_value', 15, 2)->default(0.00);
            $table->text('payment_terms')->nullable();
            $table->decimal('retention_percent', 5, 2)->default(0.00);
            $table->string('gstin')->nullable();
            $table->string('status')->default('draft'); // draft, active, closed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Client PO Items
        Schema::create('client_po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_po_id')->constrained('client_pos')->onDelete('cascade');
            $table->string('description');
            $table->string('hsn_code', 20)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('qty', 12, 2)->default(0.00);
            $table->decimal('rate', 12, 2)->default(0.00);
            $table->decimal('gst_percent', 5, 2)->default(0.00);
            $table->decimal('total_value', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. Daily progress entries
        Schema::create('progress_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_po_id')->constrained('client_pos')->onDelete('cascade');
            $table->foreignId('client_po_item_id')->constrained('client_po_items')->onDelete('cascade');
            $table->date('entry_date');
            $table->decimal('executed_qty', 12, 2);
            $table->foreignId('entry_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Vendor POs
        Schema::create('vendor_pos', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('vendor_id')->constrained('suppliers')->onDelete('cascade'); // supplier = vendor
            $table->string('site_name')->nullable();
            $table->date('po_date');
            $table->decimal('po_value', 15, 2)->default(0.00);
            $table->text('payment_terms')->nullable();
            $table->decimal('retention_percent', 5, 2)->default(0.00);
            $table->decimal('tds_percent', 5, 2)->default(0.00);
            $table->string('status')->default('draft'); // draft, active, closed
            $table->timestamps();
        });

        // 5. Vendor PO Items
        Schema::create('vendor_po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_po_id')->constrained('vendor_pos')->onDelete('cascade');
            $table->string('description');
            $table->string('hsn_code', 20)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('qty', 12, 2)->default(0.00);
            $table->decimal('rate', 12, 2)->default(0.00);
            $table->decimal('gst_percent', 5, 2)->default(0.00);
            $table->decimal('total_value', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 6. Vendor Invoices (with 3-way matching and GST ITC verification flags)
        Schema::create('vendor_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_po_id')->nullable()->constrained('vendor_pos')->onDelete('set null');
            $table->foreignId('vendor_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->decimal('invoice_amount', 15, 2);
            $table->decimal('gst_amount', 15, 2)->default(0.00);
            $table->decimal('tds_amount', 15, 2)->default(0.00);
            $table->decimal('net_payable', 15, 2);
            $table->string('matching_status')->default('pending'); // pending, matched, flagged
            $table->string('approval_status')->default('pending'); // pending, approved, rejected
            $table->boolean('itc_eligible')->default(true);
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Add client_po_id and supplier_id fields to existing Invoices table to link Client POs vs vendor receipts
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'client_po_id')) {
                $table->foreignId('client_po_id')->nullable()->constrained('client_pos')->onDelete('set null');
            }
            if (!Schema::hasColumn('invoices', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['client_po_id', 'client_id']);
        });
        Schema::dropIfExists('vendor_invoices');
        Schema::dropIfExists('vendor_po_items');
        Schema::dropIfExists('vendor_pos');
        Schema::dropIfExists('progress_entries');
        Schema::dropIfExists('client_po_items');
        Schema::dropIfExists('client_pos');
    }
};
