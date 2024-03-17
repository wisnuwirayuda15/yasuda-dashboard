<?php

namespace Database\Seeders;

use App\Models\Regency;
use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RegionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $cmd = $this->command;
    $output = $cmd->getOutput();


    $cmd->warn('Mendapatkan data provinsi...');
    $response = Http::get('https://wilayah.id/api/provinces.json');
    $provinces = $response->json();
    $provinces = $provinces['data'];
    $output->progressStart(count($provinces));
    foreach ($provinces['data'] as $province) {
      Province::create([
        'code' => $province['code'],
        'name' => $province['name'],
        'lat' => $province['coordinates']['lat'],
        'lng' => $province['coordinates']['lng'],
        'google_place_id' => $province['google_place_id'],
      ]);
      $output->progressAdvance();
    }
    $output->progressFinish();
    $cmd->info("Data provinsi berhasil ditambahkan");



    $provinces = Province::all();
    $cmd->warn("Mendapatkan data kabupaten dari semua provinsi...");
    $output->progressStart(count($provinces));
    foreach ($provinces as $province) {
      $response = Http::get("https://api.cahyadsn.com/regencies/{$province->id}");
      $regencies = $response->json();
      foreach ($regencies['data'] as $regency) {
        $province->regencies()->create([
          // 'id' => $regency['kode'],
          'name' => $regency['nama'],
        ]);
      }
      $output->progressAdvance();
    }
    $output->progressFinish();
    $cmd->info("Data kabupaten berhasil ditambahkan");
  }
}
