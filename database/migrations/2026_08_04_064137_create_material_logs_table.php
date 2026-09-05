<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_logs', function (Blueprint $table) {
            $table->id();
            $table->date('log_date');
            $table->enum('log_type', ['inward', 'outward']);
            $table->unsignedBigInteger('product_id')->nullable();
            
            // Excel Mapping fields
            $table->string('material_code')->nullable();
            $table->string('material_description')->nullable();
            $table->string('uom')->nullable();
            $table->decimal('qty', 12, 2)->default(0);
            $table->decimal('unit_rate', 12, 2)->nullable();
            
            // FIM or Purchase categorization
            $table->string('material_type')->nullable(); // Purchase or Free Issue Material
            $table->string('supplied_against')->nullable(); // supplied_against or consumed_against info
            $table->string('supplier_name')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('client_name')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            
            // Reconciliation trackers
            $table->string('meter_no')->nullable();
            $table->string('wo_invoice')->nullable();
            $table->string('ordered_or_consumed')->nullable(); // Yes/No or status indicator
            $table->string('supporting_document')->nullable();
            $table->string('store_name')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_logs');
    }
};
