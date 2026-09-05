<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('phone');
            $table->text('message');
            $table->enum('direction', ['in', 'out'])->default('in');
            $table->string('whatsapp_id')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();
            
            $table->index('phone');
            $table->index('processed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
