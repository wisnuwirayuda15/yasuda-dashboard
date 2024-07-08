<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use App\Models\District;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\CustomerStatus;
use App\Enums\CustomerCategory;
use Filament\Resources\Resource;
use Dotswan\MapPicker\Fields\Map;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\CustomerExporter;
use Filament\Forms\Components\ToggleButtons;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Filament\Resources\CustomerResource\Pages;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
  protected static ?string $model = Customer::class;

  protected static ?string $navigationIcon = 'fas-users';

  protected static ?string $recordTitleAttribute = 'name';

  protected static ?int $navigationSort = -10;

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
  }

  public static function getGlobalSearchResultDetails(Model $record): array
  {
    return [
      $record->code,
      $record->category->getlabel(),
    ];
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        static::getBasicInformationSection(),
        static::getLocationInformationSection(),
        static::getContactInformationSection(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->paginationPageOptions([5, 10, 15, 20, 30, 50])
      ->columns([
        TextColumn::make('code')
          ->badge()
          ->searchable(),
        TextColumn::make('name')
          ->sortable()
          ->searchable(),
        TextColumn::make('category')
          ->badge()
          ->searchable(),
        TextColumn::make('status')
          ->badge()
          ->tooltip('Ubah status')
          ->action(static::getChangeStatusAction()),
        TextColumn::make('address')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('regency.name')
          ->searchable(),
        TextColumn::make('district.name')
          ->searchable(),
        TextColumn::make('headmaster')
          ->searchable(),
        TextColumn::make('operator')
          ->searchable(),
        PhoneColumn::make('phone')
          ->searchable(),
        TextColumn::make('email')
          ->searchable(),
        TextColumn::make('balance')
          ->label('Saldo')
          ->state(fn(Customer $record) => $record->getBalance())
          ->money('IDR'),
        TextColumn::make('lat')
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('lng')
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        SelectFilter::make('category')
          ->multiple()
          ->options(CustomerCategory::class),
      ])
      ->headerActions([
        ExportAction::make()
          ->hidden(fn(): bool => static::getModel()::count() === 0)
          ->exporter(CustomerExporter::class)
          ->label('Export')
          ->color('success')
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
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
      'index' => Pages\ListCustomers::route('/'),
      'create' => Pages\CreateCustomer::route('/create'),
      'view' => Pages\ViewCustomer::route('/{record}'),
      'edit' => Pages\EditCustomer::route('/{record}/edit'),
    ];
  }

  private static function getBasicInformationSection(): Section
  {
    return Section::make('Basic Information')
      ->schema([
        TextInput::make('code')
          ->live(true)
          ->code(fn(Get $get) => get_code(new Customer, $get('category') ?? CustomerCategory::TK->value)),
        TextInput::make('name')
          ->required()
          ->live()
          ->maxLength(255),
        TextInput::make('address')
          ->required()
          ->maxLength(255),
        ToggleButtons::make('category')
          ->required()
          ->inline()
          ->disabledOn('edit')
          ->helperText("Category can't be edited.")
          ->options(CustomerCategory::class)
          ->default(CustomerCategory::TK->value)
          ->afterStateUpdated(fn(Set $set, string $state) => $set('code', get_code(new Customer, $state)))
          ->loadingIndicator(),
        ToggleButtons::make('status')
          ->required()
          ->inline()
          ->default(CustomerStatus::CANDIDATE->value)
          ->options(CustomerStatus::class),
      ]);
  }

  private static function getLocationInformationSection(): Section
  {
    return Section::make('Location Information')
      ->schema([
        Select::make('regency_id')
          ->label('Kabupaten / Kota')
          ->required()
          ->live()
          ->relationship('regency', 'name')
          ->afterStateUpdated(fn(Set $set) => $set('district_id', null)),
        Select::make('district_id')
          ->label('Kecamatan')
          ->required()
          ->live()
          ->relationship('district', 'name', fn(Get $get, Builder $query) => $query->where('regency_id', $get('regency_id'))),
        TextInput::make('lat')
          ->numeric()
        // ->maxLength(255)
        ,
        TextInput::make('lng')
          ->numeric()
        // ->maxLength(255)
        ,
        Map::make('location')
          ->afterStateUpdated(function (Set $set, ?array $state) {
            $set('lat', $state['lat']);
            $set('lng', $state['lng']);
          })
          ->hiddenOn('view')
          ->columnSpanFull()
          ->extraAttributes(['class' => 'rounded-md'])
          ->liveLocation()
          ->showMyLocationButton(),
      ]);
  }

  private static function getContactInformationSection(): Section
  {
    return Section::make('Contact Information')
      ->schema([
        TextInput::make('headmaster')
          ->required()
          ->maxLength(255),
        TextInput::make('operator')
          ->required()
          ->maxLength(255),
        PhoneInput::make('phone')
          ->required()
          ->indonesian(),
        TextInput::make('email')
          ->email()
          ->maxLength(255),
      ]);
  }

  public static function getChangeStatusAction(): Action
  {
    return Action::make('change_status')
      ->label('Ubah Status')
      ->form([
        ToggleButtons::make('status')
          ->required()
          ->inline()
          ->hiddenLabel()
          ->default(fn(Customer $record) => $record->status)
          ->options(CustomerStatus::class),
      ])
      ->action(function (array $data, Customer $record): void {
        $record->update(['status' => $data['status']]);
        Notification::make()
          ->success()
          ->title('Success')
          ->body('Status berhasil diubah.')
          ->send();
      });
  }
}
