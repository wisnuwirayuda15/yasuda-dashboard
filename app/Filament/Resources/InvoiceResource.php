<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Order;
use App\Models\Invoice;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Enums\FleetCategory;
use App\Enums\InvoiceStatus;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Tabs;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use App\Infolists\Components\InvoiceTemplate;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\InvoiceResource\Pages;
use Awcodes\TableRepeater\Components\TableRepeater;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InvoiceResource\RelationManagers;

class InvoiceResource extends Resource
{
  protected static ?string $model = Invoice::class;

  protected static ?string $navigationIcon = 'fas-file-invoice';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        self::getGeneralInfoSection(),
        Group::make([
          self::getTabsSection(),
          self::getPaymentDetailSection(),
          RichEditor::make('notes')
            ->label('Special Notes'),
        ])
          ->columnSpanFull()
          ->visible(fn(Get $get) => $get('order_id')),
      ]);
  }

  public static function getTabsSection(): Tabs
  {
    return Tabs::make()
      ->columnSpanFull()
      ->contained(false)
      ->tabs([
        Tab::make('Biaya Utama')
          ->icon('fas-money-bill-wave')
          ->schema([
            self::getMainCostsSection(),
          ]),
        // Tab::make('Detail Armada')
        //   ->icon(FleetCategory::BIG->getIcon())
        //   ->schema([
        //     self::getFleetDetailSection(),
        //   ]),
        Tab::make('Tambahan Kaos')
          ->icon('fas-shirt')
          ->schema([
            self::getShirtsSection(),
          ]),
        Tab::make('Charge Kursi')
          ->icon('phosphor-seat-fill')
          ->schema([
            self::getSeatChargeSection(),
          ]),
      ]);
  }

  public static function getGeneralInfoSection(): Section
  {
    return Section::make('General information')
      ->schema([
        TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->helperText('Code is generated automatically.')
          ->unique(Invoice::class, 'code', ignoreRecord: true)
          ->default(get_code(new Invoice)),
        Select::make('order_id')
          ->required()
          ->live(true)
          ->allowHtml()
          ->searchable()
          ->preload()
          ->optionsLimit(5)
          ->editOptionModalHeading('Edit Order')
          ->createOptionModalHeading('Create Order')
          ->prefixIcon(fn() => OrderResource::getNavigationIcon())
          //! ga bisa edit kalo relationshipnya pake query tambahan
          ->relationship('order', 'id', )
          // ->relationship('order', 'id', fn(Builder $query) => $query->has('orderFleets')->doesntHave('invoice'), ignoreRecord: true)
          // ->default(fn(Order $order) => $order->inRandomOrder()->has('orderFleets')->doesntHave('invoice')->value('id'))
          ->editOptionForm(fn(Form $form) => OrderResource::form($form))
          ->createOptionForm(fn(Form $form) => OrderResource::form($form))
          //! kalo pake view, ga bisa di search customer namenya
          ->getOptionLabelFromRecordUsing(fn(Order $record) => view('livewire.order-badge', compact('record'))),
        Group::make()
          ->visible(fn(Get $get) => filled($get('order_id')))
          ->schema([
            Placeholder::make('lembaga')
              ->inlineLabel()
              ->label('Lembaga :')
              ->extraAttributes(['class' => 'font-bold'])
              ->content(fn(Get $get) => Order::find($get('order_id'))->customer->name ?? '-'),
            Placeholder::make('tanggal')
              ->label('Tanggal :')
              ->inlineLabel()
              ->extraAttributes(['class' => 'font-bold'])
              ->content(function (Get $get) {
                $date = Order::find($get('order_id'))?->trip_date;
                if (filled($date))
                  return $date->translatedFormat('j F Y');
                return '-';
              }),
            Placeholder::make('tujuan')
              ->label('Tujuan :')
              ->inlineLabel()
              ->extraAttributes(['class' => 'font-bold'])
              ->content(function (Get $get) {
                $order = Order::find($get('order_id'));
                if (filled($order)) {
                  $destinations = Destination::find($order->destinations);
                  return "{$order->regency->name} ({$destinations->implode('name', ' + ')})";
                }
                return '-';
              }),
            self::getFleetDetailSection()
          ])
      ]);
  }

  public static function getFleetDetailSection(): Fieldset
  {
    return Fieldset::make('Jumlah Armada')
      ->schema([
        Hidden::make('total_seat'),
        Placeholder::make('total_seat')
          ->inlineLabel()
          ->label('Total')
          ->content(
            function (Get $get, Set $set) {
              $order = optional(Order::find($get('order_id')));
              if (filled($order->orderFleets)) {
                [$total_seat, $medium_seat, $big_seat, $legrest_seat] = [0, 0, 0, 0];
                foreach ($order->orderFleets as $orderFleet) {
                  $fleet = $orderFleet->fleet;
                  $total_seat = $fleet->seat_set->value + $total_seat;
                  match ($fleet->category->value) {
                    FleetCategory::MEDIUM->value => $medium_seat++,
                    FleetCategory::BIG->value => $big_seat++,
                    FleetCategory::LEGREST->value => $legrest_seat++,
                  };
                }
                $set('total_medium_bus', $medium_seat);
                $set('total_big_bus', $big_seat);
                $set('total_legrest_bus', $legrest_seat);
                $set('total_seat', $total_seat);
                return $total_seat;
              }
              return '-';
            }
          ),

        Hidden::make('total_medium_bus'),
        Placeholder::make('total_medium_bus')
          ->inlineLabel()
          ->label('Medium')
          ->hintIcon(FleetCategory::MEDIUM->getIcon())
          ->content(fn(Get $get) => $get('total_medium_bus') ?? '-'),

        Hidden::make('total_big_bus'),
        Placeholder::make('total_big_bus')
          ->inlineLabel()
          ->label('Big')
          ->hintIcon(FleetCategory::BIG->getIcon())
          ->content(fn(Get $get) => $get('total_big_bus') ?? '-'),

        Hidden::make('total_legrest_bus'),
        Placeholder::make('total_legrest_bus')
          ->inlineLabel()
          ->label('Legrest')
          ->hintIcon(FleetCategory::LEGREST->getIcon())
          ->content(fn(Get $get) => $get('total_legrest_bus') ?? '-'),
      ]);
  }

  public static function getMainCostsSection(): Section
  {
    return Section::make('Detail Biaya Utama')
      ->description('Biaya utama perjalanan.')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        TableRepeater::make('main_costs')
          ->required()
          ->stackAt(MaxWidth::ExtraLarge)
          ->streamlined()
          ->hiddenLabel()
          ->deletable(false)
          ->addable(false)
          ->reorderable(false)
          ->columnSpanFull()
          ->default(self::getDefaultMainCostItems())
          ->headers([
            Header::make('Keterangan')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Jumlah')
              ->align(Alignment::Center)
              ->width('80px'),
            Header::make('Harga (Gross)')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Cashback')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Total Transaksi (Gross)')
              ->align(Alignment::Center)
              ->width('150px'),
            Header::make('Total Cashback')
              ->align(Alignment::Center)
              ->width('150px'),
            Header::make('Total Transaksi')
              ->align(Alignment::Center)
              ->width('150px'),
          ])
          ->schema([
            Hidden::make('slug'),
            TextInput::make('name')
              ->required()
              ->readOnly()
              ->distinct()
              ->columnSpanFull(),
            TextInput::make('qty')
              ->required()
              ->integer()
              ->minValue(0)
              ->default(0)
              ->preventUnwantedNumberValue('qty'),
            TextInput::make('price')
              ->required()
              ->numeric()
              ->prefix('Rp')
              ->minValue(0)
              ->default(0)
              ->preventUnwantedNumberValue('price'),
            TextInput::make('cashback')
              ->required()
              ->numeric()
              ->prefix('Rp')
              ->minValue(0)
              ->default(0)
              ->preventUnwantedNumberValue('cashback'),
            Placeholder::make('total_gross_transaction')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-sky-500'])
              ->content(fn(Get $get) => idr((int) $get('qty') * (int) $get('price'))),
            Placeholder::make('total_cashback')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-red-500'])
              ->content(fn(Get $get) => idr((int) $get('qty') * (int) $get('cashback'))),
            Placeholder::make('total_net_transaction')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-green-500'])
              ->content(fn(Get $get) => idr((int) $get('qty') * (int) $get('price') - (int) $get('qty') * (int) $get('cashback'))),
          ]),

        Fieldset::make('Total')
          ->schema([
            Hidden::make('total_qty'),
            Placeholder::make('total_qty')
              ->content(function (Get $get, Set $set) {
                $qty = array_sum(array_map(fn($cost) => (int) $cost['qty'], $get('main_costs'))) ?: 0;
                $set('total_qty', $qty);
                return $qty;
              }),

            Hidden::make('total_gross_transactions'),
            Placeholder::make('total_gross_transactions')
              ->extraAttributes(['class' => 'text-sky-500'])
              ->content(function (Get $get, Set $set) {
                $total = array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['price'], $get('main_costs'))) ?: 0;
                $set('total_gross_transactions', $total);
                return idr($total);
              }),

            Hidden::make('total_cashbacks'),
            Placeholder::make('total_cashbacks')
              ->extraAttributes(['class' => 'text-red-500'])
              ->content(function (Get $get, Set $set) {
                $total = array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['cashback'], $get('main_costs'))) ?: 0;
                $set('total_cashbacks', $total);
                return idr($total);
              }),

            Hidden::make('total_net_transactions'),
            Placeholder::make('total_net_transactions')
              ->extraAttributes(['class' => 'text-green-500'])
              ->content(function (Get $get, Set $set) {
                $total = array_sum(array_map(fn($cost) => ((int) $cost['qty'] * (int) $cost['price']) - ((int) $cost['qty'] * (int) $cost['cashback']), $get('main_costs'))) ?: 0;
                $set('total_net_transactions', $total);
                return idr($total);
              }),
          ]),
      ]);
  }

  public static function getShirtsSection(): Section
  {
    return Section::make('Tambahan Kaos')
      ->description('Detail tambahan dan biaya kaos.')
      ->columnSpanFull()
      ->columns(2)
      ->schema([
        TextInput::make('kaos_diserahkan')
          ->required()
          ->label('Total Kaos Diserahkan')
          ->integer()
          ->minValue(0)
          ->default(0)
          ->preventUnwantedNumberValue(),
        Hidden::make('kaos_tercover_paket'),
        Placeholder::make('kaos_tercover_paket')
          ->label('Kaos Tercover Paket')
          ->helperText('Program + Ibu & Anak Pangku')
          ->content(
            function (Get $get, Set $set) {
              $program = self::getCostItem($get, 'main_costs', 'program')['qty'];
              $anak = self::getCostItem($get, 'main_costs', 'ibu-anak-pangku')['qty'];
              $paket = (int) $program + (int) $anak;
              $set('kaos_tercover_paket', $paket);
              $kaos = $get('kaos_diserahkan') - $get('kaos_tercover_paket');
              $set('qty_kaos_anak', $kaos);
              return $paket;
            }
          ),
        self::getShirtFields('kaos_anak', 'Selisih Kaos Anak', 25000, 'Total Kaos Diserahkan - Kaos Tercover Paket'),
        self::getShirtFields('kaos_guru', 'Tambahan 1-Stel Guru', 120000),
        self::getShirtFields('kaos_dewasa', 'Tambahan Kaos Dewasa', 80000),
        Hidden::make('total_tambahan_kaos'),
        Placeholder::make('total_tambahan_kaos')
          ->label('Total')
          ->extraAttributes(['class' => 'text-green-500 text-xl font-bolder'])
          ->content(function (Get $get, Set $set): string {
            $total = (int) $get('total_kaos_anak') + (int) $get('total_kaos_guru') + (int) $get('total_kaos_dewasa');
            $set('total_tambahan_kaos', $total);
            return idr($total);
          }),
      ]);

  }

  public static function getShirtFields(string $name, string $label, int|float $price = 0, string $helperText = null): Fieldset
  {
    return Fieldset::make($label)
      ->schema([
        TextInput::make("qty_$name")
          ->required()
          ->label('Jumlah')
          ->integer()
          ->minValue(0)
          ->default(0)
          ->disabled(fn() => $name == 'kaos_anak')
          ->dehydrated()
          ->helperText($helperText)
          ->preventUnwantedNumberValue(),
        TextInput::make("price_$name")
          ->required()
          ->label('Harga')
          ->numeric()
          ->minValue(0)
          ->default($price)
          ->dehydrated()
          ->prefix('Rp')
          ->preventUnwantedNumberValue(),
        Hidden::make("total_$name"),
        Placeholder::make("total_$name")
          ->label('Total Biaya')
          ->extraAttributes(['class' => 'text-green-500'])
          ->content(function (Get $get, Set $set) use ($name) {
            if (!str_contains($get('slug'), 'total')) {
              $total = (int) $get("qty_$name") * (int) $get("price_$name");
              $set("total_$name", $total);
              return idr($total);
            }
          }),
      ]);
  }

  public static function getSeatChargeSection(): Section
  {
    return Section::make('Charge Kursi')
      ->description('Detail biaya kursi kosong.')
      ->columns(2)
      ->schema([
        Placeholder::make('seat_capacity')
          ->inlineLabel()
          ->content(fn(Get $get) => $get('total_seat') ?? '-'),
        Placeholder::make('actual_seat_filled')
          ->inlineLabel()
          ->content(fn(Get $get) => $get('total_qty') ?? '-'),
        TextInput::make('adjusted_seat')
          ->integer()
          ->inlineLabel()
          ->default(0)
          ->extraAttributes(['class' => 'w-max'])
          ->preventUnwantedNumberValue(),
        Hidden::make('empty_seat'),
        Placeholder::make('empty_seat')
          ->inlineLabel()
          ->content(
            function (Get $get, Set $set): View {
              $emptySeat = (int) $get('total_seat') - (int) $get('total_qty') - (int) $get('adjusted_seat');
              $set('empty_seat', $emptySeat);
              $color = $emptySeat < 0 ? 'danger' : ($emptySeat == 0 ? 'warning' : 'success');
              return view('livewire.filament-badge', ['text' => $emptySeat, 'color' => $color]);
            }
          ),
        Hidden::make('seat_charge'),
        Placeholder::make('seat_charge')
          ->inlineLabel()
          ->helperText('50% x Kursi kosong x (Beli Kursi - Cashback)')
          ->content(
            function (Get $get, Set $set) {
              $seat = self::getCostItem($get, 'main_costs', 'beli-kursi');
              $kursi = $seat['price'] - $seat['cashback'];
              $charge = 0.5 * $get('empty_seat') * $kursi;
              $set('seat_charge', $charge);
              if ($get('empty_seat') < 0) {
                Notification::make()
                  ->title('Jumlah kursi tidak mencukupi')
                  ->body('Silahkan pilih armada lain atau kurangi jumlah pelanggan.')
                  ->danger()
                  ->send();
              }
              return idr($charge);
            }
          ),
      ]);
  }

  public static function getPaymentDetailSection(): Section
  {
    return Section::make('Detail Pembayaran')
      ->description('Detail total biaya pembayaran.')
      ->schema([
        Placeholder::make('total_gross_transactions')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bolder'])
          ->content(fn(Get $get) => idr($get('total_gross_transactions'))),
        Placeholder::make('total_cashbacks')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bolder'])
          ->content(fn(Get $get) => idr($get('total_cashbacks'))),
        Placeholder::make('total_net_transactions')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bolder'])
          ->content(fn(Get $get) => idr($get('total_net_transactions'))),
        Placeholder::make('seat_charge')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bolder'])
          ->content(fn(Get $get) => idr($get('seat_charge'))),
        Placeholder::make('total_tambahan_kaos')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bolder'])
          ->content(fn(Get $get) => idr($get('total_tambahan_kaos'))),
        TextInput::make('other_cost')
          ->required()
          ->numeric()
          ->inlineLabel()
          ->prefix('Rp')
          ->minValue(0)
          ->default(0)
          ->extraAttributes(['class' => 'w-max'])
          ->preventUnwantedNumberValue(),
        Placeholder::make('total_transactions')
          ->inlineLabel()
          ->label('Total Tagihan')
          ->extraAttributes(['class' => 'font-bolder'])
          ->content(function (Get $get, Set $set) {
            $total = $get('total_net_transactions') + $get('seat_charge') + $get('total_tambahan_kaos') + $get('other_cost');
            $set('total_transactions', $total);
            return idr($total);
          }),
        self::getDownPaymentReapeter(),
        Hidden::make('kekurangan'),
        Placeholder::make('kekurangan')
          ->inlineLabel()
          ->label('Kekurangan/Kelebihan')
          ->extraAttributes(['class' => '!font-bolder !text-2xl !text-red-500'])
          ->content(function (Get $get, Set $set): View {
            $dp = array_sum(array_map(fn($dp) => (int) $dp['amount'], $get('down_payments'))) ?: 0;
            $total = $get('total_transactions') - $dp;
            $set('kekurangan', $total);
            $set('status', match (true) {
              $total === 0 => InvoiceStatus::PAID_OFF->value,
              $total > 0 => InvoiceStatus::UNDER_PAYMENT->value,
              default => InvoiceStatus::OVER_PAYMENT->value,
            });
            $color = $total > 0 ? 'danger' : 'success';
            return view('livewire.filament-badge', ['text' => idr($total), 'color' => $color, 'big' => true]);
          }),

        ToggleButtons::make('status')
          ->required()
          ->grouped()
          ->inline()
          ->disabled()
          ->dehydrated()
          ->options(InvoiceStatus::class)
      ]);
  }

  public static function getDownPaymentReapeter(): Repeater
  {
    return Repeater::make('down_payments')
      ->addActionLabel('Tambah DP')
      ->live(true)
      ->columns(2)
      ->defaultItems(0)
      ->reorderable(false)
      ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
      ->schema([
        Hidden::make('order'),
        Hidden::make('name')
          ->live(true)
          ->default(function (Get $get, Set $set) {
            $count = count($get('../../down_payments'));
            $set('order', $count);
            return "DP-$count";
          }),
        TextInput::make('amount')
          ->required()
          ->numeric()
          ->prefix('Rp')
          ->minValue(0)
          ->default(0)
          ->preventUnwantedNumberValue(),
        DatePicker::make('date')
          ->required()
          ->default(today())
          ->native(false)
          ->closeOnDateSelection()
          ->prefixIcon('heroicon-s-calendar-days')
          ->displayFormat('d mm Y'),
      ])
      ->deleteAction(
        fn(Action $action) => $action
          ->disabled(function (array $arguments, Repeater $component): bool {
            $item = $component->getItemState($arguments['item']);
            if ($item['order'] != count($component->getState())) {
              return true;
            } else {
              return false;
            }
          })
      );
  }

  public static function getDefaultMainCostItems(): array
  {
    return [
      1 => [
        "slug" => "program",
        "name" => "Program",
        "qty" => 0,
        "price" => 0,
        "cashback" => 0,
      ],
      2 => [
        "slug" => "ibu-anak-pangku",
        "name" => "Ibu & Anak Pangku",
        "qty" => 0,
        "price" => 400000,
        "cashback" => 30000,
      ],
      3 => [
        "slug" => "beli-kursi",
        "name" => "Beli Kursi",
        "qty" => 0,
        "price" => 200000,
        "cashback" => 15000,
      ],
      4 => [
        "slug" => "tambahan-orang",
        "name" => "Tambahan Orang",
        "qty" => 0,
        "price" => 315000,
        "cashback" => 20000,
      ],
      5 => [
        "slug" => "pembina",
        "name" => "Pembina",
        "qty" => 0,
        "price" => 100000,
        "cashback" => 0,
      ],
      6 => [
        "slug" => "special-rate",
        "name" => "Special Rate",
        "qty" => 0,
        "price" => 0,
        "cashback" => 0,
      ],
    ];
  }

  public static function getCostItem(Get $get, string $name, string $slug): array
  {
    return collect($get($name))->firstWhere('slug', $slug);
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
        Tables\Columns\TextColumn::make('order.trip_date')
          ->date()
          ->sortable(),
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

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        InvoiceTemplate::make('invoice')
          ->hiddenLabel()
          ->columnSpanFull(),
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
      'index' => Pages\ListInvoices::route('/'),
      'create' => Pages\CreateInvoice::route('/create'),
      'view' => Pages\ViewInvoice::route('/{record}'),
      'edit' => Pages\EditInvoice::route('/{record}/edit'),
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
