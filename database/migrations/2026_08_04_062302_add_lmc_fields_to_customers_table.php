<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->date('conversion_date')->nullable()->after('lmc_date');
            $table->decimal('mlc_pipe_length', 8, 2)->nullable()->after('conversion_date');
            $table->decimal('extra_mlc_amount', 10, 2)->nullable()->after('mlc_pipe_length');
            $table->string('customer_stage')->default('registered')->after('extra_mlc_amount');
            // rfc_contractor and jmr_contractor already exist as text fields
            // We add contractor_id fields for dropdown link
            $table->unsignedBigInteger('rfc_contractor_id')->nullable()->after('rfc_contractor');
            $table->unsignedBigInteger('jmr_contractor_id')->nullable()->after('jmr_contractor');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'conversion_date', 'mlc_pipe_length', 'extra_mlc_amount',
                'customer_stage', 'rfc_contractor_id', 'jmr_contractor_id'
            ]);
        });
    }
};
