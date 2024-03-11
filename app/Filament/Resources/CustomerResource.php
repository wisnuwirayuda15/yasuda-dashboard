<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\InstitutionCategory;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
          ->maxLength(255),
        Forms\Components\TextInput::make('institution')
          ->required()
          ->maxLength(255),
        Forms\Components\Select::make('category')
          ->required()
          ->options(InstitutionCategory::class)
          ->native(false),
        Forms\Components\TextInput::make('email')
          ->email()
          ->maxLength(255),
        Forms\Components\TextInput::make('phone')
          ->tel()
          ->required()
          ->maxLength(255),
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
        Tables\Columns\TextColumn::make('phone')
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
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
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
