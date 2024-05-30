<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Fleet;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Enums\OrderStatus;
use App\Models\OrderFleet;
use App\Models\TourLeader;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\FleetPaymentStatus;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Filament\Resources\OrderResource;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\DeleteBulkAction;
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
        // Forms\Components\Select::make('order_id')
        //   ->allowHtml()
        //   ->live(true)
        //   ->prefixIcon(OrderResource::getNavigationIcon())
        //   ->editOptionForm(fn(Form $form) => OrderResource::form($form))
        //   ->createOptionForm(fn(Form $form) => OrderResource::form($form))
        //   ->editOptionModalHeading('Edit Order')
        //   ->createOptionModalHeading('Create Order')
        //   ->relationship('order', modifyQueryUsing: fn(Builder $query, Get $get): Builder => $query->whereDate('trip_date', $get('trip_date'))->with('customer'))
        //   ->getOptionLabelFromRecordUsing(fn(Order $record) => view('filament.components.badges.order', compact('record')))
        //   ->rules([
        //     fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
        //       $order = Order::findOrFail($value);
        //       if (!$order->trip_date->isSameDay($get('trip_date'))) {
        //         $fail('Order trip date must be the same as the order fleet trip date');
        //       }
        //     },
        //   ]),
        Forms\Components\Select::make('fleet_id')
          ->required()
          ->optionsLimit(false)
          ->allowHtml()
          ->options(Fleet::getGroupOptionsByCategories())
          ->getOptionLabelFromRecordUsing(fn(Fleet $record) => view('filament.components.badges.fleet-options', compact('record'))),
        Forms\Components\DatePicker::make('trip_date')
          ->required()
          ->live(true)
          ->default(today())
          ->minDate(today())
          ->afterStateUpdated(fn(Set $set) => $set('order_id', null)),
        Forms\Components\TextInput::make('duration')
          ->required()
          ->default(1)
          ->maxValue(100)
          ->suffix('Hari')
          ->prefixIcon('heroicon-s-clock')
          ->afterStateUpdated(fn(?int $state, Set $set) => $state <= 1 ? $set('duration', 1) : $state > 100 && $set('duration', 100))
          ->qty(minValue: 1),
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
                  ->minDate(today()),
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
          ->placeholder('No customer')
          ->tooltip(fn(OrderFleet $record): string => $record->order ? 'Change order' : 'Select order')
          ->action(self::getSelectOrderAction()),
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
          ViewAction::make(),
          EditAction::make(),
          DeleteAction::make(),
          ReplicateAction::make()
            ->excludeAttributes(['order_id', 'tour_leader_id'])
            ->before(function (OrderFleet $record) {
              $record->code = get_code(new OrderFleet, 'OF');
            }),
          self::getSelectOrderAction(),
          self::getSelectTourLeaderAction(),
          Action::make('delete_order_id')
            ->requiresConfirmation()
            ->icon('heroicon-s-trash')
            ->label('Remove Order')
            ->color('danger')
            ->hidden(fn(OrderFleet $record): bool => blank($record->order_id))
            ->action(
              function (OrderFleet $record) {
                $record->update(['order_id' => null]);
                Notification::make()
                  ->success()
                  ->title('Success')
                  ->body("Order removed from {$record->code}")
                  ->send();
              }
            ),
          Action::make('delete_tour_leader_id')
            ->requiresConfirmation()
            ->icon('heroicon-s-trash')
            ->label('Remove Tour Leader')
            ->color('danger')
            ->hidden(fn(OrderFleet $record): bool => blank($record->tour_leader_id))
            ->action(
              function (OrderFleet $record) {
                $record->update(['tour_leader_id' => null]);
                Notification::make()
                  ->success()
                  ->title('Success')
                  ->body("Tour leader removed from {$record->code}")
                  ->send();
              }
            ),
        ])
      ])
      ->bulkActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ])
    ;
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

  public static function getSelectOrderAction(): Action
  {
    return Action::make('order_id')
      ->icon(OrderResource::getNavigationIcon())
      ->label('Select Order')
      ->color('info')
      ->form([
        Forms\Components\Select::make('order_id')
          ->required()
          ->hiddenLabel()
          ->allowHtml()
          ->default(fn(OrderFleet $record) => $record->order_id)
          ->prefixIcon(OrderResource::getNavigationIcon())
          ->relationship('order', modifyQueryUsing: fn(Builder $query, OrderFleet $record): Builder => $query->whereDate('trip_date', $record->trip_date)->with('customer'))
          ->getOptionLabelFromRecordUsing(fn(Order $record) => view('filament.components.badges.order', compact('record')))
          ->rules([
            fn(OrderFleet $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
              $order = Order::findOrFail($value);
              if (!$order->trip_date->isSameDay($record->trip_date)) {
                $fail('Order trip date must be the same as the order fleet trip date');
              }
            },
          ]),
      ])
      ->action(function (array $data, OrderFleet $record): void {
        $record->update(['order_id' => $data['order_id']]);
        Notification::make()
          ->success()
          ->title('Success')
          ->body("Order added for {$record->code}")
          ->send();
      });
  }

  public static function getSelectTourLeaderAction(): Action
  {
    return Action::make('tour_leader_id')
      ->visible(fn(OrderFleet $record): bool => blank($record->tour_leader_id))
      ->icon(TourLeaderResource::getNavigationIcon())
      ->label('Select Tour Leader')
      ->color('success')
      ->form([
        Forms\Components\Select::make('tour_leader_id')
          ->required()
          ->hiddenLabel()
          ->allowHtml()
          ->default(fn(OrderFleet $record) => $record->tour_leader_id)
          ->prefixIcon(TourLeaderResource::getNavigationIcon())
          ->relationship(
            'tourLeader',
            'name',
            function (Builder $query, OrderFleet $record): Builder {
              return $query->whereDoesntHave('orderFleets', function (Builder $query) use ($record) {
                $query->where('trip_date', $record->trip_date);
              });
            },
          )
      ])
      ->action(function (array $data, OrderFleet $record): void {
        $record->update(['tour_leader_id' => $data['tour_leader_id']]);
        Notification::make()
          ->success()
          ->title('Success')
          ->body("Tour leader added for {$record->code}")
          ->send();
      });
  }
}
