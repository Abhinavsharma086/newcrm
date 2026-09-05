<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('societies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Smart extraction of existing societies
        try {
            $uniqueSocieties = DB::table('customers')
                ->whereNotNull('society')
                ->where('society', '!=', '')
                ->distinct()
                ->pluck('society');

            foreach ($uniqueSocieties as $name) {
                $trimmed = trim($name);
                if ($trimmed !== '' && strtolower($trimmed) !== 'none') {
                    DB::table('societies')->insertOrIgnore([
                        'name' => $trimmed,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ignore if customers table does not exist or has columns missing during clean setup
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('societies');
    }
};
