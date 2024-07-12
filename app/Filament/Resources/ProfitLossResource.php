<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use Filament\Forms\Form;
use App\Models\ProfitLoss;
use App\Models\TourReport;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Enums\FleetCategory;
use App\Enums\DestinationType;
use Illuminate\Support\Number;
use App\Enums\ProfitLossStatus;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Forms\Components\Placeholder;
use App\Filament\Exports\ProfitLossExporter;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\ProfitLossResource\Pages;
use EightyNine\Approvals\Tables\Actions\RejectAction;
use EightyNine\Approvals\Tables\Actions\SubmitAction;
use EightyNine\Approvals\Tables\Actions\ApproveAction;
use EightyNine\Approvals\Tables\Actions\DiscardAction;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use App\Filament\Resources\ProfitLossResource\RelationManagers;

class ProfitLossResource extends Resource
{
  protected static ?string $model = ProfitLoss::class;

  protected static ?string $navigationIcon = 'gmdi-attach-money-o';

  protected static ?Invoice $invoice = null;

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::FINANCE->getLabel();
  }

  public static function form(Form $form): Form
  {
    $record = $form->getRecord();

    if (blank($record)) {
      $invoice = request('invoice');

      if (blank($invoice) && Route::current()->getName() === 'livewire.update') {
        $parameters = getUrlQueryParameters(url()->previous());
        $invoice = $parameters['invoice'];
      }

      static::$invoice = Invoice::where('code', $invoice)->with(['order', 'order.orderFleets.fleet', 'order.customer'])->first();
    } else {
      static::$invoice = $record->invoice;
    }

    return $form
      ->schema([
        static::getGeneralInfoSection(),
        static::getCostTabs(),
        static::getOtherIncomeSection(),
        static::getTotalCostsSection(),
        Checkbox::make('submission')->submission(),
        Checkbox::make('confirmation')->confirmation()
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('invoice.code')
          ->searchable()
          ->badge()
          ->numeric(),
        TextColumn::make('invoice.order.code')
          ->badge()
          ->color('secondary')
          ->searchable(),
        TextColumn::make('invoice.order.customer.name')
          ->searchable(),
        TextColumn::make('net_sales')
          ->label('Net Sales')
          ->money('IDR')
          ->state(fn(ProfitLoss $record): float => $record->calculateNetSales()),
        TextColumn::make('adjusted_income')
          ->label('Income (Plan)')
          ->sortable()
          ->money('IDR'),
        TextColumn::make('actual_income')
          ->label('Income (Actual)')
          ->money('IDR')
          ->placeholder('No tour report')
          ->state(fn(ProfitLoss $record): ?float => $record->calculateIncome()),
        TextColumn::make('invoice.order.trip_date')
          ->label('Tanggal')
          ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        ApprovalStatusColumn::make('approvalStatus.status'),
      ])
      ->filters([
        Filter::make('approved')->approved(),
        Filter::make('notApproved')->notApproved(),
      ])
      ->headerActions([
        ExportAction::make()
          ->hidden(fn(): bool => static::getModel()::count() === 0)
          // ->visible(fn(): bool => Route::current()->getName() === static::getRouteBaseName() . '.index')
          ->exporter(ProfitLossExporter::class)
          ->label('Export')
          ->color('success')
      ])
      ->actions([
        SubmitAction::make()->color('info'),
        ApproveAction::make()->color('success'),
        DiscardAction::make()->color('warning'),
        RejectAction::make()->color('danger'),
        ActionGroup::make([
          ViewAction::make(),
          EditAction::make(),
          DeleteAction::make()
            ->action(function (ProfitLoss $record, DeleteAction $action) {
              $tourReport = TourReport::withoutGlobalScopes()->where('invoice_id', $record->invoice_id)->exists();
              if ($tourReport) {
                Notification::make()
                  ->danger()
                  ->title('Delete failed')
                  ->body("Invoice <strong>{$record->invoice->code}</strong> has Tour Report")
                  ->send();
                $action->cancel();
              }
              $record->delete();
            }),
        ])->visible(fn(Model $record) => $record->isApprovalCompleted()),
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

  public static function getGeneralInfoSection(): Section
  {
    return Section::make('General information')
      ->schema([
        Hidden::make('invoice_id')
          ->required()
          ->unique(ignoreRecord: true)
          ->default(fn() => static::$invoice->id),
        Placeholder::make('invoice_code')
          ->label('Invoice :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(fn() => static::$invoice->code),
        Placeholder::make('order_code')
          ->label('Order :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(fn() => static::$invoice->order->code),
        Placeholder::make('customer_name')
          ->label('Customer :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(fn() => static::$invoice->order->customer->name),
        Placeholder::make('trip_date')
          ->label('Tanggal :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(fn() => static::$invoice->order->trip_date->translatedFormat('d F Y')),
        Placeholder::make('destinations')
          ->label('Tujuan :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(function () {
            $inv = static::$invoice;
            $destinations = Destination::find($inv->order->destinations);
            return "{$inv->order->regency->name} ({$destinations->implode('name', ' + ')})";
          }),
        static::getFleetDetailSectionGroup(),
      ]);
  }

  public static function getFleetDetailSectionGroup(): Group
  {
    return Group::make([
      Fieldset::make('Jumlah Armada')
        ->schema([
          Placeholder::make('total_seat')
            ->inlineLabel()
            ->label('Total Seat')
            ->content(
              function (Set $set, Get $get, Placeholder $component) {
                $order = static::$invoice->order;
                $totalBus = $order->orderFleets()->count();
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
                  $set('snack_qty', $totalSeat + $totalBus * 3);
                  return $totalSeat;
                }
                return '-';
              }
            ),
          Placeholder::make('medium_bus_total')
            ->inlineLabel()
            ->label('Medium Bus')
            ->hintIcon(FleetCategory::MEDIUM->getIcon())
            ->content(fn(Get $get) => $get('medium_bus_total') ?? '-'),
          Placeholder::make('big_bus_total')
            ->inlineLabel()
            ->label('Big Bus')
            ->hintIcon(FleetCategory::BIG->getIcon())
            ->content(fn(Get $get) => $get('big_bus_total') ?? '-'),
          Placeholder::make('legrest_bus_total')
            ->inlineLabel()
            ->label('Legrest Bus')
            ->hintIcon(FleetCategory::LEGREST->getIcon())
            ->content(fn(Get $get) => $get('legrest_bus_total') ?? '-'),
        ]),
      Fieldset::make('Biaya Bonus Langganan')
        ->columns(3)
        ->schema([
          TextInput::make('medium_subs_bonus')
            ->required()
            ->disabled(fn(Get $get) => !$get('medium_bus_total'))
            ->dehydrated()
            ->label('Medium Bus')
            ->default(150000)
            ->currency(),
          TextInput::make('big_subs_bonus')
            ->required()
            ->disabled(fn(Get $get) => !$get('big_bus_total'))
            ->dehydrated()
            ->label('Big Bus')
            ->default(250000)
            ->currency(),
          TextInput::make('legrest_subs_bonus')
            ->required()
            ->disabled(fn(Get $get) => !$get('legrest_bus_total'))
            ->dehydrated()
            ->label('Legrest Bus')
            ->default(300000)
            ->currency(),
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
            static::getOperationalCostsSection(),
          ]),
        Tab::make('Special Cost')
          ->icon('fas-star')
          ->schema([
            static::getSpecialCostsSection(),
          ]),
        Tab::make('Variable Cost')
          ->icon('fas-question')
          ->schema([
            static::getVariableCostsSection(),
          ]),
        Tab::make('Other Cost')
          ->icon('tabler-dots')
          ->schema([
            static::getOtherCostsSection(),
          ]),
      ]);
  }

  public static function getOperationalCostsSection(): Section
  {
    return Section::make('Fix Cost - Operasional')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        static::getCostFields('medium_rent', 'Sewa Bus - Medium', 3000000, icon: FleetCategory::MEDIUM->getIcon()),
        static::getCostFields('big_rent', 'Sewa Bus - Big', 4000000, icon: FleetCategory::BIG->getIcon()),
        static::getCostFields('legrest_rent', 'Sewa Bus - Legrest', 5000000, icon: FleetCategory::LEGREST->getIcon()),
        static::getCostFields('toll', 'Biaya Toll', 450000, description: 'Sesuai jumlah bus'),
        static::getCostFields('banner', 'Banner Bus', 10000, description: 'Sesuai jumlah bus'),
        static::getCostFields('crew', 'Fee Crew Bus', 340000, description: 'Sesuai jumlah bus'),
        static::getCostFields('tour_leader', 'Fee Tour Leader', 300000, description: 'Sesuai jumlah bus'),
        static::getCostFields('documentation', 'Fee Dokumentasi', 300000, disableQty: false),

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
    $inv = static::$invoice;
    $teacher = collect($inv->main_costs)->firstWhere('slug', 'pembina')['qty'];
    $teacherShirtPrice = $inv->teacher_shirt_price;
    $additionalTeacherShirtQty = $inv->teacher_shirt_qty;

    return Section::make('Fix Cost - Special Threatment')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        static::getCostFields('teacher_shirt', 'Kaos Pembina', 120000, $teacher, disableQty: false),
        static::getCostFields('additional_teacher_shirt', 'Tambahan Kaos Pembina', $teacherShirtPrice, $additionalTeacherShirtQty, disablePrice: true),
        static::getCostFields('principle_fee', 'Fee Kepsek', qty: 1, disablePrice: true, description: 'Sesuai jumlah dan harga sewa bus'),
        static::getCostFields('souvenir', 'Oleh-Oleh Pembina', 15000, $teacher),
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
    $inv = static::$invoice;
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
    $makanAnak = $anak + $program;

    return Section::make('Variable Cost')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        static::getCostFields('child_shirt', 'Kaos Anak', 20000, $childShirt, description: 'Total kaos diserahkan'),
        static::getCostFields('adult_shirt', 'Kaos Dewasa', 70000, $adultShirt),
        static::getCostFields('photo', 'Dokumentasi Foto', 3000, $totalMakanFoto, description: 'Program + Anak + Tambahan + Special'),
        Section::make('Biaya Makan')
          ->aside()
          ->schema([
            static::getCostFields('snack', 'Snack & Mineral', 10000, description: 'Total kursi + (Jumlah bus x 3)', aside: false),
            static::getCostFields('eat', 'Paket Box', 25000, $makan, description: 'Program + Anak + Tambahan + Pembina + Special', aside: false),
            static::getCostFields('eat_child', 'Porsi Anak', 0, $makanAnak, description: 'Program + Anak', aside: false),
            static::getCostFields('eat_prasmanan', 'Prasmanan', 0, $makan, description: 'Program + Anak + Tambahan + Pembina + Special', aside: false),
          ]),
        static::getDestinationsCostsRepeaterSection(),
        Placeholder::make('variable_cost_total')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'child_shirt',
              'adult_shirt',
              'photo',
              'snack',
              'eat',
              'eat_child',
              'eat_prasmanan',
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
        static::getCostFields('backup', 'Cadangan', 250000, description: 'Sesuai jumlah bus'),
        static::getCostFields('subs_bonus', 'Bonus Langganan', 250000, 1, disablePrice: true, description: 'Sesuai jumlah dan harga sewa bus'),
        static::getCostFields('emergency_cost', 'Biaya Darurat', 0, 1),
        Placeholder::make('other_cost_total')
          ->inlineLabel()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_reduce([
              'backup',
              'subs_bonus',
              'emergency_cost',
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
    $inv = static::$invoice;
    $mainCosts = $inv->main_costs;
    $totalQty = array_sum(array_map(fn($cost) => $cost['qty'], $mainCosts)) ?: 0;
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
          ->inlineLabel()
          ->label('Other Income')
          ->default(0)
          ->extraAttributes(['class' => 'w-max'])
          ->currency(),
        Placeholder::make('others_income_total')
          ->inlineLabel()
          ->content(function (Set $set, Get $get, Placeholder $component) {
            $total = $get('seat_charge') + $get('others_income') + $get('additional_child_shirt');
            $set($component, $total);
            return view('filament.components.badges.default', ['text' => idr($total), 'big' => true, 'color' => 'success']);
          }),
      ]);
  }

  public static function getTotalCostsSection(): Section
  {
    $inv = static::$invoice;
    $mainCosts = $inv->main_costs;
    $totalPrices = array_sum(array_map(fn($cost) => $cost['qty'] * $cost['price'], $mainCosts)) ?: 0;
    $totalCashbacks = array_sum(array_map(fn($cost) => $cost['qty'] * $cost['cashback'], $mainCosts)) ?: 0;
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
        Placeholder::make('adjusted_income')
          ->inlineLabel()
          ->dehydrated()
          ->label('Adjusted Income (Plan)')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $adjustedIncome = $get('net_income') + $get('others_income_total');
            $netSales = $get('net_sales');
            $status = $netSales ? $adjustedIncome / $netSales : 0;
            $rate = Number::percentage($status * 100, 2);
            $statusClass = $status > 0.15 ? ProfitLossStatus::GOOD->value : ProfitLossStatus::BAD->value;
            $color = $status > 0.15 ? 'success' : 'danger';
            $set('adjusted_income_status', $statusClass);
            $set($component, $adjustedIncome);
            return view('filament.components.badges.default', ['text' => idr($adjustedIncome) . " ($rate)", 'color' => $color, 'big' => true]);
          }),
        ToggleButtons::make('adjusted_income_status')
          ->label('Status (Plan)')
          ->grouped()
          ->inlineLabel()
          ->disabled()
          ->helperText('Dikategorikan good profit jika income > 15%')
          ->options(ProfitLossStatus::class),

        Group::make([
          Placeholder::make('actual_income')
            ->inlineLabel()
            ->label('Actual Income')
            ->content(function (Get $get, Set $set, Placeholder $component) {
              $inv = static::$invoice;
              $actualIncome = $get('adjusted_income') + $inv->tourReport?->difference;
              $netSales = $get('net_sales');
              $status = $netSales ? $actualIncome / $netSales : 0;
              $rate = Number::percentage($status * 100, 2);
              $statusClass = $status > 0.15 ? ProfitLossStatus::GOOD->value : ProfitLossStatus::BAD->value;
              $color = $status > 0.15 ? 'success' : 'danger';
              $set('actual_income_status', $statusClass);
              $set($component, $actualIncome);
              return view('filament.components.badges.default', ['text' => idr($actualIncome) . " ($rate)", 'color' => $color, 'big' => true]);
            }),
          ToggleButtons::make('actual_income_status')
            ->label('Status')
            ->grouped()
            ->inlineLabel()
            ->disabled()
            ->helperText('Dikategorikan good profit jika income > 15%')
            ->options(ProfitLossStatus::class)
        ])->visible(static::$invoice->tourReport()->exists())
      ])
    ;
  }

  public static function getDestinationsCostsRepeaterSection(): Section
  {
    return Section::make('Destinasi Wisata')
      ->aside()
      ->description(static::$invoice->order->trip_date->isWeekday() ? 'Harga Weekday' : 'Harga Weekend')
      ->schema([
        Repeater::make('destinations_cost')
          ->hiddenLabel()
          ->columns(3)
          ->deletable(false)
          ->addable(false)
          ->reorderable(false)
          ->afterStateHydrated(fn(Set $set, Repeater $component) => $set($component, static::getDestinationsCostItems()))
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
              ->helperText(static::$invoice->order->trip_date->isWeekday() ? 'Harga Weekday' : 'Harga Weekend')
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
            $dc = array_sum(array_map(fn($dc) => $dc['total'], $get('destinations_cost'))) ?: 0;
            $set($component, $dc);
            return idr($dc);
          }),
      ]);
  }

  public static function getDestinationsCostItems(): array
  {
    $inv = static::$invoice;

    $id = $inv->order->destinations;

    $destinations = Destination::findOrFail($id);

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
        "type" => $des->type->getDescription(),
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
    bool|Closure $disablePrice = false,
    bool|Closure $aside = true,
  ): Section {
    return Section::make($label)
      ->description($description)
      ->icon($icon)
      ->columns(5)
      ->aside($aside)
      ->schema([
        TextInput::make("{$name}_qty")
          ->required()
          ->label('Jumlah')
          ->default($qty)
          ->hidden($disableQty)
          ->qty(),
        Placeholder::make("{$name}_qty")
          ->label('Jumlah')
          ->visible($disableQty)
          ->content(function (Get $get, Set $set) use ($name, $qty) {
            $get("{$name}_qty") ?? $set("{$name}_qty", $qty);
            return $get("{$name}_qty");
          }),
        TextInput::make("{$name}_price")
          ->required()
          ->columnSpan(3)
          ->label('Harga')
          ->default($price)
          ->hidden($disablePrice)
          ->disabled(fn(Get $get) => $get("{$name}_qty") < 1)
          ->dehydrated()
          ->currency(),
        Placeholder::make("{$name}_price")
          ->label('Harga')
          ->columnSpan(3)
          ->visible($disablePrice)
          ->content(function (Get $get, Set $set) use ($name, $price) {
            $get("{$name}_price") ?? $set("{$name}_price", $price);
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
