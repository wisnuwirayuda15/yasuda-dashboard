<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Policies\ProcessApprovalFlowPolicy;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The model to policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    // This policies is not registered by default, filament shield doesn't recognize it.
    ProcessApprovalFlow::class => ProcessApprovalFlowPolicy::class,
  ];

  /**
   * Register any authentication / authorization services.
   */
  public function boot(): void
  {
    //
  }
}
