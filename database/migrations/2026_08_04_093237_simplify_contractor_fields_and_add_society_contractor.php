<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add single contractor fields to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('contractor')->nullable()->after('manufacturer');
            $table->unsignedBigInteger('contractor_id')->nullable()->after('contractor');
            
            // Add foreign key constraint safely (without strict checks to support existing tables)
            // $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('set null');
        });

        // Add contractor relation to societies table
        Schema::table('societies', function (Blueprint $table) {
            $table->unsignedBigInteger('contractor_id')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['contractor', 'contractor_id']);
        });

        Schema::table('societies', function (Blueprint $table) {
            $table->dropColumn('contractor_id');
        });
    }
};
