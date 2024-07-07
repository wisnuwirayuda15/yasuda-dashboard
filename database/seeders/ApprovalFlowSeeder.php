<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Fleet;
use App\Models\Order;
use App\Models\OrderFleet;
use App\Models\Destination;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use EightyNine\Approvals\Services\ModelScannerService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use RingleSoft\LaravelProcessApproval\Facades\ProcessApproval;

class ApprovalFlowSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $cmd = $this->command;

    $cmd->warn('Creating aprroval flows...');

    $models = (new ModelScannerService())->getApprovableModels();

    foreach ($models as $key => $value) {
      $modelName = str_replace('App\Models\\', '', $key);

      $flow = ProcessApproval::createFlow(Str::headline($modelName), $value);

      $flow->steps()->create([
        'role_id' => 1,
        'action' => 'Approve',
        'order' => 1
      ]);
    }

    $cmd->info('Done!');
  }
}
