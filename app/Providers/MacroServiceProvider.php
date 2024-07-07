<?php

namespace App\Providers;

use Closure;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use App\Models\Scopes\ApprovedScope;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\MountableAction;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Checkbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Rawilk\FilamentPasswordInput\Password;
use Filament\Forms\Components\Actions\Action;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;

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
    MountableAction::macro('requiresPasswordConfirmation', function (): static {
      $this
        ->form([
          Password::make('password')
            ->required()
            ->minLength(8)
            ->hidePasswordManagerIcons()
            ->label('Confirm Password')
        ])
        ->modalDescription('Confirm your password to perform this action.')
        ->action(function (array $data, Model $record, MountableAction $action) {
          if (!Hash::check($data['password'], auth()->user()->password)) {
            Notification::make()
              ->danger()
              ->title('Woops...')
              ->body('Wrong password!')
              ->send();
            $action->halt();
          }
          $record->delete();
        });
      return $this;
    });

    Field::macro('loadingIndicator', function (?string $label = 'Loading...', ?string $target = null, bool $onBlur = false): static {
      $target = $target ?? $this->name;

      $html = new HtmlString(
        Blade::render(<<<HTML
          <span class="flex">
            <x-filament::loading-indicator class="h-5 w-5 mr-1" wire:loading wire:target="data.{$target}"/>
            <strong wire:loading wire:target="data.{$target}">{$label}</strong>
          </span>
        HTML)
      );

      $this
        ->live($onBlur)
        ->hint($html);

      return $this;
    });

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

    Filter::macro('approval', function (): static {
      $this
        ->label('Approved')
        ->query(fn(Builder $query): Builder => ApprovedScope::getQuery($query));

      return $this;
    });

    PhoneInput::macro('indonesian', function (): static {
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
