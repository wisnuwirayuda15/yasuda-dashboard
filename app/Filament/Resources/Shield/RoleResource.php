<?php

namespace App\Filament\Resources\Shield;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Permission;
use BezhanSalleh\FilamentShield\Support\Utils;
use App\Filament\Resources\Shield\RoleResource\Pages;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Forms\ShieldSelectAllToggle;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RoleResource extends Resource implements HasShieldPermissions
{
  protected static ?string $recordTitleAttribute = 'name';

  public static function getPermissionPrefixes(): array
  {
    return [
      'view',
      'view_any',
      'create',
      'update',
      'delete',
      'delete_any',
    ];
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      Grid::make()
        ->schema([
          Section::make()
            ->columns(['sm' => 2, 'lg' => 3])
            ->schema([
              TextInput::make('name')
                ->label(__('filament-shield::filament-shield.field.name'))
                ->unique(ignoreRecord: true)
                ->required()
                ->disabled(fn(?Role $record): bool => $record?->id === 1)
                ->helperText(fn(?Role $record): string|null => $record?->id === 1 ? 'Demi keamanan sistem, anda tidak dapat merubah nama role ini.' : null)
                ->maxLength(255),
              TextInput::make('guard_name')
                ->label(__('filament-shield::filament-shield.field.guard_name'))
                ->default(Utils::getFilamentAuthGuard() ?? 'web')
                ->helperText('Guard name terisi secara otomatis.')
                // ->nullable()
                ->disabled()
                ->dehydrated()
                ->maxLength(3),
              ShieldSelectAllToggle::make('select_all')
                ->onIcon('heroicon-s-shield-check')
                ->offIcon('heroicon-s-shield-exclamation')
                ->label(__('filament-shield::filament-shield.field.select_all.name'))
                ->helperText(fn(): HtmlString => new HtmlString(__('filament-shield::filament-shield.field.select_all.message')))
                ->dehydrated(fn($state): bool => $state),
            ]),
        ]),
      Tabs::make('Permissions')
        ->columnSpanFull()
        ->tabs([
          static::getTabFormComponentForResources(),
          static::getTabFormComponentForPage(),
          static::getTabFormComponentForWidget(),
          static::getTabFormComponentForCustomPermissions(),
        ]),
    ]);
  }


  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->label(__('filament-shield::filament-shield.column.name'))
          ->formatStateUsing(fn(string $state): string => Str::headline($state))
          ->sortable()
          ->searchable(),
        TextColumn::make('guard_name')
          ->badge()
          ->label(__('filament-shield::filament-shield.column.guard_name')),
        TextColumn::make('users_count')
          ->badge()
          ->sortable()
          ->label('Users')
          ->counts('users')
          ->colors(['warning']),
        TextColumn::make('permissions_count')
          ->badge()
          ->sortable()
          ->label(__('filament-shield::filament-shield.column.permissions'))
          ->counts('permissions')
          ->colors(['success']),
        // TextColumn::make('updated_at')
        //   ->label(__('filament-shield::filament-shield.column.updated_at'))
        //   ->dateTime(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make()
            ->hidden(fn(Role $record): bool => $record->id === 1),
          Tables\Actions\DeleteAction::make()
            ->hidden(fn(Role $record): bool => $record->users()->exists() || $record->id === 1)
            ->before(function (Role $record, Tables\Actions\DeleteAction $action) {
              if ($record->users()->exists()) {
                $role = Str::headline($record->name);
                Notification::make()
                  ->danger()
                  ->title('Failed')
                  ->body("<strong>{$role}</strong> role cannot be deleted because it is assigned to some users.")
                  ->send();
                $action->cancel();
              }
            }),
        ])
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListRoles::route('/'),
      'create' => Pages\CreateRole::route('/create'),
      'view' => Pages\ViewRole::route('/{record}'),
      'edit' => Pages\EditRole::route('/{record}/edit'),
    ];
  }

  public static function getCluster(): ?string
  {
    return Utils::getResourceCluster() ?? static::$cluster;
  }

  public static function getModel(): string
  {
    return Utils::getRoleModel();
  }

  public static function getModelLabel(): string
  {
    return __('filament-shield::filament-shield.resource.label.role');
  }

  public static function getPluralModelLabel(): string
  {
    return __('filament-shield::filament-shield.resource.label.roles');
  }

  public static function shouldRegisterNavigation(): bool
  {
    return Utils::isResourceNavigationRegistered();
  }

  public static function getNavigationGroup(): ?string
  {
    return Utils::isResourceNavigationGroupEnabled()
      ? __('filament-shield::filament-shield.nav.group')
      : '';
  }

  public static function getNavigationLabel(): string
  {
    return __('filament-shield::filament-shield.nav.role.label');
  }

  public static function getNavigationIcon(): string
  {
    return __('filament-shield::filament-shield.nav.role.icon');
  }

  public static function getNavigationSort(): ?int
  {
    return Utils::getResourceNavigationSort();
  }

  public static function getSlug(): string
  {
    return Utils::getResourceSlug();
  }

  public static function getNavigationBadge(): ?string
  {
    return null;

    // return Utils::isResourceNavigationBadgeEnabled()
    //   ? strval(static::getEloquentQuery()->count())
    //   : null;
  }

  public static function isScopedToTenant(): bool
  {
    return Utils::isScopedToTenant();
  }

  public static function canGloballySearch(): bool
  {
    return Utils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
  }

  public static function getResourceEntitiesSchema(): ?array
  {
    return collect(FilamentShield::getResources())
      ->sortKeys()
      ->map(function ($entity) {
        $sectionLabel = strval(
          static::shield()->hasLocalizedPermissionLabels()
          ? FilamentShield::getLocalizedResourceLabel($entity['fqcn'])
          : $entity['model']
        );

        return Forms\Components\Section::make($sectionLabel)
          ->description(fn() => new HtmlString('<span style="word-break: break-word;">' . Utils::showModelPath($entity['fqcn']) . '</span>'))
          ->compact()
          ->schema([
            static::getCheckBoxListComponentForResource($entity),
          ])
          ->columnSpan(static::shield()->getSectionColumnSpan())
          ->collapsible();
      })
      ->toArray();
  }

  public static function getResourceTabBadgeCount(): ?int
  {
    return collect(FilamentShield::getResources())
      ->map(fn($resource) => count(static::getResourcePermissionOptions($resource)))
      ->sum();
  }

  public static function getResourcePermissionOptions(array $entity): array
  {
    return collect(Utils::getResourcePermissionPrefixes($entity['fqcn']))
      ->flatMap(function ($permission) use ($entity) {
        $name = $permission . '_' . $entity['resource'];
        $label = static::shield()->hasLocalizedPermissionLabels()
          ? FilamentShield::getLocalizedResourcePermissionLabel($permission)
          : $name;

        return [
          $name => $label,
        ];
      })
      ->toArray();
  }

  public static function setPermissionStateForRecordPermissions(Component $component, string $operation, array $permissions, ?Model $record): void
  {

    if (in_array($operation, ['edit', 'view'])) {

      if (blank($record)) {
        return;
      }
      if ($component->isVisible() && count($permissions) > 0) {
        $component->state(
          collect($permissions)
            /** @phpstan-ignore-next-line */
            ->filter(fn($value, $key) => $record->checkPermissionTo($key))
            ->keys()
            ->toArray()
        );
      }
    }
  }

  public static function getPageOptions(): array
  {
    return collect(FilamentShield::getPages())
      ->flatMap(fn($page) => [
        $page['permission'] => static::shield()->hasLocalizedPermissionLabels()
          ? FilamentShield::getLocalizedPageLabel($page['class'])
          : $page['permission'],
      ])
      ->toArray();
  }

  public static function getWidgetOptions(): array
  {
    return collect(FilamentShield::getWidgets())
      ->flatMap(fn($widget) => [
        $widget['permission'] => static::shield()->hasLocalizedPermissionLabels()
          ? FilamentShield::getLocalizedWidgetLabel($widget['class'])
          : $widget['permission'],
      ])
      ->toArray();
  }

  public static function getCustomPermissionOptions(): ?array
  {
    return FilamentShield::getCustomPermissions()
      ->mapWithKeys(fn($customPermission) => [
        $customPermission => static::shield()->hasLocalizedPermissionLabels() ? str($customPermission)->headline()->toString() : $customPermission,
      ])
      ->toArray();
  }

  public static function getTabFormComponentForResources(): Component
  {
    return static::shield()->hasSimpleResourcePermissionView()
      ? static::getTabFormComponentForSimpleResourcePermissionsView()
      : Forms\Components\Tabs\Tab::make('resources')
        ->label(__('filament-shield::filament-shield.resources'))
        ->visible(fn(): bool => (bool) Utils::isResourceEntityEnabled())
        ->badge(static::getResourceTabBadgeCount())
        ->schema([
          Forms\Components\Grid::make()
            ->schema(static::getResourceEntitiesSchema())
            ->columns(static::shield()->getGridColumns()),
        ]);
  }

  public static function getCheckBoxListComponentForResource(array $entity): Component
  {
    $permissionsArray = static::getResourcePermissionOptions($entity);

    return static::getCheckboxListFormComponent($entity['resource'], $permissionsArray, false);
  }

  public static function getTabFormComponentForPage(): Component
  {
    $options = static::getPageOptions();
    $count = count($options);

    return Forms\Components\Tabs\Tab::make('pages')
      ->label(__('filament-shield::filament-shield.pages'))
      ->visible(fn(): bool => (bool) Utils::isPageEntityEnabled() && $count > 0)
      ->badge($count)
      ->schema([
        static::getCheckboxListFormComponent('pages_tab', $options),
      ]);
  }

  public static function getTabFormComponentForWidget(): Component
  {
    $options = static::getWidgetOptions();
    $count = count($options);

    return Forms\Components\Tabs\Tab::make('widgets')
      ->label(__('filament-shield::filament-shield.widgets'))
      ->visible(fn(): bool => (bool) Utils::isWidgetEntityEnabled() && $count > 0)
      ->badge($count)
      ->schema([
        static::getCheckboxListFormComponent('widgets_tab', $options),
      ]);
  }

  public static function getTabFormComponentForCustomPermissions(): Component
  {
    $options = static::getCustomPermissionOptions();
    $count = count($options);

    return Forms\Components\Tabs\Tab::make('custom')
      ->label(__('filament-shield::filament-shield.custom'))
      ->visible(fn(): bool => (bool) Utils::isCustomPermissionEntityEnabled() && $count > 0)
      ->badge($count)
      ->schema([
        static::getCheckboxListFormComponent('custom_permissions', $options),
      ]);
  }

  public static function getTabFormComponentForSimpleResourcePermissionsView(): Component
  {
    $options = FilamentShield::getAllResourcePermissions();
    $count = count($options);

    return Forms\Components\Tabs\Tab::make('resources')
      ->label(__('filament-shield::filament-shield.resources'))
      ->visible(fn(): bool => (bool) Utils::isResourceEntityEnabled() && $count > 0)
      ->badge($count)
      ->schema([
        static::getCheckboxListFormComponent('resources_tab', $options),
      ]);
  }

  public static function getCheckboxListFormComponent(string $name, array $options, bool $searchable = true): Component
  {
    return Forms\Components\CheckboxList::make($name)
      ->label('')
      ->options(fn(): array => $options)
      ->searchable($searchable)
      ->afterStateHydrated(
        fn(Component $component, string $operation, ?Model $record) => static::setPermissionStateForRecordPermissions(
          component: $component,
          operation: $operation,
          permissions: $options,
          record: $record
        )
      )
      ->dehydrated(fn($state) => !blank($state))
      ->bulkToggleable()
      ->gridDirection('row')
      ->columns(static::shield()->getCheckboxListColumns())
      ->columnSpan(static::shield()->getCheckboxListColumnSpan());
  }

  public static function shield(): FilamentShieldPlugin
  {
    return FilamentShieldPlugin::get();
  }
}
