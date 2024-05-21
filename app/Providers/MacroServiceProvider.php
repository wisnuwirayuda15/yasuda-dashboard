<?php

namespace App\Providers;

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
    TextInput::macro('preventUnwantedNumberValue', function (string $fieldName = null): static {
      $this
        ->live(true)
        ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set($fieldName ?? $this, 0) : false);
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
