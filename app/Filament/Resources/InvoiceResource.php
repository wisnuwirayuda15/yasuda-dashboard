<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Invoice;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\CostDetail;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Awcodes\TableRepeater\Header;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\InvoiceResource\Pages;
use Awcodes\TableRepeater\Components\TableRepeater;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Rupadana\FilamentCustomForms\Components\InputGroup;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use Doctrine\DBAL\SQL\Parser\Visitor;
use Filament\Forms\Components\Section;
use PhpParser\Node\Expr\Cast\Object_;

class InvoiceResource extends Resource
{
  protected static ?string $model = Invoice::class;

  protected static ?string $navigationIcon = 'fas-file-invoice';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->helperText('Code is generated automatically.')
          ->unique(Invoice::class, 'code', ignoreRecord: true)
          ->default(get_code(new Invoice)),
        Forms\Components\Select::make('order_id')
          ->required()
          ->relationship('order', 'id', fn(Builder $query) => $query->doesntHave('invoice'), ignoreRecord: true)
          ->native(false)
          ->prefixIcon(fn() => OrderResource::getNavigationIcon())
          ->editOptionForm(fn(Form $form) => OrderResource::form($form))
          ->createOptionForm(fn(Form $form) => OrderResource::form($form))
          ->editOptionModalHeading('Edit Order')
          ->createOptionModalHeading('Create Order')
          ->allowHtml()
          ->getOptionLabelFromRecordUsing(
            function (Order $record) {
              return view('livewire.order-badge', ['record' => $record]);
            }
          ),
        self::getMainCostsSection(),
        self::getShirtsSection(),
        Forms\Components\RichEditor::make('notes')
        ->columnSpanFull(),
        Forms\Components\TextInput::make('payment_detail'),
        Forms\Components\TextInput::make('down_payments'),
        Forms\Components\TextInput::make('total_transactions'),
      ]);
  }

  public static function getMainCostsSection(): Section
  {
    return Forms\Components\Section::make('Detail Biaya Utama')
      ->description('Biaya utama perjalanan.')
      ->columns(1)
      ->columnSpanFull()
      ->schema([
        TableRepeater::make('main_costs')
          ->stackAt(MaxWidth::ExtraLarge)
          ->streamlined()
          ->hiddenLabel()
          ->addActionLabel('Tambah biaya')
          ->required()
          ->live()
          ->deletable(false)
          ->addable(false)
          ->columnSpanFull()
          ->default(self::getDefaultMainCostItems())
          ->deleteAction(fn(Action $action) => $action->requiresConfirmation())
          ->headers([
            Header::make('Keterangan')
              ->align(Alignment::Center)
              ->width('300px'),
            Header::make('Jumlah')
              ->align(Alignment::Center)
              ->width('50px'),
            Header::make('Harga (Gross)')
              ->align(Alignment::Center)
              ->width('150px'),
            Header::make('Cashback')
              ->align(Alignment::Center)
              ->width('150px'),
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
            Forms\Components\Hidden::make('slug'),
            Forms\Components\TextInput::make('name')
              ->distinct()
              ->readOnly()
              ->required()
              ->columnSpanFull(),
            Forms\Components\TextInput::make('qty')
              ->integer()
              ->required()
              ->minValue(0)
              ->default(0)
              ->live(true)
              ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set('qty', 0) : true),
            Forms\Components\TextInput::make('price')
              ->required()
              ->numeric()
              ->prefix(fn(?int $state) => filled($state) ? 'Rp' : false)
              ->minValue(0)
              ->default(0)
              ->live(true)
              ->disabled(fn(?int $state) => blank($state))
              ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set('price', 0) : true),
            Forms\Components\TextInput::make('cashback')
              ->required()
              ->numeric()
              ->prefix(fn(?int $state) => filled($state) ? 'Rp' : false)
              ->minValue(0)
              ->default(0)
              ->live(true)
              ->disabled(fn(?int $state) => blank($state))
              ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set('cashback', 0) : true),
            Forms\Components\Placeholder::make('total_gross_transaction')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-green-500'])
              ->content(fn(Get $get) => Number::currency((int) $get('qty') * (int) $get('price'), 'IDR', 'id')),
            Forms\Components\Placeholder::make('total_cashback')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-red-500'])
              ->content(fn(Get $get) => Number::currency((int) $get('qty') * (int) $get('cashback'), 'IDR', 'id')),
            Forms\Components\Placeholder::make('total_net_transaction')
              ->hiddenLabel()
              ->content(fn(Get $get) => Number::currency((int) $get('qty') * (int) $get('price') - (int) $get('qty') * (int) $get('cashback'), 'IDR', 'id')),
          ]),

        Forms\Components\Fieldset::make('Total')
          ->schema([
            Forms\Components\Placeholder::make('total_qty')
              ->content(fn(Get $get): string => array_sum(array_map(fn($cost) => (int) $cost['qty'], $get('main_costs'))) ?: 0),
            Forms\Components\Placeholder::make('total_gross_transactions')
              ->extraAttributes(['class' => 'text-green-500'])
              ->content(fn(Get $get) => Number::currency(array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['price'], $get('main_costs'))), 'IDR', 'id')),
            Forms\Components\Placeholder::make('total_cashbacks')
              ->extraAttributes(['class' => 'text-red-500'])
              ->content(fn(Get $get) => Number::currency(array_sum(array_map(fn($cost) => (int) $cost['qty'] * (int) $cost['cashback'], $get('main_costs'))), 'IDR', 'id')),
            Forms\Components\Placeholder::make('total_net_transactions')
              ->content(fn(Get $get) => Number::currency(array_sum(array_map(fn($cost) => ((int) $cost['qty'] * (int) $cost['price']) - ((int) $cost['qty'] * (int) $cost['cashback']), $get('main_costs'))), 'IDR', 'id')),
          ]),
      ]);
  }

  public static function getShirtsSection(): Section
  {
    return Forms\Components\Section::make('Tambahan Kaos')
      ->description('Detail tambahan dan biaya kaos.')
      ->columnSpanFull()
      ->schema([
        TableRepeater::make('additional_shirts')
          ->stackAt(MaxWidth::ExtraLarge)
          ->streamlined()
          ->hiddenLabel()
          ->addActionLabel('Tambah biaya')
          ->required()
          ->live()
          ->deletable(false)
          ->addable(false)
          ->columnSpanFull()
          ->default(self::getAdditionalShirtsItems())
          ->deleteAction(fn(Action $action) => $action->requiresConfirmation())
          ->headers([
            Header::make('Keterangan')
              ->align(Alignment::Center),
            Header::make('Jumlah')
              ->align(Alignment::Center)
              ->width('50px'),
            Header::make('Harga')
              ->align(Alignment::Center)
              ->width('150px'),
            Header::make('Total')
              ->align(Alignment::Center)
              ->width('150px'),
          ])
          ->schema([
            Forms\Components\Hidden::make('slug'),
            Forms\Components\TextInput::make('name')
              ->distinct()
              ->readOnly()
              ->required()
              ->columnSpanFull(),
            Forms\Components\TextInput::make('qty')
              ->required()
              ->integer()
              ->minValue(0)
              ->default(0)
              ->live(true)
              ->dehydrated()
              ->disabled(fn(Get $get) => str_contains($get('slug'), 'qty'))
              ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set('qty', 0) : true),
            Forms\Components\TextInput::make('price')
              // ->required()
              ->numeric()
              ->minValue(0)
              ->default(0)
              ->live(true)
              ->dehydrated()
              ->prefix(fn(Get $get) => str_contains($get('slug'), 'price') ? false : 'Rp')
              ->disabled(fn(Get $get) => str_contains($get('slug'), 'price'))
              ->afterStateUpdated(fn(?int $state, Set $set) => blank($state) || !is_int($state) || $state < 0 ? $set('price', 0) : true),
            Forms\Components\Placeholder::make('total')
              ->hiddenLabel()
              ->content(fn(Get $get) => str_contains($get('slug'), 'total') ? null : Number::currency((int) $get('qty') * (int) $get('price'), 'IDR', 'id')),
          ]),
        Forms\Components\Placeholder::make('kaos_tercover_paket')
          ->label('Kaos Tercover Paket (Program + Ibu & Anak Pangku)')
          ->content(
            function (Get $get) {
              $program = self::getCostItem($get, 'main_costs', 'program')['qty'];
              $anak = self::getCostItem($get, 'main_costs', 'ibu-anak-pangku')['qty'];
              return (int) $program + (int) $anak;
            }
          ),
        // Forms\Components\Placeholder::make('total_tambahan_kaos')
        //   ->label('Total Tambahan Kaos')
        //   ->content(
        //     function (Get $get) {
        //       $anak = self::getCostItem($get, 'additional_shirts', 'program');
        //       $anak = self::getCostItem($get, 'main_costs', 'ibu-anak-pangku')['qty'];
        //       return (int) $program + (int) $anak;
        //     }
        //   ),
      ]);
  }

  public static function getDefaultMainCostItems(): array
  {
    return [
      1 => [
        "slug" => "program",
        "name" => "Program",
        "qty" => 0,
        "price" => 75000,
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

  public static function getAdditionalShirtsItems(): array
  {
    return [
      1 => [
        "slug" => "total-kaos-diserahkan-price-total",
        "name" => "Total Kaos Diserahkan",
        "price" => null,
        "qty" => 0,
      ],
      2 => [
        "slug" => "kaos-tercover-paket-price-qty-total",
        "name" => "Kaos Tercover Paket (Program + Ibu & Anak Pangku)",
        "price" => null,
        "qty" => 0,
      ],
      3 => [
        "slug" => "selisih-kaos-anak-qty",
        "name" => "Selisih Kaos Anak",
        "price" => 25000,
        "qty" => 0,
      ],
      4 => [
        "slug" => "tamb-stel-guru",
        "name" => "Tambahan 1-Stel Guru",
        "price" => 120000,
        "qty" => 0,
      ],
      5 => [
        "slug" => "tamb-kaos-dewasa",
        "name" => "Tambahan Kaos Dewasa",
        "price" => 80000,
        "qty" => 0,
      ],
    ];

  }

  public static function getCostItem(Get $get, string $data, string $slug): array
  {
    return collect($get($data))->firstWhere('slug', $slug);
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
