<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderFleetResource\Pages;
use App\Filament\Resources\OrderFleetResource\RelationManagers;

class OrderFleetResource extends Resource
{
  protected static ?string $model = OrderFleet::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->live()
          ->hiddenOn('edit')
          ->helperText('Code are generated automatically.')
          ->unique(OrderFleet::class, 'code', ignoreRecord: true)
          ->default(get_code(new OrderFleet, 'OF-')),
        Forms\Components\Select::make('order_id')
          ->relationship('order', 'code')
          ->native(false)
          ->prefixIcon('heroicon-s-check-badge')
          ->editOptionForm(fn(Form $form) => OrderResource::form($form))
          ->createOptionForm(fn(Form $form) => OrderResource::form($form))
          ->editOptionModalHeading('Edit Order')
          ->createOptionModalHeading('Create Order')
          ->getOptionLabelFromRecordUsing(fn(Model $record) => "($record->code) {$record->customer->name}"),
        Forms\Components\Select::make('fleet_id')
          ->required()
          ->native(false)
          ->relationship('fleet', 'name')
          ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} - {$record->seat_set->getLabel()}"),
        Forms\Components\DatePicker::make('trip_date')
          ->required()
          ->native(false)
          ->closeOnDateSelection()
          ->prefixIcon('heroicon-s-calendar-days')
          ->displayFormat('d mm Y'),
        Forms\Components\TextInput::make('duration')
          ->required()
          ->default(1)
          ->numeric()
          ->minValue(1)
          ->maxValue(100)
          ->suffix('Hari')
          ->prefixIcon('heroicon-s-clock')
          ->live()
          ->afterStateUpdated(fn(?int $state, Set $set) => $state <= 1 ? $set('duration', 1) : $state > 100 && $set('duration', 100)),
        Forms\Components\TextInput::make('payment_status')
          ->required()
          ->maxLength(50),
        Forms\Components\DateTimePicker::make('payment_date'),
        Forms\Components\TextInput::make('payment_amount')
          ->numeric(),
        Forms\Components\Select::make('tour_leader_id')
          ->relationship('tourLeader', 'name'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('code')
          ->searchable(),
        Tables\Columns\TextColumn::make('order.id')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('fleet.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('trip_date')
          ->dateTime()
          ->sortable(),
        Tables\Columns\TextColumn::make('duration')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('payment_status')
          ->searchable(),
        Tables\Columns\TextColumn::make('payment_date')
          ->dateTime()
          ->sortable(),
        Tables\Columns\TextColumn::make('payment_amount')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('tourLeader.name')
          ->numeric()
          ->sortable(),
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
      'index' => Pages\ListOrderFleets::route('/'),
      'create' => Pages\CreateOrderFleet::route('/create'),
      'view' => Pages\ViewOrderFleet::route('/{record}'),
      'edit' => Pages\EditOrderFleet::route('/{record}/edit'),
    ];
  }
}
