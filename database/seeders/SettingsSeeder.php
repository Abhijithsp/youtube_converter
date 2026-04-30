<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'download_path', 'value' => storage_path('app/downloads')],
            ['key' => 'max_playlist_size', 'value' => '50'],
            ['key' => 'max_concurrent_downloads', 'value' => '3'],
            ['key' => 'rate_limit_seconds', 'value' => '2'],
            ['key' => 'min_disk_space_mb', 'value' => '500'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
