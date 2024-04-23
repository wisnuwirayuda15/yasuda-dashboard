<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Assets\Js;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentAsset;
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
    // Use secure connection for production
    if (env('APP_ENV') !== 'local') {
      URL::forceScheme(scheme: 'https');
    }

    // Tailwind CSS CDN in local
    // if (env('APP_ENV') === 'local') {
    //   FilamentAsset::register([
    //     Js::make('tailwindcss', 'https://cdn.tailwindcss.com'),
    //   ]);
    // }

    // Indonesian locale and timezone
    Carbon::setlocale(config('app.locale'));

    // Unguard model
    if ((bool) env('UNGUARD_MODEL', false)) {
      Model::unguard();
    }

    // Sending validation notifications
    if ((bool) env('VALIDATION_NOTIFICATION', true)) {
      Page::$reportValidationErrorUsing = function (ValidationException $exception) {
        Notification::make()
          ->title($exception->getMessage())
          ->danger()
          ->send();
        // ->sendToDatabase(auth()->user());
      };
    }

    // Languages selector
    LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
      $switch
        ->locales(['en', 'id'])
        ->visible(outsidePanels: true);
    });
  }
}
