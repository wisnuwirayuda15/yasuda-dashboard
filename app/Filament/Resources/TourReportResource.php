<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\TourReport;
use Filament\Tables\Table;
use App\Models\Destination;
use Illuminate\Support\Str;
use App\Enums\DestinationType;
use Filament\Resources\Resource;
use Awcodes\TableRepeater\Header;
use App\Enums\NavigationGroupLabel;
use App\Filament\Exports\TourReportExporter;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Awcodes\TableRepeater\Components\TableRepeater;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TourReportResource\Pages;
use App\Filament\Resources\TourReportResource\RelationManagers;

class TourReportResource extends Resource
{
  protected static ?string $model = TourReport::class;

  protected static ?string $navigationIcon = 'heroicon-s-document-check';

  protected static ?Invoice $invoice = null;

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

      static::$invoice = Invoice::where('code', $invoice)->with(['order', 'order.orderFleets.fleet', 'order.orderFleets.tourLeader', 'order.customer'])->firstOrFail();
    } else {
      static::$invoice = $record->invoice;
    }

    return $form
      ->schema([
        static::getGeneralInfoSection(),
        static::getMainCostsSection(),
        static::getOtherCostsSection(),
        static::getSummariesSection(),
        Checkbox::make('confirmation')->confirmation()
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('invoice.code')
          ->badge()
          ->searchable()
          ->sortable(),
        TextColumn::make('invoice.order.code')
          ->badge()
          ->color('secondary')
          ->searchable(),
        TextColumn::make('invoice.order.customer.name')
          ->searchable(),
        TextColumn::make('customer_repayment')
          ->label('Pembayaran Customer')
          ->money('IDR')
          ->sortable(),
        TextColumn::make('difference')
          ->label('Selisih')
          ->money('IDR')
          ->sortable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable(),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable(),
      ])
      ->headerActions([
        ExportAction::make()
          ->hidden(fn(): bool => static::getModel()::count() === 0)
          ->visible(fn(): bool => Route::current()->getName() === static::getRouteBaseName() . '.index')
          ->exporter(TourReportExporter::class)
          ->label('Export')
          ->color('success')
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->modalWidth(MaxWidth::MaxContent),
          Tables\Actions\EditAction::make()
            ->modalWidth(MaxWidth::MaxContent),
          Tables\Actions\DeleteAction::make()
            ->action(function (TourReport $record, $livewire) {
              $record->delete();
              $livewire->js('location.reload();');
            }),
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
      'index' => Pages\ListTourReports::route('/'),
      'create' => Pages\CreateTourReport::route('/create'),
      'view' => Pages\ViewTourReport::route('/{record}'),
      'edit' => Pages\EditTourReport::route('/{record}/edit'),
    ];
  }

  public static function getGeneralInfoSection(): Section
  {
    return Section::make('General Information')
      ->schema([
        Select::make('invoice_id')
          ->required()
          ->unique(ignoreRecord: true)
          ->disabled()
          ->dehydrated()
          ->allowHtml()
          ->prefixIcon(InvoiceResource::getNavigationIcon())
          ->default(static::$invoice->id)
          ->relationship('invoice', 'id')
          ->getOptionLabelFromRecordUsing(fn(Invoice $record) => view('filament.components.badges.invoice', compact('record'))),
        Placeholder::make('invoice_code')
          ->label('Invoice :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(static::$invoice->code),
        Placeholder::make('order_code')
          ->label('Order :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(static::$invoice->order->code),
        Placeholder::make('tour_leader_name')
          ->label('Tour Leader :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(function () {
            $orderFleets = static::$invoice->order->orderFleets;
            $tourLeaderNames = [];
            foreach ($orderFleets as $orderFleet) {
              $tourLeader = $orderFleet->tourLeader->name;
              if (!in_array($tourLeader, $tourLeaderNames)) {
                $tourLeaderNames[] = $tourLeader;
              }
            }
            return view('filament.components.lists.array', ['array' => $tourLeaderNames]);
          }),
        Placeholder::make('armada')
          ->label('Armada :')
          ->inlineLabel()
          ->extraAttributes(['class' => 'font-bold'])
          ->content(function () {
            $orderFleets = static::$invoice->order->orderFleets;
            foreach ($orderFleets as $orderFleet) {
              $fleet = $orderFleet->fleet->name . ' (' . $orderFleet->fleet->seat_set->value . ')';
              $fleetNames[] = $fleet;
            }
            return view('filament.components.lists.array', ['array' => $fleetNames]);
          }),
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
      ]);
  }

  public static function getMainCostsSection(): Section
  {
    return Section::make('Detail Biaya Utama')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        TableRepeater::make('main_costs')
          ->required()
          ->live()
          ->stackAt(MaxWidth::ExtraLarge)
          ->streamlined()
          ->hiddenLabel()
          ->default(static::getCostsDetailItems())
          ->deletable(false)
          ->addable(false)
          ->reorderable(false)
          ->columnSpanFull()
          ->headers([
            Header::make('Keterangan')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Qty (Plan)')
              ->align(Alignment::Center)
              ->width('80px'),
            Header::make('Harga (Plan)')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Total (Plan)')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Qty (Aktual)')
              ->align(Alignment::Center)
              ->width('80px'),
            Header::make('Harga (Aktual)')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Total (Aktual)')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Selisih (Plan - Act)')
              ->align(Alignment::Center)
              ->width('auto'),
          ])
          ->schema([
            Hidden::make('slug')
              ->distinct()
              ->required(),
            TextInput::make('name')
              ->required()
              ->disabled()
              ->dehydrated()
              ->extraAttributes(fn($state) => ['title' => $state])
              ->distinct(),
            TextInput::make('plan_qty')
              ->required()
              ->disabled()
              ->dehydrated()
              ->default(0)
              ->qty(),
            TextInput::make('plan_price')
              ->required()
              ->disabled()
              ->dehydrated()
              ->default(0)
              ->currency(),
            Placeholder::make('plan_total')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-green-500'])
              ->content(
                function (Get $get, Set $set, Placeholder $component) {
                  $total = $get('plan_qty') * $get('plan_price');
                  $set($component, $total);
                  return idr($total);
                }
              ),
            TextInput::make('act_qty')
              ->required()
              ->default(0)
              ->disabled(fn(Get $get) => $get('slug') === 'lain-lain')
              ->dehydrated()
              ->qty(),
            TextInput::make('act_price')
              ->required()
              ->default(0)
              ->hidden(fn(Get $get) => $get('slug') === 'lain-lain')
              ->currency(),
            Placeholder::make('act_price')
              ->hiddenLabel()
              ->visible(fn(Get $get) => $get('slug') === 'lain-lain')
              ->extraAttributes(['class' => 'text-slate-500'])
              ->dehydrated()
              ->content(
                function (Get $get, Set $set, Placeholder $component, $state) {
                  $price = $get('../../others_total') ?? $state;
                  $set($component, $price);
                  return idr($price);
                }
              ),
            Placeholder::make('act_total')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-yellow-500'])
              ->content(
                function (Get $get, Set $set, Placeholder $component) {
                  $total = $get('act_qty') * $get('act_price');
                  $set($component, $total);
                  return idr($total);
                }
              ),
            Placeholder::make('difference_total')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-red-500'])
              ->content(
                function (Get $get, Set $set, Placeholder $component) {
                  $total = $get('plan_total') - $get('act_total');
                  $set($component, $total);
                  return idr($total);
                }
              ),
          ]),
        Fieldset::make('Total')
          ->columns(3)
          ->schema(function () {
            foreach (['plan', 'act', 'difference'] as $key) {
              $textColor = $key === 'plan' ? 'text-green-500' : ($key === 'act' ? 'text-yellow-500' : 'text-red-500');
              $totalPlaceholders[] = Placeholder::make("{$key}_totals")
                ->extraAttributes(['class' => "$textColor font-semibold"])
                ->content(function (Get $get, Set $set, Placeholder $component) use ($key) {
                  $total = array_sum(array_map(fn($cost) => $cost["{$key}_total"], $get('main_costs'))) ?: 0;
                  $set($component, $total);
                  return idr($total);
                });
            }
            return $totalPlaceholders;
          })
      ]);
  }

  public static function getOtherCostsSection(): Section
  {
    return Section::make('Detail Biaya Lain-Lain (Cadangan)')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        TableRepeater::make('other_costs')
          ->live()
          ->addActionLabel('Tambah Biaya')
          ->stackAt(MaxWidth::ExtraLarge)
          ->default(null)
          ->streamlined()
          ->hiddenLabel()
          ->columnSpanFull()
          ->headers([
            Header::make('Keterangan')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Qty (Aktual)')
              ->align(Alignment::Center)
              ->width('80px'),
            Header::make('Harga (Aktual)')
              ->align(Alignment::Center)
              ->width('auto'),
            Header::make('Total (Aktual)')
              ->align(Alignment::Center)
              ->width('auto'),
          ])
          ->schema([
            TextInput::make('name')
              ->required()
              ->placeholder('Biaya X')
              ->distinct(),
            TextInput::make('other_qty')
              ->required()
              ->default(1)
              ->qty(),
            TextInput::make('other_price')
              ->required()
              ->default(0)
              ->currency(1),
            Placeholder::make('other_total')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-sky-500'])
              ->content(
                function (Get $get, Set $set, Placeholder $component) {
                  $total = $get('other_qty') * $get('other_price');
                  $set($component, $total);
                  return idr($total);
                }
              ),
          ]),
        Fieldset::make('Total')
          ->columns(3)
          ->schema([
            Placeholder::make('backup_price')
              ->label('Biaya Cadangan')
              ->helperText('Berdasarkan laporan Profit & Loss')
              ->extraAttributes(['class' => 'text-green-500 font-semibold'])
              ->content(function (Get $get, Set $set, Placeholder $component) {
                $inv = static::$invoice;
                $totalBus = $inv->order->orderFleets()->count();
                $backupPrice = $inv->profitLoss->backup_price;
                $total = $backupPrice * $totalBus;
                $set($component, $total);
                return idr($total);
              }),
            Placeholder::make('others_total')
              ->label('Total Biaya Lain-Lain')
              ->extraAttributes(['class' => 'text-red-500 font-semibold'])
              ->content(function (Get $get, Set $set, Placeholder $component) {
                $total = array_sum(array_map(fn($cost) => $cost['other_total'], $get('other_costs'))) ?: 0;
                $set($component, $total);
                return idr($total);
              }),
            Placeholder::make('others_difference')
              ->label('Sisa/Kekurangan')
              ->extraAttributes(['class' => 'text-yellow-500 font-semibold'])
              ->content(function (Get $get, Set $set, Placeholder $component) {
                $total = $get('backup_price') - $get('others_total');
                $set($component, $total);
                return idr($total);
              })
          ])
      ]);
  }

  public static function getSummariesSection(): Section
  {
    return Section::make('Summary')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        TextInput::make('customer_repayment')
          ->required()
          ->label('Pelunasan Customer')
          ->inlineLabel()
          ->default(0)
          ->extraAttributes(['class' => 'w-max'])
          ->currency(minValue: 0),
        Placeholder::make('income_total')
          ->dehydrated()
          ->inlineLabel()
          ->label('Total Pemasukan')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('plan_totals') + $get('backup_price');
            $set($component, $total);
            return view('filament.components.badges.default', [
              'text' => idr($total),
              'color' => 'success'
            ]);
          }),
        Placeholder::make('expense_total')
          ->dehydrated()
          ->inlineLabel()
          ->label('Total Pengeluaran')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('act_totals') + $get('others_total');
            $set($component, $total);
            return view('filament.components.badges.default', [
              'text' => idr($total),
              'color' => 'danger'
            ]);
          }),
        Placeholder::make('difference')
          ->dehydrated()
          ->inlineLabel()
          ->label('Jumlah Selisih')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('difference_totals') + $get('others_difference');
            $set($component, $total);
            return view('filament.components.badges.default', [
              'text' => idr($total),
              'color' => 'danger'
            ]);
          }),
        Placeholder::make('defisit_surplus')
          ->dehydrated()
          ->inlineLabel()
          ->label('Defisit/Surplus Tour')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('plan_totals') - $get('act_totals');
            $set($component, $total);
            return view('filament.components.badges.default', [
              'text' => idr($total),
              'color' => 'warning',
              'big' => true
            ]);
          }),
        Placeholder::make('refundable')
          ->dehydrated()
          ->inlineLabel()
          ->label('Cash yang harus dikembalikan/ditagihkan')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('customer_repayment') + $get('defisit_surplus');
            $set($component, $total);
            return view('filament.components.badges.default', [
              'text' => idr($total),
              'color' => 'info',
              'big' => true
            ]);
          }),
      ]);
  }

  public static function getCostsDetailItems(): array
  {
    $inv = static::$invoice;

    $destinationId = $inv->order->destinations;

    $destinations = Destination::findOrFail($destinationId);

    $getMainCostQty = fn(string $slug): int => collect($inv->main_costs)->firstWhere('slug', $slug)['qty'] ?? 0;

    $anak = $getMainCostQty('ibu-anak-pangku') + $getMainCostQty('program');
    $tambahan = $getMainCostQty('tambahan-orang');
    $pembina = $getMainCostQty('pembina');
    $special = $getMainCostQty('special-rate');

    foreach ($destinations as $des) {
      $price = $inv->order->trip_date->isWeekday() ? $des->weekday_price : ($des->weekend_price ?? 0);
      $name = 'HTM - ' . $des->name;

      $qty = match ($des->type) {
        DestinationType::SISWA_ONLY => $anak,
        DestinationType::SISWA_DEWASA => $anak * 2 + $tambahan,
        DestinationType::SISWA_DEWASA_PEMBINA => $anak * 2 + $tambahan + $pembina,
        DestinationType::SISWA_TAMBAHAN => $anak + $tambahan,
        DestinationType::DEWASA => $anak,
      };

      $costsDetail[$des->id] = [
        'slug' => Str::slug($name),
        'name' => $name,
        'plan_qty' => $qty,
        'plan_price' => $price,
        'plan_total' => 0,
        'act_qty' => $qty,
        'act_price' => $price,
        'act_total' => 0,
      ];
    }

    // $totalBus = $inv->order->orderFleets()->count();
    // $backupPrice = $inv->profitLoss->backup_price;

    if ($inv->profitLoss->eat_price > 0) {
      $qty = $anak + $tambahan + $pembina + $special;
      $costsDetail['makan-paket'] = [
        'slug' => 'makan-paket',
        'name' => 'Makan Paket',
        'plan_qty' => $qty,
        'plan_price' => $inv->profitLoss->eat_price,
        'plan_total' => 0,
        'act_qty' => $qty,
        'act_price' => $inv->profitLoss->eat_price,
        'act_total' => 0,
      ];
    }

    if ($inv->profitLoss->eat_child_price > 0) {
      $qty = $anak;
      $costsDetail['makan-anak'] = [
        'slug' => 'makan-anak',
        'name' => 'Makan Porsi Anak',
        'plan_qty' => $qty,
        'plan_price' => $inv->profitLoss->eat_child_price,
        'plan_total' => 0,
        'act_qty' => $qty,
        'act_price' => $inv->profitLoss->eat_child_price,
        'act_total' => 0,
      ];
    }

    if ($inv->profitLoss->eat_prasmanan_price > 0) {
      $qty = $anak + $tambahan + $pembina + $special;
      $costsDetail['makan-prasmanan'] = [
        'slug' => 'makan-prasmanan',
        'name' => 'Makan Prasmanan',
        'plan_qty' => $qty,
        'plan_price' => $inv->profitLoss->eat_prasmanan_price,
        'plan_total' => 0,
        'act_qty' => $qty,
        'act_price' => $inv->profitLoss->eat_prasmanan_price,
        'act_total' => 0,
      ];
    }

    // $costsDetail['lain-lain'] = [
    //   'slug' => 'lain-lain',
    //   'name' => 'Lain-lain',
    //   'plan_qty' => 1,
    //   'plan_price' => $backupPrice * $totalBus,
    //   'plan_total' => 0,
    //   'act_qty' => 1,
    //   'act_price' => 0,
    //   'act_total' => 0,
    // ];

    return $costsDetail;
  }

  public static function getOtherCostItem(int|float $price = 0): array
  {
    $inv = static::$invoice;

    $totalBus = $inv->order->orderFleets()->count();

    $backupPrice = $inv->profitLoss->backup_price;

    $otherCost = [
      'slug' => 'lain-lain',
      'name' => 'Lain-lain',
      'plan_qty' => 1,
      'plan_price' => $backupPrice * $totalBus,
      'plan_total' => 0,
      'act_qty' => 1,
      'act_price' => $price,
      'act_total' => 0,
    ];

    return $otherCost;
  }
}
