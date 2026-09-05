<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_gstin')->nullable()->after('customer_name');
            $table->string('trade_name')->nullable()->after('customer_gstin');
            $table->text('billing_address')->nullable()->after('trade_name');
            $table->string('city')->nullable()->after('billing_address');
            $table->string('pincode')->nullable()->after('city');
            $table->string('state')->nullable()->after('pincode');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_gstin',
                'trade_name',
                'billing_address',
                'city',
                'pincode',
                'state'
            ]);
        });
    }
};
