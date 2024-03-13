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
    $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
    $provinces = $response->json();
    $output->progressStart(count($provinces));
    foreach ($provinces as $province) {
      Province::create([
        'id' => $province['id'],
        'name' => $province['name'],
      ]);
      $output->progressAdvance();
    }
    $output->progressFinish();
    $cmd->info("Data provinsi berhasil ditambahkan");



    $provinces = Province::all();
    foreach ($provinces as $province) {
      $cmd->warn("Mendapatkan data kabupaten dari provinsi {$province->name}...");
      $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/regencies/' . $province->id . '.json');
      $regencies = $response->json();
      $output->progressStart(count($regencies));
      foreach ($regencies as $regency) {
        $province->regencies()->create([
          'id' => $regency['id'],
          'name' => $regency['name'],
        ]);
        $output->progressAdvance();
      }
      $output->progressFinish();
    }
    $cmd->info("Data kabupaten berhasil ditambahkan");


    $regencies = Regency::all();
    foreach ($regencies as $regency) {
      $cmd->warn("Mendapatkan data kecamatan dari kabupaten {$regency->name}...");
      $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/districts/' . $regency->id . '.json');
      $districts = $response->json();
      $output->progressStart(count($districts));
      foreach ($districts as $district) {
        $regency->districts()->create([
          'id' => $district['id'],
          'name' => $district['name'],
        ]);
        $output->progressAdvance();
      }
      $output->progressFinish();
    }
    $cmd->info("Data kecamatan berhasil ditambahkan");



    $districts = District::all();
    foreach ($districts as $district) {
      $cmd->warn("Mendapatkan data keluarahan dari kecamatan {$district->name}...");
      $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/villages/' . $district->id . '.json');
      $villages = $response->json();
      $output->progressStart(count($villages));
      foreach ($villages as $village) {
        $district->villages()->create([
          'id' => $village['id'],
          'name' => $village['name'],
        ]);
        $output->progressAdvance();
      }
      $output->progressFinish();
    }
    $cmd->info("Data kelurahan berhasil ditambahkan");
  }
}
