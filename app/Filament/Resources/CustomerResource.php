<?php

namespace App\Filament\Resources;

use Filament\Forms;
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
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
  protected static ?string $model = Customer::class;

  protected static ?string $navigationIcon = 'fas-users';

  public static function form(Form $form): Form
  {
    return $form->schema([
      self::getBasicInformationSection(),
      self::getLocationInformationSection(),
      self::getContactInformationSection(),
    ]);
  }

  private static function getBasicInformationSection(): Section
  {
    return Forms\Components\Section::make('Basic Information')
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->live()
          ->default(get_code(new Customer, CustomerCategory::TK->value . '-'))
          ->helperText('Code is generated automatically based on the categories you choose.')
          ->unique(Customer::class, 'code', ignoreRecord: true),
        Forms\Components\TextInput::make('name')
          ->required()
          ->live()
          ->afterStateUpdated(fn(string $operation) => dd($operation))
          ->maxLength(255),
        Forms\Components\TextInput::make('address')
          ->required()
          ->maxLength(255),
        Forms\Components\ToggleButtons::make('category')
          ->required()
          ->live()
          ->inline()
          ->disabledOn(['edit', 'editOption', 'editOption.editOption', 'createOption.editOption'])
          ->helperText("Category can't be edited.")
          ->options(CustomerCategory::class)
          ->default(CustomerCategory::TK->value)
          ->afterStateUpdated(fn(Set $set, string $state) => $set('code', get_code(new Customer, $state . '-'))),
        Forms\Components\ToggleButtons::make('status')
          ->required()
          ->inline()
          ->default(CustomerStatus::NEW ->value)
          ->options(CustomerStatus::class),
      ]);
  }

  private static function getLocationInformationSection(): Section
  {
    return Forms\Components\Section::make('Location Information')
      ->schema([
        Forms\Components\Select::make('regency_id')
          ->label('Kabupaten / Kota')
          ->required()
          ->live()
          ->searchable()
          ->native(false)
          ->preload()
          ->relationship('regency', 'name')
          ->afterStateUpdated(fn(Set $set) => $set('district_id', null)),
        Forms\Components\Select::make('district_id')
          ->label('Kecamatan')
          ->required()
          ->live()
          ->searchable()
          ->native(false)
          ->options(fn(Get $get) => District::where('regency_id', $get('regency_id'))->orderBy('name')->pluck('name', 'id')),
        Forms\Components\TextInput::make('lat')
          ->maxLength(255),
        Forms\Components\TextInput::make('lng')
          ->maxLength(255),
        Map::make('location')
          ->afterStateUpdated(function (Set $set, ?array $state) {
            $set('lat', $state['lat']);
            $set('lng', $state['lng']);
          })
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
    return Forms\Components\Section::make('Contact Information')
      ->schema([
        Forms\Components\TextInput::make('headmaster')
          ->required()
          ->maxLength(255),
        Forms\Components\TextInput::make('operator')
          ->required()
          ->maxLength(255),
        PhoneInput::make('phone')
          ->focusNumberFormat(PhoneInputNumberType::E164)
          ->defaultCountry('ID')
          ->initialCountry('id')
          ->showSelectedDialCode(true)
          ->formatAsYouType(false)
          ->required()
          ->rules('phone:mobile'),
        Forms\Components\TextInput::make('email')
          ->email()
          ->maxLength(255),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table->columns([
      Tables\Columns\TextColumn::make('code')
        ->searchable(),
      Tables\Columns\TextColumn::make('category')
        ->badge()
        ->searchable(),
      Tables\Columns\TextColumn::make('name')
        ->searchable(),
      Tables\Columns\TextColumn::make('address')
        ->searchable()
        ->toggleable(isToggledHiddenByDefault: true),
      Tables\Columns\TextColumn::make('regency.name')
        ->searchable(),
      Tables\Columns\TextColumn::make('district.name')
        ->searchable(),
      Tables\Columns\TextColumn::make('headmaster')
        ->searchable(),
      Tables\Columns\TextColumn::make('operator')
        ->searchable(),
      Tables\Columns\TextColumn::make('phone')
        ->searchable(),
      Tables\Columns\TextColumn::make('email')
        ->searchable(),
      Tables\Columns\TextColumn::make('status')
        ->badge()
        ->searchable(),
      Tables\Columns\TextColumn::make('lat')
        ->searchable()
        ->toggleable(isToggledHiddenByDefault: true),
      Tables\Columns\TextColumn::make('lng')
        ->searchable()
        ->toggleable(isToggledHiddenByDefault: true),
      Tables\Columns\TextColumn::make('created_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
      Tables\Columns\TextColumn::make('updated_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
      Tables\Columns\TextColumn::make('deleted_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
    ])
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
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

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
