<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::findOrCreate('admin', 'web');
        $employeeRole = Role::findOrCreate('employee', 'web');

        // Create Permissions
        $permissions = [
            'access-admin',
            'access-employee',
            'manage-employees',
            'manage-customers',
            'manage-products',
            'manage-inventory',
            'manage-invoices',
            'manage-quotations',
            'manage-accounts',
            'manage-tasks',
            'manage-tickets',
            'manage-leads',
            'manage-shipments',
            'view-reports',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to employee
        $employeeRole->givePermissionTo([
            'access-employee',
            'manage-tasks',
            'manage-tickets',
            'manage-leads',
        ]);

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@hisabmittra.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '9876543210',
                'department' => 'Management',
                'status' => 'active',
            ]
        );
        $admin->assignRole('admin');

        // Create Employee User
        $employee = User::updateOrCreate(
            ['email' => 'employee@hisabmittra.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password'),
                'phone' => '9876543211',
                'department' => 'Sales',
                'status' => 'active',
            ]
        );
        $employee->assignRole('employee');

        // Create Sample Company Settings
        \App\Models\CompanySetting::create(['key' => 'company_name', 'value' => 'HisabMittra Pvt. Ltd.']);
        \App\Models\CompanySetting::create(['key' => 'company_gstin', 'value' => '07AAACH7409R1ZZ']);
        \App\Models\CompanySetting::create(['key' => 'company_address', 'value' => '123 Business Park, Sector 62, Noida']);
        \App\Models\CompanySetting::create(['key' => 'company_state', 'value' => 'Delhi']);
        \App\Models\CompanySetting::create(['key' => 'company_phone', 'value' => '1800-103-7555']);
        \App\Models\CompanySetting::create(['key' => 'company_email', 'value' => 'info@hisabmittra.com']);

        // Create Default Warehouse
        \App\Models\Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'Noida, Uttar Pradesh',
            'manager_id' => $admin->id,
        ]);

        // Create Sample Products
        $products = [
            [
                'sku' => 'HAV-WIRE-001',
                'name' => 'HisabMittra Lifeline Cable 1.5 sq mm',
                'description' => 'Single core PVC insulated cable',
                'category' => 'Cables & Wires',
                'unit' => 'meter',
                'hsn_code' => '85444290',
                'price' => 25.00,
                'tax_rate' => 18.00,
                'reorder_level' => 500,
                'current_stock' => 1000,
            ],
            [
                'sku' => 'HAV-MCB-001',
                'name' => 'HisabMittra MCB 16A Single Pole',
                'description' => 'Miniature Circuit Breaker',
                'category' => 'Switchgear',
                'unit' => 'pcs',
                'hsn_code' => '85363000',
                'price' => 120.00,
                'tax_rate' => 18.00,
                'reorder_level' => 50,
                'current_stock' => 200,
            ],
            [
                'sku' => 'HAV-FAN-001',
                'name' => 'HisabMittra Decorative Ceiling Fan 1200mm',
                'description' => 'Energy efficient ceiling fan',
                'category' => 'Fans',
                'unit' => 'pcs',
                'hsn_code' => '84145910',
                'price' => 2500.00,
                'tax_rate' => 18.00,
                'reorder_level' => 10,
                'current_stock' => 50,
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }

        // Create Sample Customers
        $customers = [
            [
                'name' => 'ABC Electricals',
                'email' => 'abc@electricals.com',
                'phone' => '9876543212',
                'gstin' => '07AAABC1234A1Z5',
                'address' => '123 Main Street',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pin' => '110001',
                'source' => 'manual',
            ],
            [
                'name' => 'XYZ Traders',
                'email' => 'xyz@traders.com',
                'phone' => '9876543213',
                'gstin' => '27AAAXYZ5678B1Z5',
                'address' => '456 Market Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pin' => '400001',
                'source' => 'manual',
            ],
        ];

        foreach ($customers as $customer) {
            \App\Models\Customer::create($customer);
        }

        // Create Sample Clients (Billed To)
        $clients = [
            [
                'name' => 'Metric Qube Demo Client 1',
                'company_name' => 'Metric Qube 1',
                'email' => 'client1@example.com',
                'phone' => '9876500001',
                'address' => '789 Client Avenue',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'country' => 'India',
                'pin' => '302001',
                'is_active' => true,
            ],
            [
                'name' => 'Metric Qube Demo Client 2',
                'company_name' => 'Metric Qube 2',
                'email' => 'client2@example.com',
                'phone' => '9876500002',
                'address' => '456 Another Road',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'pin' => '110002',
                'is_active' => true,
            ]
        ];

        foreach ($clients as $client) {
            \App\Models\Client::create($client);
        }

        // Create Chart of Accounts
        $accounts = [
            ['account_code' => 'A-1000', 'account_name' => 'Cash', 'account_type' => 'asset', 'opening_balance' => 50000, 'current_balance' => 50000],
            ['account_code' => 'A-1100', 'account_name' => 'Bank Account', 'account_type' => 'asset', 'opening_balance' => 500000, 'current_balance' => 500000],
            ['account_code' => 'A-1200', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'opening_balance' => 0, 'current_balance' => 0],
            ['account_code' => 'L-2000', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'opening_balance' => 0, 'current_balance' => 0],
            ['account_code' => 'E-3000', 'account_name' => 'Owner\'s Equity', 'account_type' => 'equity', 'opening_balance' => 550000, 'current_balance' => 550000],
            ['account_code' => 'I-4000', 'account_name' => 'Sales Revenue', 'account_type' => 'income', 'opening_balance' => 0, 'current_balance' => 0],
            ['account_code' => 'X-5000', 'account_name' => 'Cost of Goods Sold', 'account_type' => 'expense', 'opening_balance' => 0, 'current_balance' => 0],
        ];

        foreach ($accounts as $account) {
            \App\Models\Account::create($account);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@hisabmittra.com / password');
        $this->command->info('Employee Login: employee@hisabmittra.com / password');
    }
}
