<?php

namespace App\Providers;

use Closure;
use Filament\Forms\Set;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;

class MacroServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    TextInput::macro('preventUnwantedNumberValue', function (string $fieldName = null): static {
      $this
        ->live(true)
        ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set($fieldName ?? $this, 0) : false);
      return $this;
    });
  }
}
