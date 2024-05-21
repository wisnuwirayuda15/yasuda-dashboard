<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Fleet;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Enums\OrderStatus;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\FleetPaymentStatus;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Filament\Resources\OrderResource;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderFleetResource\Pages;
use App\Filament\Resources\OrderFleetResource\RelationManagers;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class OrderFleetResource extends Resource
{
  protected static ?string $model = OrderFleet::class;

  protected static ?string $navigationIcon = 'mdi-bus-marker';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->code(get_code(new OrderFleet, 'OF')),
        Forms\Components\Select::make('order_id')
          ->allowHtml()
          ->relationship('order', 'id')
          ->prefixIcon(fn() => OrderResource::getNavigationIcon())
          ->editOptionForm(fn(Form $form) => OrderResource::form($form))
          ->createOptionForm(fn(Form $form) => OrderResource::form($form))
          ->editOptionModalHeading('Edit Order')
          ->createOptionModalHeading('Create Order')
          ->getOptionLabelFromRecordUsing(fn(Order $record) => view('filament.components.badges.order', compact('record'))),
        Forms\Components\Select::make('fleet_id')
          ->required()
          ->optionsLimit(false)
          // ->relationship('fleet', 'name')
          ->allowHtml()
          ->options(Fleet::getGroupOptionsByCategories())
          ->getOptionLabelFromRecordUsing(fn(Fleet $record) => view('filament.components.badges.fleet-options', compact('record'))),
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
        Tables\Columns\TextColumn::make('order.customer.name')
          ->sortable()
          ->placeholder('No customer'),
        // SelectColumn::make('order_id')
        //   ->label('Customer')
        //   ->extraAttributes(['class' => 'w-52'])
        //   ->placeholder(
        //     function (OrderFleet $record) {
        //       $orders = Order::whereDate('trip_date', $record->trip_date)->get();
        //       return blank($orders) ? 'No order' : 'Select order';
        //     }
        //   )
        //   ->disabled(
        //     function (OrderFleet $record) {
        //       $orders = Order::whereDate('trip_date', $record->trip_date)->get();
        //       return blank($orders) ? true : false;
        //     }
        //   )
        //   ->options(function (OrderFleet $record) {
        //     $orders = Order::whereDate('trip_date', $record->trip_date)->get();
        //     $orderArray = [];
        //     foreach ($orders as $order) {
        //       $orderArray[$order->id] = "{$order->code} • {$order->customer->name}";
        //     }
        //     return $orderArray;
        //   }),
        Tables\Columns\TextColumn::make('trip_date')
          ->date()
          ->sortable()
          ->formatStateUsing(fn($state): string => $state->format('d/m/Y')),
        Tables\Columns\TextColumn::make('remaining_day')
          ->alignCenter()
          ->badge()
          ->state(
            function (OrderFleet $record): string {
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
        Tables\Columns\TextColumn::make('fleet.name')
          ->label('Mitra Armada'),
        Tables\Columns\TextColumn::make('fleet.category')
          ->label('Jenis')
          ->badge()
          ->formatStateUsing(fn($state): string => $state->getLabel()),
        Tables\Columns\TextColumn::make('fleet.seat_set')
          ->label('Seat Set')
          ->formatStateUsing(fn($state): string => $state->getLabel()),
        Tables\Columns\TextColumn::make('trip_day')
          ->state(fn(OrderFleet $record): string => $record->trip_date->translatedFormat('l')),
        Tables\Columns\TextColumn::make('trip_month')
          ->state(fn(OrderFleet $record): string => $record->trip_date->translatedFormat('F')),
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
        DateRangeFilter::make('trip_date')
          ->label('Trip Date'),
      ])
      ->actions([
        ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
          Action::make('order_id')
            ->icon(OrderResource::getNavigationIcon())
            ->label('Select Order')
            ->color('info')
            ->form([
              Select::make('order_id')
                ->hiddenLabel()
                ->prefixIcon(fn() => OrderResource::getNavigationIcon())
                ->options(function (OrderFleet $record) {
                  $orders = Order::whereDate('trip_date', $record->trip_date)->get();
                  $orderArray = [];
                  foreach ($orders as $order) {
                    $orderArray[$order->id] = "{$order->code} • {$order->customer->name}";
                  }
                  return $orderArray;
                })
                ->required(),
            ])
            ->action(function (array $data, OrderFleet $record): void {
              $record->update(['order_id' => $data['order_id']]);
            }),
          Action::make('delete_order_id')
            ->requiresConfirmation()
            ->icon('heroicon-s-trash')
            ->label('Remove Order')
            ->color('warning')
            ->hidden(fn(OrderFleet $record): bool => blank($record->order_id))
            ->action(fn(OrderFleet $record) => $record->update(['order_id' => null])),
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
      'index' => Pages\ListOrderFleets::route('/'),
      'create' => Pages\CreateOrderFleet::route('/create'),
      'view' => Pages\ViewOrderFleet::route('/{record}'),
      'edit' => Pages\EditOrderFleet::route('/{record}/edit'),
    ];
  }
}
