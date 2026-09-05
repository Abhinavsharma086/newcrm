<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('gstin', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin', 10)->nullable();
            $table->enum('source', ['manual', 'whatsapp', 'web'])->default('manual');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('phone');
            $table->index('gstin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
