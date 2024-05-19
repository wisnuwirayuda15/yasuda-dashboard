<?php

namespace App\Providers;

use Closure;
use Filament\Forms\Set;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

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

    PhoneInput::macro('idDefaultFormat', function (): static {
      $this
        ->focusNumberFormat(PhoneInputNumberType::E164)
        ->defaultCountry('ID')
        ->initialCountry('id')
        ->showSelectedDialCode(true)
        ->formatAsYouType(false)
        ->rules('phone:mobile');
      return $this;
    });
  }
}
