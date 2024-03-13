<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\BusStatus;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BusAvailability;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BusAvailabilityResource\Pages;
use App\Filament\Resources\BusAvailabilityResource\RelationManagers;

class BusAvailabilityResource extends Resource
{
  protected static ?string $model = BusAvailability::class;

  protected static ?string $navigationGroup = 'Master Data';

  protected static ?string $navigationLabel = 'Ketersediaan Bus';

  protected static ?string $navigationIcon = 'mdi-bus-clock';

  protected static ?string $activeNavigationIcon = 'mdi-bus-clock';

  protected static ?int $navigationSort = 2;

  // protected static bool $shouldRegisterNavigation = false;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Select::make('bus_id')
          ->relationship('bus', 'name')
          ->required()
          ->native(false),
        Forms\Components\Select::make('status')
          ->required()
          ->native(false)
          ->options(BusStatus::class)
          ->default('available')
          ->live()
          ->prefixIcon(function ($state): string {
            if ($state === 'available') {
              return 'heroicon-m-check-circle';
            } else if ($state === 'on_trip') {
              return 'heroicon-m-information-circle';
            } else if ($state === 'canceled') {
              return 'heroicon-m-x-circle';
            } else {
              return '';
            }
          })
          ->prefixIconColor(function ($state): string {
            if ($state === 'available') {
              return 'success';
            } else if ($state === 'on_trip') {
              return 'info';
            } else if ($state === 'canceled') {
              return 'danger';
            } else {
              return '';
            }
          }),
        Forms\Components\DatePicker::make('date')
          ->required()
          ->native(false)
          ->closeOnDateSelection()
          ->default(now())
          ->prefixIcon('heroicon-m-calendar-days'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('bus.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('date')
        ->label('Tgl Tersedia')
          ->date('d M Y')
          ->sortable(),
        Tables\Columns\TextColumn::make('status')
          ->searchable()
          ->badge(),
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
      'index' => Pages\ListBusAvailabilities::route('/'),
      'create' => Pages\CreateBusAvailability::route('/create'),
      'view' => Pages\ViewBusAvailability::route('/{record}'),
      'edit' => Pages\EditBusAvailability::route('/{record}/edit'),
    ];
  }
}
