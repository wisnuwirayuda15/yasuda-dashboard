<?php

namespace Database\Seeders;

use App\Models\Regency;
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
    foreach ($provinces as $province) {
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



    // $provinces = Province::where('code', '33')->get(); // Jawa Tengah
    $provinces = Province::all(); // Jawa Tengah
    $cmd->warn("Mendapatkan data kabupaten/kota dari semua provinsi...");
    $output->progressStart(count($provinces));
    foreach ($provinces as $province) {
      $response = Http::get("https://wilayah.id/api/regencies/{$province->code}.json");
      $regencies = $response->json();
      $regencies = $regencies['data'];
      foreach ($regencies as $regency) {
        $province->regencies()->create([
          'code' => $regency['code'],
          'name' => $regency['name'],
          'lat' => $regency['coordinates']['lat'],
          'lng' => $regency['coordinates']['lng'],
          'google_place_id' => $regency['google_place_id'],
        ]);
      }
      $output->progressAdvance();
    }
    $output->progressFinish();
    $cmd->info("Data kabupaten berhasil ditambahkan");



    $regencies = Regency::all();
    $cmd->warn("Mendapatkan data kecamatan dari semua kabupaten/kota...");
    $output->progressStart(count($regencies));
    foreach ($regencies as $regency) {
      $response = Http::get("https://wilayah.id/api/districts/{$regency->code}.json");
      $districts = $response->json();
      $districts = $districts['data'];
      foreach ($districts as $district) {
        $regency->districts()->create([
          'code' => $district['code'],
          'name' => $district['name'],
          'lat' => $district['coordinates']['lat'],
          'lng' => $district['coordinates']['lng'],
          'google_place_id' => $district['google_place_id'],
        ]);
      }
      $output->progressAdvance();
    }
    $output->progressFinish();
    $cmd->info("Data kecamatan berhasil ditambahkan");
  }
}
