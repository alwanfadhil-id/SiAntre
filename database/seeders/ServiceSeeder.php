<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Poli Umum', 'prefix' => 'PU'],
            ['name' => 'Poli Gigi', 'prefix' => 'PG'],
            ['name' => 'Poli Anak', 'prefix' => 'PA'],
            ['name' => 'Laboratorium', 'prefix' => 'LAB'],
            ['name' => 'Apotek', 'prefix' => 'APK'],
            ['name' => 'Administrasi', 'prefix' => 'ADM'],
            ['name' => 'Poli Kulit', 'prefix' => 'PK'],
            ['name' => 'Poli Mata', 'prefix' => 'PM'],
            ['name' => 'Poli THT', 'prefix' => 'THT'],
            ['name' => 'Radiologi', 'prefix' => 'RAD'],
        ];

        foreach ($services as $serviceData) {
            \App\Models\Service::updateOrCreate(
                ['name' => $serviceData['name']], // Find by name
                [
                    'name' => $serviceData['name'],
                    'prefix' => $serviceData['prefix'],
                ] // Update or create with this data
            );
        }

        // Optionally add more services in local/testing environments
        if (app()->environment('local', 'testing')) {
            $additionalServices = [
                ['name' => 'Poli Jantung', 'prefix' => 'PJ'],
                ['name' => 'Poli Saraf', 'prefix' => 'PS'],
                ['name' => 'Poli Paru', 'prefix' => 'PP'],
                ['name' => 'Poli Kandungan', 'prefix' => 'PKB'],
                ['name' => 'Poli Bedah', 'prefix' => 'BED'],
            ];

            foreach ($additionalServices as $serviceData) {
                \App\Models\Service::updateOrCreate(
                    ['name' => $serviceData['name']], // Find by name
                    [
                        'name' => $serviceData['name'],
                        'prefix' => $serviceData['prefix'],
                    ] // Update or create with this data
                );
            }
        }
    }
}
