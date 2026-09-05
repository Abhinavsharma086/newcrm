<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Rename general photo column (if exists, else we add the specific ones)
            // We will add the 5 specific guide-based document columns:
            if (!Schema::hasColumn('customers', 'inside_kitchen_photo')) {
                $table->string('inside_kitchen_photo')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('customers', 'meter_photo_3_angles')) {
                $table->string('meter_photo_3_angles')->nullable()->after('inside_kitchen_photo');
            }
            if (!Schema::hasColumn('customers', 'outside_kitchen_photo')) {
                $table->string('outside_kitchen_photo')->nullable()->after('meter_photo_3_angles');
            }
            if (!Schema::hasColumn('customers', 'rfc_report_image')) {
                $table->string('rfc_report_image')->nullable()->after('outside_kitchen_photo');
            }
            if (!Schema::hasColumn('customers', 'jmr_report_image')) {
                $table->string('jmr_report_image')->nullable()->after('rfc_report_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'inside_kitchen_photo',
                'meter_photo_3_angles',
                'outside_kitchen_photo',
                'rfc_report_image',
                'jmr_report_image'
            ]);
        });
    }
};
