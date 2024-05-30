<?php

namespace App\Providers;

use Closure;
use Filament\Forms\Set;
use Illuminate\Support\ServiceProvider;
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
    TextInput::macro('qty', function (int|Closure $minValue = 0): static {
      $this
        ->live(true)
        ->integer()
        ->minValue($minValue)
        ->afterStateUpdated(fn(?int $state, Set $set, TextInput $component) => (
          blank($state)
          || is_string($state)
          || is_int($state) && $state < 0
          || !is_numeric($state)
        ) ? $set($component, 0) : false);
      return $this;
    });

    TextInput::macro('currency', function (int $minValue = 0, string $prefix = 'Rp'): static {
      $this
        ->live(true)
        ->numeric()
        ->minValue($minValue)
        ->prefix($prefix)
        ->afterStateUpdated(fn(?int $state, Set $set, TextInput $component) => (
          blank($state)
          || is_string($state)
          || is_int($state) && $state < 0
          || !is_numeric($state)
        ) ? $set($component, 0) : false);
      return $this;
    });

    TextInput::macro('code', function (string $code = null): static {
      $this
        ->required()
        ->disabled()
        ->dehydrated()
        ->default($code)
        ->unique(ignoreRecord: true)
        ->helperText('Code is generated automatically.');
      return $this;
    });

    PhoneInput::macro('idDefaultFormat', function (): static {
      $this
        ->defaultCountry('ID')
        ->initialCountry('id')
        ->rules('phone:mobile')
        ->formatAsYouType(false)
        ->showSelectedDialCode(true)
        ->focusNumberFormat(PhoneInputNumberType::E164);
      return $this;
    });
  }
}
