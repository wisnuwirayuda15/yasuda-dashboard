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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
  protected static ?string $model = Customer::class;

  protected static ?string $navigationIcon = 'fas-users';

  protected static ?string $recordTitleAttribute = 'name';

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
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
        self::getBasicInformationSection(),
        self::getLocationInformationSection(),
        self::getContactInformationSection(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('code')
          ->searchable(),
        TextColumn::make('category')
          ->badge()
          ->searchable(),
        TextColumn::make('name')
          ->searchable(),
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
        TextColumn::make('phone')
          ->searchable(),
        TextColumn::make('email')
          ->searchable(),
        TextColumn::make('status')
          ->badge()
          ->searchable(),
        TextColumn::make('loyalty_point')
          ->numeric(),
        TextColumn::make('lat')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('lng')
          ->searchable()
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
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
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
          ->live()
          ->code(get_code(new Customer, CustomerCategory::TK->value)),
        TextInput::make('name')
          ->required()
          ->live()
          ->maxLength(255),
        TextInput::make('address')
          ->required()
          ->maxLength(255),
        ToggleButtons::make('category')
          ->required()
          ->live()
          ->inline()
          ->disabledOn(['edit', 'editOption', 'editOption.editOption', 'createOption.editOption'])
          ->helperText("Category can't be edited.")
          ->options(CustomerCategory::class)
          ->default(CustomerCategory::TK->value)
          ->afterStateUpdated(fn(Set $set, string $state) => $set('code', get_code(new Customer, $state))),
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
          ->options(fn(Get $get) => District::where('regency_id', $get('regency_id'))->orderBy('name')->pluck('name', 'id')),
        TextInput::make('lat')
          ->maxLength(255),
        TextInput::make('lng')
          ->maxLength(255),
        Map::make('location')
          ->afterStateUpdated(function (Set $set, ?array $state) {
            $set('lat', $state['lat']);
            $set('lng', $state['lng']);
          })
          ->extraAttributes(['class' => 'rounded-md'])
          ->liveLocation()
          ->columnSpanFull()
          ->showMyLocationButton()
          ->showMarker()
          ->draggable()
          ->detectRetina()
          ->showZoomControl()
          ->showFullscreenControl(),
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
          ->idDefaultFormat(),
        TextInput::make('email')
          ->email()
          ->maxLength(255),
      ]);
  }
}
