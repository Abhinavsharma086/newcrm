<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_pos', function (Blueprint $table) {
            $table->string('irn')->nullable()->after('po_number');
            $table->string('ack_no')->nullable()->after('irn');
            $table->date('ack_date')->nullable()->after('ack_no');
            $table->string('vendor_name')->nullable()->after('vendor_id');
            $table->string('vendor_gstin')->nullable()->after('vendor_name');
            $table->text('vendor_address')->nullable()->after('vendor_gstin');
            $table->string('vendor_state')->nullable()->after('vendor_address');
            $table->string('vendor_contact')->nullable()->after('vendor_state');

            $table->string('delivery_note')->nullable()->after('po_date');
            $table->string('reference_no')->nullable()->after('delivery_note');
            $table->date('reference_date')->nullable()->after('reference_no');
            $table->string('buyer_order_no')->nullable()->after('reference_date');
            $table->date('buyer_order_date')->nullable()->after('buyer_order_no');
            $table->string('dispatch_doc_no')->nullable()->after('buyer_order_date');
            $table->string('dispatched_through')->nullable()->after('dispatch_doc_no');
            $table->string('destination')->nullable()->after('dispatched_through');
            $table->string('terms_of_delivery')->nullable()->after('destination');

            // Consignee (Ship To)
            $table->string('consignee_name')->nullable()->after('terms_of_delivery');
            $table->text('consignee_address')->nullable()->after('consignee_name');
            $table->string('consignee_gstin')->nullable()->after('consignee_address');
            $table->string('consignee_state')->nullable()->after('consignee_gstin');
            $table->string('consignee_contact_person')->nullable()->after('consignee_state');
            $table->string('consignee_contact')->nullable()->after('consignee_contact_person');

            // Buyer (Bill To)
            $table->string('buyer_name')->nullable()->after('consignee_contact');
            $table->text('buyer_address')->nullable()->after('buyer_name');
            $table->string('buyer_gstin')->nullable()->after('buyer_address');
            $table->string('buyer_state')->nullable()->after('buyer_gstin');
            $table->string('buyer_place_of_supply')->nullable()->after('buyer_state');
            $table->string('buyer_contact_person')->nullable()->after('buyer_place_of_supply');
            $table->string('buyer_contact')->nullable()->after('buyer_contact_person');
            $table->string('buyer_email')->nullable()->after('buyer_contact');

            // Financial Summary
            $table->decimal('subtotal', 15, 2)->default(0.00)->after('po_value');
            $table->decimal('cgst_amount', 15, 2)->default(0.00)->after('subtotal');
            $table->decimal('sgst_amount', 15, 2)->default(0.00)->after('cgst_amount');
            $table->decimal('igst_amount', 15, 2)->default(0.00)->after('sgst_amount');
            $table->decimal('round_off', 10, 2)->default(0.00)->after('igst_amount');
            $table->decimal('grand_total', 15, 2)->default(0.00)->after('round_off');

            // Bank details
            $table->string('bank_name')->nullable()->after('grand_total');
            $table->string('bank_account_no')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account_no');
            $table->string('bank_branch')->nullable()->after('bank_ifsc');

            // Uploaded Bill image path
            $table->string('bill_image_path')->nullable()->after('bank_branch');
        });

        Schema::table('vendor_po_items', function (Blueprint $table) {
            $table->string('part_no')->nullable()->after('hsn_code');
            $table->decimal('discount_percent', 5, 2)->default(0.00)->after('rate');
            $table->decimal('taxable_amount', 15, 2)->default(0.00)->after('discount_percent');
            $table->decimal('cgst_amount', 15, 2)->default(0.00)->after('gst_percent');
            $table->decimal('sgst_amount', 15, 2)->default(0.00)->after('cgst_amount');
            $table->decimal('igst_amount', 15, 2)->default(0.00)->after('sgst_amount');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_pos', function (Blueprint $table) {
            $table->dropColumn([
                'irn', 'ack_no', 'ack_date', 'vendor_name', 'vendor_gstin', 'vendor_address', 'vendor_state', 'vendor_contact',
                'delivery_note', 'reference_no', 'reference_date', 'buyer_order_no', 'buyer_order_date',
                'dispatch_doc_no', 'dispatched_through', 'destination', 'terms_of_delivery',
                'consignee_name', 'consignee_address', 'consignee_gstin', 'consignee_state', 'consignee_contact_person', 'consignee_contact',
                'buyer_name', 'buyer_address', 'buyer_gstin', 'buyer_state', 'buyer_place_of_supply', 'buyer_contact_person', 'buyer_contact', 'buyer_email',
                'subtotal', 'cgst_amount', 'sgst_amount', 'igst_amount', 'round_off', 'grand_total',
                'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch', 'bill_image_path'
            ]);
        });

        Schema::table('vendor_po_items', function (Blueprint $table) {
            $table->dropColumn([
                'part_no', 'discount_percent', 'taxable_amount', 'cgst_amount', 'sgst_amount', 'igst_amount'
            ]);
        });
    }
};
