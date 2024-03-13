<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\InstitutionCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Filament\Resources\CustomerResource\Pages;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
  protected static ?string $model = Customer::class;

  protected static ?string $navigationGroup = 'Master Data';

  protected static ?string $recordTitleAttribute = 'name';

  protected static ?string $navigationIcon = 'heroicon-o-user-group';

  protected static ?string $activeNavigationIcon = 'heroicon-s-user-group';

  protected static ?int $navigationSort = 1;

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('name')
          ->required()
          ->columnSpanFull()
          ->maxLength(255),
        Forms\Components\TextInput::make('institution')
          ->required()
          ->columnSpanFull()
          ->maxLength(255),
        Forms\Components\Select::make('category')
          ->required()
          ->options(InstitutionCategory::class)
          ->columnSpanFull()
          ->native(false),
        Forms\Components\TextInput::make('email')
          ->email()
          ->columnSpanFull()
          ->maxLength(255),
        PhoneInput::make('phone')
          ->focusNumberFormat(PhoneInputNumberType::E164)
          ->defaultCountry('ID')
          ->initialCountry('id')
          ->columnSpanFull()
          ->showSelectedDialCode(true)
          ->formatAsYouType(false)
          ->required()
          ->rules('phone:mobile')
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('institution')
          ->searchable(),
        Tables\Columns\TextColumn::make('category')
          ->searchable()
          ->badge(),
        Tables\Columns\TextColumn::make('email')
          ->searchable(),
        PhoneColumn::make('phone')
          ->displayFormat(PhoneInputNumberType::NATIONAL)
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make()
        ])
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
}
