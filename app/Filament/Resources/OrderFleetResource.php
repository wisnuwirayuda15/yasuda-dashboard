<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Fleet;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Enums\OrderStatus;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\FleetPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderFleetResource\Pages;
use App\Filament\Resources\OrderFleetResource\RelationManagers;

class OrderFleetResource extends Resource
{
  protected static ?string $model = OrderFleet::class;

  protected static ?string $navigationIcon = 'mdi-bus-marker';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->live()
          ->helperText('Code is generated automatically.')
          ->unique(OrderFleet::class, 'code', ignoreRecord: true)
          ->default(get_code(new OrderFleet, 'OF')),
        Forms\Components\Select::make('order_id')
          ->allowHtml()
          ->native(false)
          ->relationship('order', 'id')
          ->prefixIcon(fn() => OrderResource::getNavigationIcon())
          ->editOptionForm(fn(Form $form) => OrderResource::form($form))
          ->createOptionForm(fn(Form $form) => OrderResource::form($form))
          ->editOptionModalHeading('Edit Order')
          ->createOptionModalHeading('Create Order')
          ->getOptionLabelFromRecordUsing(fn(Order $record) => view('livewire.order-badge', compact('record'))),
        Forms\Components\Select::make('fleet_id')
          ->required()
          ->native(false)
          ->relationship('fleet', 'name')
          ->allowHtml()
          ->getOptionLabelFromRecordUsing(fn(Fleet $record) => view('livewire.fleet-options-badge', compact('record'))),
        Forms\Components\DatePicker::make('trip_date')
          ->required()
          ->native(false)
          ->closeOnDateSelection()
          ->minDate(today())
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
        Forms\Components\ToggleButtons::make('status')
          ->required()
          ->inline()
          ->disabledOn('create')
          ->dehydrated()
          ->options(OrderStatus::class)
          ->default(OrderStatus::READY->value),
        Forms\Components\Section::make('Pembayaran Armada')
          ->schema([
            Forms\Components\ToggleButtons::make('payment_status')
              ->required()
              ->live()
              ->inline()
              ->options(FleetPaymentStatus::class)
              ->default(FleetPaymentStatus::NON_DP->value)
              ->afterStateUpdated(function (Get $get, Set $set) {
                if ($get('payment_status') == FleetPaymentStatus::NON_DP->value) {
                  $set('payment_date', null);
                  $set('payment_amount', null);
                }
              }),
            Forms\Components\Group::make()
              ->visible(fn(Get $get) => $get('payment_status') != FleetPaymentStatus::NON_DP->value)
              ->columnSpan(2)
              ->schema([
                Forms\Components\DatePicker::make('payment_date')
                  ->required()
                  ->native(false)
                  ->minDate(today())
                  ->closeOnDateSelection()
                  ->prefixIcon('iconsax-bol-money-time')
                  ->displayFormat('d mm Y'),
                Forms\Components\TextInput::make('payment_amount')
                  ->required()
                  ->numeric()
                  ->live(true)
                  ->prefix('Rp')
                  ->minValue(1)
                  ->afterStateUpdated(fn(?int $state, Set $set) => $state <= 1 && $set('payment_amount', 1)),
              ])
          ]),
        Forms\Components\Select::make('tour_leader_id')
          ->searchable()
          ->preload()
          ->optionsLimit(5)
          ->relationship('tourLeader', 'name')
          ->prefixIcon(fn() => TourLeaderResource::getNavigationIcon()),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('code')
          ->searchable(),
        Tables\Columns\TextColumn::make('trip_date')
          ->date()
          ->sortable()
          ->formatStateUsing(fn($state): string => $state->translatedFormat('d F Y')),
        Tables\Columns\TextColumn::make('order.customer.name')
          ->numeric()
          ->sortable()
          ->placeholder('No customer'),
        Tables\Columns\TextColumn::make('trip_day')
          ->state(fn(Model $record): string => $record->trip_date->translatedFormat('l')),
        Tables\Columns\TextColumn::make('trip_month')
          ->state(fn(Model $record): string => $record->trip_date->translatedFormat('F')),
        Tables\Columns\TextColumn::make('remaining_days')
          ->badge()
          ->sortable()
          ->state(
            function (Model $record): string {
              $date = $record->trip_date;
              if ($date->isToday()) {
                return OrderStatus::ON_TRIP->getLabel();
              } elseif ($date->isPast()) {
                return OrderStatus::FINISHED->getLabel();
              }
              return today()->diffInDays($date);
            }
          )
          ->color(fn(string $state): string => $state == OrderStatus::ON_TRIP->getLabel() ? 'warning' : ($state <= 7 ? 'danger' : ($state <= 30 ? 'warning' : 'success'))),
        Tables\Columns\TextColumn::make('duration')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('payment_status')
          ->badge()
          ->searchable(),
        Tables\Columns\TextColumn::make('payment_date')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('payment_amount')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('tourLeader.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('status')
          ->badge()
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
