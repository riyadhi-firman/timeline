<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Fiber Optic Cable',
                'code' => 'FOC',
                'description' => 'Divisi yang menangani instalasi dan pemeliharaan kabel fiber optik',
            ],
            [
                'name' => 'Instalasi Kabel Rumah',
                'code' => 'IKR',
                'description' => 'Divisi yang menangani instalasi kabel di rumah pelanggan',
            ],
            [
                'name' => 'Network Operation Center',
                'code' => 'NOC',
                'description' => 'Divisi yang menangani operasional jaringan',
            ],
            [
                'name' => 'Maintenance Service',
                'code' => 'MS',
                'description' => 'Divisi yang menangani pemeliharaan dan perbaikan layanan',
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}