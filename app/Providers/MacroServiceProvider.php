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
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Rawilk\FilamentPasswordInput\Password;
use Filament\Forms\Components\Actions\Action;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
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
        ->modalDescription('Mohon konfirmasi password anda untuk melakukan aksi ini.')
        ->action(function (array $data, Model $record, MountableAction $action) {
          if (!Hash::check($data['password'], auth()->user()->password)) {
            Notification::make()
              ->danger()
              ->title('Woops...')
              ->body('Password anda salah!')
              ->send();
            $action->halt();
          }
          $record->delete();
        });

      return $this;
    });

    Field::macro('loadingIndicator', function (?string $label = 'Loading...', ?string $target = null, bool $onBlur = false, bool $condition = true): static {
      if (!$condition) {
        return $this;
      }

      $target = $target ?? $this->name;

      $html = new HtmlString(
        Blade::render(<<<HTML
          <span class="flex">
            <x-filament::loading-indicator class="h-5 w-5 mr-1" wire:loading wire:target="data.{$target}"/>
            <strong wire:loading wire:target="data.{$target}">{$label}</strong>
          </span>
        HTML)
      );

      $this->live($onBlur)->hint($html);

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

    TextInput::macro('currency', function (int|bool|Closure $minValue = 0, string|Closure|null $prefix = 'Rp', bool $minusValidation = true): static {
      $this
        ->live(true)
        ->numeric()
        ->prefix($prefix);

      if (is_numeric($minValue)) {
        $this->minValue($minValue);
      }

      if ($minusValidation) {
        $this
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
      }

      return $this;
    });

    TextInput::macro('code', function (string|Closure $code, bool $editable = true, bool $generateable = false, bool $resetable = true): static {
      $edit = ['edit', 'editOption', 'editOption.editOption', 'createOption.editOption'];
      $view = ['view', 'viewOption', 'viewOption.viewOption', 'createOption.viewOption'];

      $this
        ->required()
        ->disabled()
        ->dehydrated()
        ->default(fn() => $code)
        ->unique(ignoreRecord: true)
        ->hiddenOn($edit)
        ->helperText(fn(string $operation) => !in_array($operation, $view) ? 'Code otomatis terbuat' . ($editable ? ' dan bisa diubah.' : '.') : null)
        ->hintActions([
          Action::make('edit')
            ->icon('tabler-edit')
            ->hidden(fn(string $operation) => in_array($operation, $view))
            ->visible($editable)
            ->action(fn(TextInput $component) => $component->disabled(false)),
          Action::make('generate')
            ->icon('fas-random')
            ->hidden(fn(string $operation) => in_array($operation, $view))
            ->visible($generateable)
            ->action(function (TextInput $component, Set $set) use ($code) {
              $set($component, $code);
            }),
          Action::make('reset')
            ->icon('gmdi-restart-alt-o')
            ->hidden(fn(string $operation) => in_array($operation, $view))
            ->visible($resetable)
            ->action(function (TextInput $component, Set $set) use ($code) {
              $set($component, $code);
            })
        ]);

      return $this;
    });

    ImageColumn::macro('preview', function (string $label = 'Image Preview'): static {
      $image = $this->name;

      $this
        ->tooltip(fn(Model $record) => blank($record->$image) ? 'No image' : 'Preview image')
        ->action(MediaAction::make('image_preview')
          ->hidden(fn(Model $record) => blank($record->$image))
          ->modalWidth('full')
          ->label($label)
          ->media(fn(Model $record) =>
            str_starts_with($record->$image, 'http')
            ? $record->$image
            : Storage::url($record->$image)));

      return $this;
    });

    Checkbox::macro('confirmation', function (): static {
      $this
        ->required()
        ->hiddenOn('view')
        ->label('Semua perhitungan sudah dicek dan tidak ada kesalahan');

      return $this;
    });

    Checkbox::macro('submission', function (): static {
      $this
        ->label('Submit data ini')
        ->visibleOn('create')
        ->hidden(auth()->user()->isSuperAdmin());

      return $this;
    });

    Repeater::macro('resetAction', function (): static {
      $this
        ->hintAction(
          Action::make('reset')
            ->button()
            ->color('danger')
            ->icon('gmdi-restart-alt-tt')
            ->requiresConfirmation()
            ->modalHeading('Are you sure?')
            ->modalDescription('All existing items will be removed.')
            ->tooltip('Remove all items')
            ->visible(fn(?array $state, string $operation) => $operation !== 'view' && count($state) > 1)
            ->action(fn(Set $set) => $set($this, [])),
        );

      return $this;
    });

    Filter::macro('approved', function (): static {
      $this
        ->label('Approved')
        ->query(fn(Builder $query): Builder => ApprovedScope::getQuery($query));

      return $this;
    });

    Filter::macro('notApproved', function (): static {
      $this
        ->label('Not Approved')
        ->query(fn(Builder $query): Builder => ApprovedScope::getQuery($query, true));

      return $this;
    });

    PhoneInput::macro('indonesian', function (): static {
      $this
        ->defaultCountry('ID')
        ->initialCountry('id')
        // ->rules('phone:mobile')
        ->formatAsYouType(false)
        ->showSelectedDialCode(true)
        ->focusNumberFormat(PhoneInputNumberType::E164);

      return $this;
    });
  }
}
