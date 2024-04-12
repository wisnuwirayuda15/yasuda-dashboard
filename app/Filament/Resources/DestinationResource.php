<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Enums\DestinationType;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\DestinationResource\Pages;
use App\Filament\Resources\DestinationResource\RelationManagers;

class DestinationResource extends Resource
{
  protected static ?string $model = Destination::class;

  protected static ?string $navigationIcon = 'fas-map-location-dot';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(255),
        Forms\Components\ToggleButtons::make('type')
          ->required()
          ->inline()
          ->default('new')
          ->options(DestinationType::class),
        Forms\Components\TextInput::make('marketing_name')
          ->required()
          ->maxLength(255),
        PhoneInput::make('marketing_phone')
          ->focusNumberFormat(PhoneInputNumberType::E164)
          ->defaultCountry('ID')
          ->initialCountry('id')
          ->showSelectedDialCode(true)
          ->formatAsYouType(false)
          ->required()
          ->rules('phone:mobile'),
        Forms\Components\TextInput::make('weekday_price')
          ->required()
          ->numeric()
          ->prefix('Rp'),
        Forms\Components\TextInput::make('weekend_price')
          ->required()
          ->numeric()
          ->prefix('Rp'),
        Forms\Components\TextInput::make('high_season_price')
          ->numeric()
          ->prefix('Rp'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('type')
          ->badge()
          ->searchable(),
        Tables\Columns\TextColumn::make('marketing_name')
          ->searchable(),
        Tables\Columns\TextColumn::make('marketing_phone')
          ->searchable(),
        Tables\Columns\TextColumn::make('weekday_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        Tables\Columns\TextColumn::make('weekend_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        Tables\Columns\TextColumn::make('high_season_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
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
      'index' => Pages\ListDestinations::route('/'),
      'create' => Pages\CreateDestination::route('/create'),
      'view' => Pages\ViewDestination::route('/{record}'),
      'edit' => Pages\EditDestination::route('/{record}/edit'),
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
