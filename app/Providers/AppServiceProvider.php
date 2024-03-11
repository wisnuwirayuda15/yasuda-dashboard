<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;

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
    (bool) env('UNGUARD_MODEL') && Model::unguard();

    //Register the ENUM type mapping
    // DB::connection()
    //   ->getDoctrineSchemaManager()
    //   ->getDatabasePlatform()
    //   ->registerDoctrineTypeMapping('enum', 'string');

    //Sending validation notifications
    if ((bool) env('USE_NOTIFICATION')) {
      Page::$reportValidationErrorUsing = function (ValidationException $exception) {
        Notification::make()
          ->title($exception->getMessage())
          ->danger()
          ->send();
      };
    }

    LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
      $switch
        ->locales(['id', 'en'])
        ->visible(outsidePanels: true);
    });
  }
}
