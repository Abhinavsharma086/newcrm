<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('material_code')->nullable()->after('sku');
            $table->string('inventory_type')->default('purchase')->after('material_code'); // purchase, free_issue
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['material_code', 'inventory_type']);
        });
    }
};
