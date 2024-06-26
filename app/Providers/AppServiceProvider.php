<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Use secure connection for production
    if (!app()->environment('local')) {
      URL::forceScheme('https');
    }

    // Indonesian locale and timezone
    Carbon::setlocale('id');
    Number::useLocale('id');

    // Unguard model
    if ((bool) env('UNGUARD_MODEL', false)) {
      Model::unguard();
    }
  }
}
