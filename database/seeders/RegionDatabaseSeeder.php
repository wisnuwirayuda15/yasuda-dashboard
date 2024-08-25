<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RegionDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $path = database_path('sql/region');
    
    $files = File::files($path);

    foreach ($files as $file) {
      $sql = file_get_contents($file->getPathname());

      DB::unprepared($sql);

      $this->command->info('Imported SQL file: ' . $file->getFilename());
    }
  }
}
