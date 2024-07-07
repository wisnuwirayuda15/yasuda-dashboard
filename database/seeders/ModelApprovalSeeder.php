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

class ModelApprovalSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $cmd = $this->command;

    $cmd->warn('Aprroving all Models...');

    $user = User::first();

    $models = (new ModelScannerService())->getApprovableModels();

    foreach ($models as $model) {
      foreach ($model::withoutGlobalScopes()->get() as $model) {
        if (!$model->isApprovalCompleted()) {
          $model->approve(user: $user);
        }
      }
    }

    $cmd->info('Done!');
  }
}
