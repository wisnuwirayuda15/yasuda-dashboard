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


    // Model::unguard();

    // Model::preventLazyLoading();

    // // But in production, log the violation instead of throwing an exception.
    // if ($this->app->isProduction()) {
    //   Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
    //     $class = get_class($model);

    //     info("Attempted to lazy load [{$relation}] on model [{$class}].");
    //   });
    // }

    // Model::preventAccessingMissingAttributes();
  }
}
