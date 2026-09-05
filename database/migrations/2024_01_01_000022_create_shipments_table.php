<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('courier_name')->nullable();
            $table->string('tracking_no')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->date('dispatch_date');
            $table->date('expected_delivery')->nullable();
            $table->enum('status', ['dispatched', 'transit', 'delivered'])->default('dispatched');
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('shipment_no');
            $table->index('tracking_no');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
