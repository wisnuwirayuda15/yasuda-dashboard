<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Enums\OrderStatus;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\FleetPaymentStatus;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\OrderResource;
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
          ->hiddenOn('edit')
          ->helperText('Code are generated automatically.')
          ->unique(OrderFleet::class, 'code', ignoreRecord: true)
          ->default(get_code(new OrderFleet, 'OF-')),
        Forms\Components\Select::make('order_id')
          ->relationship('order', 'id')
          ->native(false)
          ->prefixIcon(fn() => OrderResource::getNavigationIcon())
          ->editOptionForm(fn(Form $form) => OrderResource::form($form))
          ->createOptionForm(fn(Form $form) => OrderResource::form($form))
          ->editOptionModalHeading('Edit Order')
          ->createOptionModalHeading('Create Order')
          ->allowHtml()
          ->getOptionLabelFromRecordUsing(
            function (Order $record) {
              return Blade::render(
                '
                  <span class="flex items-center">
                    <x-filament::badge class="mr-2">{{ $record->code }}</x-filament::badge>
                    <span>{{ $record->customer->name }}</span>
                  </span>
                ',
                ['record' => $record]
              );
            }
          ),
        Forms\Components\Select::make('fleet_id')
          ->required()
          ->native(false)
          ->relationship('fleet', 'name')
          ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->name} - {$record->seat_set}"),
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
              ->afterStateUpdated(function (Set $set) {
                $set('payment_date', null);
                $set('payment_amount', null);
              }),
            Forms\Components\DateTimePicker::make('payment_date')
              ->live()
              ->native(false)
              ->closeOnDateSelection()
              ->required(fn(Get $get) => $get('payment_status') != FleetPaymentStatus::NON_DP->value)
              ->hidden(fn(Get $get) => $get('payment_status') == FleetPaymentStatus::NON_DP->value)
              ->prefixIcon('iconsax-bol-money-time')
              ->displayFormat('d mm Y'),
            Forms\Components\TextInput::make('payment_amount')
              ->live()
              ->numeric()
              ->prefix('Rp')
              ->minValue(1)
              ->required(fn(Get $get) => $get('payment_status') != FleetPaymentStatus::NON_DP->value)
              ->hidden(fn(Get $get) => $get('payment_status') == FleetPaymentStatus::NON_DP->value)
              ->afterStateUpdated(fn(?int $state, Set $set) => $state <= 1 && $set('payment_amount', 1)),
          ]),
        Forms\Components\Select::make('tour_leader_id')
          ->relationship('tourLeader', 'name')
          ->native(false)
          ->searchable()
          ->preload()
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
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('fleet.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('trip_date')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('duration')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('payment_status')
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

  public function renderBadgeOptionLabel(string $label, string $color = 'primary', string $size = 'sm'): string
  {
    $label = e($label);
    return Blade::render("<x-filament::badge color='$color' size='$size'>$label</x-filament::badge>");
  }
}
