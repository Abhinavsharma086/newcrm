<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('crn_no')->nullable()->unique()->after('phone');
            $table->string('sap_bp_id')->nullable()->after('crn_no');
            $table->string('society')->nullable()->after('sap_bp_id');
            $table->string('meter_no')->nullable()->after('society');
            $table->string('meter_type')->nullable()->after('meter_no');
            $table->string('manufacturer')->nullable()->after('meter_type');
            $table->string('rfc_contractor')->nullable()->after('manufacturer');
            $table->date('rfc_date')->nullable()->after('rfc_contractor');
            $table->string('jmr_contractor')->nullable()->after('rfc_date');
            $table->date('jmr_date')->nullable()->after('jmr_contractor');
            $table->string('mode_of_payment')->nullable()->after('jmr_date');
            $table->string('payment_ref_no')->nullable()->after('mode_of_payment');
            $table->date('payment_date')->nullable()->after('payment_ref_no');
            $table->decimal('reg_amount', 15, 2)->nullable()->after('payment_date');
            $table->string('job_card')->nullable()->after('reg_amount');
            $table->string('photo')->nullable()->after('job_card');
            $table->text('remarks')->nullable()->after('photo');
            $table->date('registration_date')->nullable()->after('remarks');
            $table->string('burner_type')->nullable()->after('registration_date');
            $table->date('lmc_date')->nullable()->after('burner_type');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'crn_no', 'sap_bp_id', 'society', 'meter_no', 'meter_type',
                'manufacturer', 'rfc_contractor', 'rfc_date', 'jmr_contractor',
                'jmr_date', 'mode_of_payment', 'payment_ref_no', 'payment_date',
                'reg_amount', 'job_card', 'photo', 'remarks', 'registration_date',
                'burner_type', 'lmc_date'
            ]);
        });
    }
};
