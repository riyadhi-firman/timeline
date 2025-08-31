<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TechnicianPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan izin ada
        $permission = Permission::firstOrCreate(['name' => 'create_job::report']);
        
        // Dapatkan peran teknisi (sesuaikan nama peran jika berbeda)
        $technicianRole = Role::firstOrCreate(['name' => 'technician']);
        
        // Berikan izin ke peran teknisi
        $technicianRole->givePermissionTo($permission);
    }
}