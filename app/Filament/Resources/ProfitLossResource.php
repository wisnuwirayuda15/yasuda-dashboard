<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\ProfitLoss;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Enums\FleetCategory;
use App\Enums\DestinationType;
use App\Enums\ProfitLossStatus;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProfitLossResource\Pages;
use App\Filament\Resources\ProfitLossResource\RelationManagers;

class ProfitLossResource extends Resource
{
  protected static ?string $model = ProfitLoss::class;

  protected static ?string $navigationIcon = 'gmdi-attach-money-o';

  protected static bool $shouldRegisterNavigation = false;

  protected static ?Invoice $invoice = null;

  public static function form(Form $form): Form
  {
    $invoice = request('invoice');

    if (blank($invoice) && Route::current()->getName() == 'livewire.update') {
      $parameters = getUrlQueryParameters(url()->previous());
      $invoice = $parameters['invoice'];
    }

    self::$invoice = Invoice::where('code', $invoice)->with(['order.orderFleets.fleet', 'order.customer'])->firstOrFail();

    return $form
      ->schema([
        self::getGeneralInfoSection(),
        self::getCostTabs(),
        self::getOtherIncomeSection(),
        self::getTotalCostsSection(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('invoice.code')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('status')
          ->searchable(),
        Tables\Columns\TextColumn::make('total_cost')
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
      'index' => Pages\ListProfitLosses::route('/'),
      'create' => Pages\CreateProfitLoss::route('/create'),
      'view' => Pages\ViewProfitLoss::route('/{record}'),
      'edit' => Pages\EditProfitLoss::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }

  public static function getGeneralInfoSection(): Section
  {
    return Section::make('General information')
      ->schema([
        Select::make('invoice_id')
          ->required()
          ->disabled()
          ->dehydrated()
          ->prefixIcon(InvoiceResource::getNavigationIcon())
          ->default(self::$invoice->id)
          ->allowHtml()
          ->relationship('invoice', 'id')
          ->getOptionLabelFromRecordUsing(fn(Invoice $record) => view('filament.components.badges.invoice', compact('record'))),
        Placeholder::make('invoice_code')
          ->label('Invoice :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(self::$invoice->code),
        Placeholder::make('customer_name')
          ->label('Customer :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(fn() => self::$invoice->order->customer->name),
        Placeholder::make('trip_date')
          ->label('Tanggal :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(fn() => self::$invoice->order->trip_date->translatedFormat('d F Y')),
        Placeholder::make('destinations')
          ->label('Tujuan :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(function () {
            $inv = self::$invoice;
            $destinations = Destination::find($inv->order->destinations);
            return "{$inv->order->regency->name} ({$destinations->implode('name', ' + ')})";
          }),
        self::getFleetDetailSectionGroup(),
      ]);
  }

  public static function getFleetDetailSectionGroup(): Group
  {
    return Group::make([
      Fieldset::make('Jumlah Armada')
        ->schema([
          Placeholder::make('total_seat')
            ->inlineLabel()
            ->label('Total')
            ->content(
              function (Set $set, Get $get, Placeholder $component) {
                $order = self::$invoice->order;
                $totalBus = count($order->orderFleets);
                if (filled($order->orderFleets)) {
                  [$totalSeat, $mediumTotal, $bigTotal, $legrestTotal] = [0, 0, 0, 0];
                  foreach ($order->orderFleets as $orderFleet) {
                    $fleet = $orderFleet->fleet;
                    $totalSeat = $fleet->seat_set->value + $totalSeat;
                    match ($fleet->category->value) {
                      FleetCategory::MEDIUM->value => $mediumTotal++,
                      FleetCategory::BIG->value => $bigTotal++,
                      FleetCategory::LEGREST->value => $legrestTotal++,
                    };
                  }
                  $set('medium_bus_total', $mediumTotal);
                  $set('big_bus_total', $bigTotal);
                  $set('legrest_bus_total', $legrestTotal);
                  $set('medium_rent_qty', $mediumTotal);
                  $set('big_rent_qty', $bigTotal);
                  $set('legrest_rent_qty', $legrestTotal);
                  $set('toll_qty', $totalBus);
                  $set('banner_qty', $totalBus);
                  $set('crew_qty', $totalBus);
                  $set('tour_leader_qty', $totalBus);
                  $set('backup_qty', $totalBus);
                  $set($component, $totalSeat);
                  $bonus = $mediumTotal * $get('medium_subs_bonus') + $bigTotal * $get('big_subs_bonus') + $legrestTotal * $get('legrest_subs_bonus');
                  $set('principle_fee_price', $bonus);
                  $set('subs_bonus_price', $bonus);
                  $set('snack_qty', $totalSeat + $bigTotal * 3);
                  return $totalSeat;
                }
                return '-';
              }
            ),
          Placeholder::make('medium_bus_total')
            ->inlineLabel()
            ->label('Medium')
            ->hintIcon(FleetCategory::MEDIUM->getIcon())
            ->content(fn(Get $get) => $get('medium_bus_total') ?? '-'),
          Placeholder::make('big_bus_total')
            ->inlineLabel()
            ->label('Big')
            ->hintIcon(FleetCategory::BIG->getIcon())
            ->content(fn(Get $get) => $get('big_bus_total') ?? '-'),
          Placeholder::make('legrest_bus_total')
            ->inlineLabel()
            ->label('Legrest')
            ->hintIcon(FleetCategory::LEGREST->getIcon())
            ->content(fn(Get $get) => $get('legrest_bus_total') ?? '-'),
        ]),
      Fieldset::make('Biaya Bonus Bus')
        ->columns(3)
        ->schema([
          TextInput::make('medium_subs_bonus')
            ->required()
            ->numeric()
            ->disabled(fn(Get $get) => !$get('medium_bus_total'))
            ->label('Medium Bus')
            ->prefix('Rp')
            ->minValue(0)
            ->default(150000)
            ->preventUnwantedNumberValue(),
          TextInput::make('big_subs_bonus')
            ->required()
            ->numeric()
            ->disabled(fn(Get $get) => !$get('big_bus_total'))
            ->label('Big Bus')
            ->prefix('Rp')
            ->minValue(0)
            ->default(250000)
            ->preventUnwantedNumberValue(),
          TextInput::make('legrest_subs_bonus')
            ->required()
            ->numeric()
            ->disabled(fn(Get $get) => !$get('legrest_bus_total'))
            ->label('Legrest Bus')
            ->prefix('Rp')
            ->minValue(0)
            ->default(0)
            ->preventUnwantedNumberValue(),
        ]),
    ]);
  }

  public static function getCostTabs(): Tabs
  {
    return Tabs::make()
      ->columnSpanFull()
      ->contained(false)
      ->tabs([
        Tab::make('Operational Cost')
          ->icon('fas-gear')
          ->schema([
            self::getOperationalCostsSection(),
          ]),
        Tab::make('Special Cost')
          ->icon('fas-star')
          ->schema([
            self::getSpecialCostsSection(),
          ]),
        Tab::make('Variable Cost')
          ->icon('fas-question')
          ->schema([
            self::getVariableCostsSection(),
          ]),
        Tab::make('Other Cost')
          ->icon('tabler-dots')
          ->schema([
            self::getOtherCostsSection(),
          ]),
      ]);
  }

  public static function getOperationalCostsSection(): Section
  {
    return Section::make('Fix Cost - Operasional')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        self::getCostFields('medium_rent', 'Sewa Bus - Medium', 3000000, icon: FleetCategory::MEDIUM->getIcon()),
        self::getCostFields('big_rent', 'Sewa Bus - Big', 4000000, icon: FleetCategory::BIG->getIcon()),
        self::getCostFields('legrest_rent', 'Sewa Bus - Legrest', 5000000, icon: FleetCategory::LEGREST->getIcon()),
        self::getCostFields('toll', 'Biaya Toll', 450000, description: 'Sesuai jumlah bus'),
        self::getCostFields('banner', 'Banner Bus', 10000, description: 'Sesuai jumlah bus'),
        self::getCostFields('crew', 'Fee Crew Bus', 340000, description: 'Sesuai jumlah bus'),
        self::getCostFields('tour_leader', 'Fee Tour Leader', 300000, description: 'Sesuai jumlah bus'),
        self::getCostFields('documentation', 'Fee Dokumentasi', 0, disableQty: false),

        Placeholder::make('operational_cost_total')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'medium_rent',
              'big_rent',
              'legrest_rent',
              'toll',
              'banner',
              'crew',
              'tour_leader',
              'documentation',
            ], function ($carry, $key) use ($get) {
              return $carry + $get("{$key}_total");
            }, 0);
            $set($component, $total);
            return view('filament.components.badges.default', ['text' => idr($total), 'big' => true, 'color' => 'danger']);
          }),
      ]);
  }

  public static function getSpecialCostsSection(): Section
  {
    $inv = self::$invoice;
    $teacher = collect($inv->main_costs)->firstWhere('slug', 'pembina')['qty'];
    $teacherShirtPrice = $inv->teacher_shirt_price;
    $additionalTeacherShirtQty = $inv->teacher_shirt_qty;
    // dd($teacher);

    return Section::make('Fix Cost - Special Threatment')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        self::getCostFields('teacher_shirt', 'Kaos Pembina', 120000, $teacher),
        self::getCostFields('additional_teacher_shirt', 'Tambahan Kaos Pembina', $teacherShirtPrice, $additionalTeacherShirtQty, disablePrice: true),
        self::getCostFields('principle_fee', 'Fee Kepsek', qty: 1, disablePrice: true, description: 'Sesuai jumlah dan harga sewa bus'),
        self::getCostFields('souvenir', 'Oleh-Oleh Pembina', 15000, $teacher),
        Placeholder::make('special_cost_total')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'teacher_shirt',
              'additional_teacher_shirt',
              'principle_fee',
              'souvenir',
            ], function ($carry, $key) use ($get) {
              return $carry + $get("{$key}_total");
            }, 0);
            $set($component, $total);
            return view('filament.components.badges.default', ['text' => idr($total), 'big' => true, 'color' => 'danger']);
          }),
      ]);
  }

  public static function getVariableCostsSection(): Section
  {
    $inv = self::$invoice;
    $childShirt = $inv->submitted_shirt;
    $adultShirt = $inv->adult_shirt_qty;

    $getMainCostQty = fn(string $slug): int => collect($inv->main_costs)->firstWhere('slug', $slug)['qty'] ?? 0;
    $program = $getMainCostQty('program');
    $anak = $getMainCostQty('ibu-anak-pangku');
    $tambahan = $getMainCostQty('tambahan-orang');
    $special = $getMainCostQty('special-rate');
    $pembina = $getMainCostQty('pembina');

    $totalMakanFoto = $program + $anak + $tambahan + $special;
    $makan = $totalMakanFoto + $pembina;

    return Section::make('Variable Cost')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        self::getCostFields('child_shirt', 'Kaos Anak', 20000, $childShirt, description: 'Total kaos diserahkan'),
        self::getCostFields('adult_shirt', 'Kaos Dewasa', 70000, $adultShirt),
        self::getCostFields('photo', 'Dokumentasi Foto', 3000, $totalMakanFoto, description: 'Program + Anak + Tambahan + Special'),
        self::getCostFields('snack', 'Snack & Mineral', 10000, description: 'Total kursi + (Jumlah big bus x 3)'),
        self::getCostFields('eat', 'Makan 1x', 25000, $makan, description: 'Program + Anak + Tambahan + Pembina + Special'),
        self::getDestinationsCostsRepeaterSection(),
        Placeholder::make('variable_cost_total')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'child_shirt',
              'adult_shirt',
              'photo',
              'snack',
              'eat',
              'destinations_cost',
            ], function ($carry, $key) use ($get) {
              return $carry + $get("{$key}_total");
            }, 0);
            $set($component, $total);
            return view('filament.components.badges.default', ['text' => idr($total), 'big' => true, 'color' => 'danger']);
          }),
      ]);
  }

  public static function getOtherCostsSection(): Section
  {
    return Section::make('Other Cost')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        self::getCostFields('backup', 'Cadangan', 250000, description: 'Sesuai jumlah bus'),
        self::getCostFields('subs_bonus', 'Bonus Langganan', 250000, 1, disablePrice: true, description: 'Sesuai jumlah dan harga sewa bus'),
        Placeholder::make('other_cost_total')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'backup',
              'subs_bonus',
            ], function ($carry, $key) use ($get) {
              return $carry + $get("{$key}_total");
            }, 0);
            $set($component, $total);
            return view('filament.components.badges.default', ['text' => idr($total), 'big' => true, 'color' => 'danger']);
          }),
      ]);
  }

  public static function getOtherIncomeSection(): Section
  {
    $inv = self::$invoice;
    $mainCosts = $inv->main_costs;
    $totalQty = array_sum(array_map(fn($cost) => (int) $cost['qty'], $mainCosts)) ?: 0;
    $adjustedSeat = $inv->adjusted_seat;
    $beliKursi = collect($mainCosts)->firstWhere('slug', 'beli-kursi');
    $priceBeliKursi = $beliKursi['price'] - $beliKursi['cashback'];
    $getMainCostQty = fn(string $slug): int => collect($mainCosts)->firstWhere('slug', $slug)['qty'] ?? 0;
    $program = $getMainCostQty('program');
    $anak = $getMainCostQty('ibu-anak-pangku');
    $kaosPaket = $program + $anak;
    $kaosDiserahkan = $inv->submitted_shirt;
    $qtyKaosAnak = $kaosDiserahkan - $kaosPaket;
    $qtyKaosGuru = $inv->teacher_shirt_qty;
    $qtyKaosDewasa = $inv->adult_shirt_qty;
    $priceKaosAnak = $inv->child_shirt_price;
    $priceKaosGuru = $inv->teacher_shirt_price;
    $priceKaosDewasa = $inv->adult_shirt_price;
    $totalPriceKaosAnak = $qtyKaosAnak * $priceKaosAnak;
    $totalPriceKaosGuru = $qtyKaosGuru * $priceKaosGuru;
    $totalPriceKaosDewasa = $qtyKaosDewasa * $priceKaosDewasa;
    $totalPriceKaos = $totalPriceKaosAnak + $totalPriceKaosGuru + $totalPriceKaosDewasa;

    return Section::make('Other Income')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        Placeholder::make('seat_charge')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) use ($totalQty, $adjustedSeat, $priceBeliKursi) {
            $emptySeat = $get('total_seat') - $totalQty - $adjustedSeat;
            $seatCharge = 0.5 * $emptySeat * $priceBeliKursi;
            $set($component, $seatCharge);
            return idr($seatCharge);
          }),
        Placeholder::make('additional_child_shirt')
          ->label('Tambahan Kaos')
          ->inlineLabel()
          ->content(function (Set $set, Placeholder $component) use ($totalPriceKaos) {
            $set($component, $totalPriceKaos);
            return idr($totalPriceKaos);
          }),
        TextInput::make('others_income')
          ->required()
          ->numeric()
          ->inlineLabel()
          ->label('Other Income')
          ->prefix('Rp')
          ->minValue(0)
          ->default(0)
          ->preventUnwantedNumberValue(),

        Placeholder::make('others_income_total')
          ->label('Total')
          ->inlineLabel()
          ->content(function (Set $set, Get $get, Placeholder $component) {
            $total = $get('seat_charge') + $get('others_income') + $get('additional_child_shirt');
            $set($component, $total);
            return idr($total);
          }),
      ]);
  }

  public static function getTotalCostsSection(): Section
  {
    $inv = self::$invoice;
    $mainCosts = $inv->main_costs;
    $totalPrices = array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['price'], $mainCosts)) ?: 0;
    $totalCashbacks = array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['cashback'], $mainCosts)) ?: 0;
    $totalNetTransactions = $totalPrices - $totalCashbacks;

    return Section::make('Cost Information')
      ->schema([
        Placeholder::make('net_sales')
          ->inlineLabel()
          ->label('Net Sales')
          ->content(function (Set $set, Placeholder $component) use ($totalNetTransactions) {
            $set($component, $totalNetTransactions);
            return view('filament.components.badges.default', ['text' => idr($totalNetTransactions), 'color' => 'warning']);
          }),

        Placeholder::make('cost_total')
          ->inlineLabel()
          ->label('Total Cost')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'operational_cost',
              'fixed_cost',
              'special_cost',
              'variable_cost',
              'other_cost',
            ], function ($carry, $key) use ($get) {
              return $carry + $get("{$key}_total");
            }, 0);
            $set($component, $total);
            return view('filament.components.badges.default', ['text' => idr($total), 'color' => 'danger']);
          }),

        Placeholder::make('net_income')
          ->inlineLabel()
          ->label('Net Income')
          ->content(function (Get $get, Set $set, Placeholder $component) use ($totalNetTransactions) {
            $netIncome = $totalNetTransactions - $get('cost_total');
            $set($component, $netIncome);
            return view('filament.components.badges.default', ['text' => idr($netIncome), 'color' => 'info']);
          }),

        Group::make([
          Placeholder::make('adjusted_income')
            ->inlineLabel()
            ->label('Adjusted Income')
            ->content(function (Get $get, Set $set, Placeholder $component) {
              $adjustedIncome = $get('net_income') + $get('others_income_total');
              $status = $adjustedIncome / $get('net_sales');
              $rate = round($status * 100, 2) . '%';
              $statusClass = $status > 0.15 ? ProfitLossStatus::GOOD : ProfitLossStatus::BAD;
              $color = $status > 0.15 ? 'success' : 'danger';
              $set('adjusted_income_status', $statusClass->value);
              $set($component, $adjustedIncome);
              return view('filament.components.badges.default', ['text' => idr($adjustedIncome) . " ($rate)", 'color' => $color, 'big' => true]);
            }),
          Placeholder::make('adjusted_income_rate')
            ->hiddenLabel()
            ->content(fn($state) => $state),
        ]),

        ToggleButtons::make('adjusted_income_status')
          ->grouped()
          ->inlineLabel()
          ->disabled()
          ->options(ProfitLossStatus::class)
      ])
    ;
  }

  public static function getDestinationsCostsRepeaterSection(): Section
  {
    return Section::make('Destinasi Wisata')
      ->aside()
      ->description(self::$invoice->order->trip_date->isWeekday() ? 'Harga Weekday' : 'Harga Weekend')
      ->schema([
        Repeater::make('destinations_cost')
          ->hiddenLabel()
          ->columns(3)
          ->deletable(false)
          ->addable(false)
          ->reorderable(false)
          ->default(self::getDestinationsCostItems())
          ->itemLabel(fn(array $state) => $state['name'] ?? null)
          ->schema([
            Hidden::make('name'),
            Hidden::make('type'),
            Placeholder::make('qty')
              ->label('Jumlah')
              ->extraAttributes(['class' => 'text-green-500'])
              ->helperText(fn(Get $get) => $get('type'))
              ->content(fn(Get $get) => $get('qty')),
            Placeholder::make('price')
              ->label('Harga')
              ->extraAttributes(['class' => 'font-semibold'])
              ->helperText(self::$invoice->order->trip_date->isWeekday() ? 'Harga Weekday' : 'Harga Weekend')
              ->content(fn(Get $get) => idr($get('price'))),
            Placeholder::make('total')
              ->label('Total')
              ->extraAttributes(['class' => 'text-red-500 font-bold'])
              ->content(function (Get $get, Set $set, Placeholder $component) {
                $total = $get('price') * $get('qty');
                $set($component, $total);
                return idr($total);
              }),
          ])
          ->extraItemActions([
            Action::make('open_destination')
              ->tooltip('Lihat Destinasi')
              ->icon('heroicon-m-arrow-top-right-on-square')
              ->url(function (array $arguments, Repeater $component): ?string {
                $itemData = $component->getRawItemState($arguments['item']);
                return DestinationResource::getUrl('edit', ['record' => $itemData['id']]);
              }, true)
          ]),
        Placeholder::make('destinations_cost_total')
          ->hiddenLabel()
          ->extraAttributes(['class' => 'hidden'])
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $dc = array_sum(array_map(fn($dc) => (int) $dc['total'], $get('destinations_cost'))) ?: 0;
            $set($component, $dc);
            return idr($dc);
          }),
      ]);
  }

  public static function getDestinationsCostItems(): array
  {
    $inv = self::$invoice;

    $id = $inv->order->destinations;

    $destinations = Destination::find($id);

    $getMainCostQty = fn(string $slug): int => collect($inv->main_costs)->firstWhere('slug', $slug)['qty'] ?? 0;

    $anak = $getMainCostQty('ibu-anak-pangku') + $getMainCostQty('program');
    $tambahan = $getMainCostQty('tambahan-orang');
    $pembina = $getMainCostQty('pembina');

    foreach ($destinations as $des) {
      $price = $inv->order->trip_date->isWeekday() ? $des->weekday_price : ($des->weekend_price ?? 0);

      $qty = match ($des->type) {
        DestinationType::SISWA_ONLY => $anak,
        DestinationType::SISWA_DEWASA => $anak * 2 + $tambahan,
        DestinationType::SISWA_DEWASA_PEMBINA => $anak * 2 + $tambahan + $pembina,
        DestinationType::SISWA_TAMBAHAN => $anak + $tambahan,
        DestinationType::DEWASA => $anak,
      };

      $destinationArray[$des->id] = [
        "id" => $des->id,
        "name" => $des->type->value . ' - ' . $des->name,
        "type" => $des->type->getFormula(),
        "qty" => $qty,
        "price" => $price,
      ];
    }

    return $destinationArray;
  }

  public static function getCostFields(
    string|Closure $name,
    string|Closure $label,
    int|float|Closure $price = 0,
    int|Closure $qty = 0,
    string|Closure $description = null,
    string|Closure $icon = null,
    bool|Closure $disableQty = true,
    bool|Closure $disablePrice = false
  ): Section {
    return Section::make($label)
      ->description($description)
      ->icon($icon)
      ->columns(3)
      ->aside()
      ->schema([
        TextInput::make("{$name}_qty")
          ->required()
          ->integer()
          ->label('Jumlah')
          ->minValue(0)
          ->default($qty)
          ->hidden($disableQty)
          ->preventUnwantedNumberValue(),
        Placeholder::make("{$name}_qty")
          ->label('Jumlah')
          ->visible($disableQty)
          ->content(function (Get $get) use ($name) {
            return $get("{$name}_qty");
          }),
        TextInput::make("{$name}_price")
          ->required()
          ->numeric()
          ->label('Harga')
          ->prefix('Rp')
          ->minValue(0)
          ->default($price)
          ->hidden($disablePrice)
          ->preventUnwantedNumberValue(),
        Placeholder::make("{$name}_price")
          ->label('Harga')
          ->visible($disablePrice)
          ->content(function (Get $get) use ($name) {
            return idr($get("{$name}_price"));
          }),
        Placeholder::make("{$name}_total")
          ->label('Total')
          ->extraAttributes(['class' => 'text-red-500 font-semibold'])
          ->content(function (Get $get, Set $set, Placeholder $component) use ($name) {
            $total = $get("{$name}_qty") * $get("{$name}_price");
            $set($component, $total);
            return idr($total);
          })
      ]);
  }
}
