<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Define dynamic array of standard Spatie roles mapping
        $roles = [
            'admin',
            'business user',
            'employee',
            'field technician',
            'field marketing'
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }

    public function down(): void
    {
        // No down migration logic needed for seed records
    }
};
