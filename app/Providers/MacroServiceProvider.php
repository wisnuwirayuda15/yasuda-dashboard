<?php

namespace App\Providers;

use Closure;
use Filament\Forms\Set;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
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
        ->afterStateUpdated(function ($state, Set $set, TextInput $component) {
          $value = (int) $state;
          $value < 0 ? $set($component, 0) : $set($component, $value);
        })
        ->extraInputAttributes([
          'x-data' => '{
            value: $el.value,
            validate() {
                if (isNaN(this.value) || !this.value || !Number.isInteger(Number(this.value)) || this.value < 0) {
                      this.value = 0;
                  } else if (this.value > 0) {
                      this.value = this.value.replace(/^0+/, "");
                  }
                  $el.value = this.value;
              }
          }',
          'x-model' => 'value',
          'x-on:input' => 'validate()'
        ]);
      return $this;
    });

    TextInput::macro('currency', function (int|Closure $minValue = 0, string|Closure $prefix = 'Rp'): static {
      $this
        ->live(true)
        ->numeric()
        ->minValue($minValue)
        ->prefix($prefix)
        ->afterStateUpdated(function ($state, Set $set, TextInput $component) {
          $value = (float) $state;
          $value < 0 ? $set($component, 0) : $set($component, $value);
        })
        ->extraInputAttributes([
          'x-data' => '{
              value: $el.value,
              validate() {
                  if (isNaN(this.value) || !this.value || this.value < 0) {
                      this.value = 0;
                  } else if (this.value > 0) {
                      this.value = this.value.replace(/^0+/, "");
                  }
                  $el.value = this.value;
              }
          }',
          'x-model' => 'value',
          'x-on:input' => 'validate()'
        ]);
      return $this;
    });

    TextInput::macro('code', function (string|Closure $code, bool $editable = true, bool $generateable = true): static {
      $edit = ['edit', 'editOption', 'editOption.editOption', 'createOption.editOption'];
      $view = ['view', 'viewOption', 'viewOption.viewOption', 'createOption.viewOption'];

      $this
        ->required()
        ->disabled()
        ->dehydrated()
        ->default(fn() => $code)
        ->unique(ignoreRecord: true)
        ->hiddenOn($edit)
        ->helperText(fn($operation) => !in_array($operation, $view) ? 'The code is generated automatically' . ($editable ? ' and can be edited.' : '.') : null)
        ->hintActions([
          Action::make('edit')
            ->icon('tabler-edit')
            ->hidden(fn($operation) => $operation === 'view')
            ->visible($editable)
            ->action(fn(TextInput $component) => $component->disabled(false)),
          Action::make('generate')
            ->icon('fas-random')
            ->hidden(fn($operation) => $operation === 'view')
            ->visible($generateable)
            ->action(function (TextInput $component, Set $set) use ($code) {
              $set($component, $code);
            })
        ]);
      return $this;
    });

    Checkbox::macro('confirmation', function (): static {
      $this
        ->required()
        ->hiddenOn('view')
        ->label('Semua perhitungan sudah dicek dan tidak ada kesalahan');
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
