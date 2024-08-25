<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Number;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
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
    Carbon::setlocale(env('APP_LOCALE', 'id'));
    Number::useLocale(env('APP_LOCALE', 'id'));

    Gate::define('viewPulse', function (User $user) {
      return $user->isSuperAdmin();
    });

    Pulse::user(fn(User $user) => [
      'name' => $user->getFilamentName(),
      'extra' => $user->email,
      'avatar' => $user->getFilamentAvatarUrl(),
    ]);

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
