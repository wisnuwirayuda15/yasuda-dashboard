<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

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
    //Indonesian locale and timezone
    Carbon::setLocale(env('APP_LOCALE', 'en'));

    //Unguard model
    if ((bool) env('UNGUARD_MODEL', false)) {
      Model::unguard();
    }

    //Sending validation notifications
    if ((bool) env('USE_NOTIFICATION', true)) {
      Page::$reportValidationErrorUsing = function (ValidationException $exception) {
        Notification::make()
          ->title($exception->getMessage())
          ->danger()
          ->send()
          ->sendToDatabase(auth()->user());
      };
    }

    //Languages selector
    LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
      $switch
        ->locales(['id', 'en'])
        ->visible(outsidePanels: true);
    });
  }
}
