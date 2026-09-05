<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('primary_id_number')->nullable()->after('remarks');
            $table->string('primary_id_file')->nullable()->after('primary_id_number');
            $table->string('secondary_id_type')->nullable()->after('primary_id_file');
            $table->string('secondary_id_number')->nullable()->after('secondary_id_type');
            $table->string('secondary_id_file')->nullable()->after('secondary_id_number');
            $table->string('passport_photo')->nullable()->after('secondary_id_file');
            $table->string('address_proof_file')->nullable()->after('passport_photo');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'primary_id_number',
                'primary_id_file',
                'secondary_id_type',
                'secondary_id_number',
                'secondary_id_file',
                'passport_photo',
                'address_proof_file'
            ]);
        });
    }
};
