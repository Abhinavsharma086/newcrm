<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_debit_notes', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('note_no')->unique();
            $blueprint->enum('type', ['credit', 'debit']);
            $blueprint->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->decimal('tax_amount', 15, 2)->default(0);
            $blueprint->string('reason');
            $blueprint->date('note_date');
            $blueprint->foreignId('created_by')->constrained('users');
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_debit_notes');
    }
};
