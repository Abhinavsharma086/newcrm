<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('mlc_pipe_no')->nullable()->after('mlc_pipe_length');
            $table->string('male_union')->nullable()->after('mlc_pipe_no');
            $table->string('female_union')->nullable()->after('male_union');
            $table->string('isolation_valve')->nullable()->after('female_union');
            $table->string('lmc_contractor')->nullable()->after('isolation_valve');
            $table->unsignedBigInteger('lmc_contractor_id')->nullable()->after('lmc_contractor');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'mlc_pipe_no', 'male_union', 'female_union',
                'isolation_valve', 'lmc_contractor', 'lmc_contractor_id'
            ]);
        });
    }
};
