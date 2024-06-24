<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Enums\DestinationType;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Filament\Resources\DestinationResource\Pages;
use App\Filament\Resources\DestinationResource\RelationManagers;

class DestinationResource extends Resource
{
  protected static ?string $model = Destination::class;

  protected static ?string $navigationIcon = 'fas-map-location-dot';

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('name')
          ->required()
          ->maxLength(255),
        Select::make('type')
          ->required()
          ->default(DestinationType::SISWA_ONLY->value)
          ->options(DestinationType::class),
        TextInput::make('marketing_name')
          ->required()
          ->maxLength(255),
        PhoneInput::make('marketing_phone')
          ->required()
          ->idDefaultFormat(),
        TextInput::make('weekday_price')
          ->required()
          ->default(0)
          ->currency(minValue: 0),
        TextInput::make('weekend_price')
          ->required()
          ->default(0)
          ->currency(minValue: 0),
        TextInput::make('high_season_price')
          ->default(0)
          ->currency(minValue: 0),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->searchable(),
        TextColumn::make('type')
          ->badge()
          ->searchable(),
        TextColumn::make('marketing_name')
          ->searchable(),
        TextColumn::make('marketing_phone')
          ->searchable(),
        TextColumn::make('weekday_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        TextColumn::make('weekend_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        TextColumn::make('high_season_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
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
}
