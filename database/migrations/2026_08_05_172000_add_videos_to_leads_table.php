<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Check if columns do not exist before adding
            if (!Schema::hasColumn('leads', 'burner_type')) {
                $table->enum('burner_type', ['Hob Stove', 'Normal'])->default('Normal')->after('customer_id');
            }
            if (!Schema::hasColumn('leads', 'kitchen_burner_video')) {
                $table->string('kitchen_burner_video')->nullable()->after('burner_type');
            }
            if (!Schema::hasColumn('leads', 'external_riser_video')) {
                $table->string('external_riser_video')->nullable()->after('kitchen_burner_video');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'burner_type',
                'kitchen_burner_video',
                'external_riser_video'
            ]);
        });
    }
};
