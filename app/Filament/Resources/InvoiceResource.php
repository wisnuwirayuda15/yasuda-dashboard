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
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\InvoiceResource\Pages;
use Awcodes\TableRepeater\Components\TableRepeater;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InvoiceResource\RelationManagers;

class InvoiceResource extends Resource
{
  protected static ?string $model = Invoice::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->helperText('Code are generated automatically.')
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
              return Blade::render(
                '
                  <span class="flex">
                    <x-filament::badge class="mr-2">{{ $record->code }}</x-filament::badge>
                    <span>{{ $record->customer->name }}</span>
                  </span>
                ',
                ['record' => $record]
              );
            }
          ),
        self::getMainCostsSection(),
        Forms\Components\RichEditor::make('special_notes')
          ->required()
          ->live()
          // ->afterStateUpdated(function(Get $get) {
          //   $x = collect($get('main_costs'));
          //   dd($x->where('name', 'Ibu & Anak Pangku')->first());
          // })
          ->columnSpanFull(),
      ]);
  }

  public static function getMainCostsSection(): Component
  {
    return Forms\Components\Section::make('Detail Biaya Utama')
      ->description('Biaya utama perjalanan.')
      ->columns(4)
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
          ->columnSpanFull()
          ->default((new Invoice)->getDefaultMainCosts())
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
            Forms\Components\TextInput::make('name')
              ->live(true)
              ->distinct()
              ->required()
              ->columnSpanFull()
              ->placeholder('Isi keterangan disini...')
              ->suffixAction(
                Forms\Components\Actions\Action::make('select_cost_detail')
                  ->icon('tabler-playlist-add')
                  ->form([
                    Forms\Components\Select::make('cost_detail')
                      ->required()
                      ->native(false)
                      ->searchable()
                      ->options(CostDetail::query()->pluck('name', 'id')),
                  ])
                  ->action(function (array $data, Get $get, Set $set) {
                    $costDetail = CostDetail::findOrFail($data)->toArray()[0];
                    $set('name', $costDetail['name']);
                    $set('price', $costDetail['price']);
                    $set('cashback', $costDetail['cashback']);
                    self::updateTotalMainCostPriceAndCashback($get, $set);
                  }),
              ),
            Forms\Components\TextInput::make('qty')
              ->integer()
              ->live(true)
              ->required()
              ->minValue(0)
              ->default(0),
            Forms\Components\TextInput::make('price')
              ->required()
              ->live(true)
              ->numeric()
              ->prefix('Rp')
              ->minValue(0)
              ->default(0),
            Forms\Components\TextInput::make('cashback')
              ->required()
              ->live(true)
              ->numeric()
              ->prefix('Rp')
              ->minValue(0)
              ->default(0),
            Forms\Components\Placeholder::make('total_gross_transaction')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-green-500'])
              ->content(fn(Get $get) => Number::currency($get('qty') * $get('price'), 'IDR', 'id')),
            Forms\Components\Placeholder::make('total_cashback')
              ->hiddenLabel()
              ->extraAttributes(['class' => 'text-red-500'])
              ->content(fn(Get $get) => Number::currency($get('qty') * $get('cashback'), 'IDR', 'id')),
            Forms\Components\Placeholder::make('total_net_transaction')
              ->hiddenLabel()
              ->content(fn(Get $get) => Number::currency($get('qty') * $get('price') - $get('qty') * $get('cashback'), 'IDR', 'id')),
          ]),
        Forms\Components\Placeholder::make('total_qty')
          ->content(fn(Get $get): string => array_sum(array_column($get('main_costs'), 'qty')) ?: 0),

        Forms\Components\Placeholder::make('total_gross_transactions')
          ->content(fn(Get $get) => Number::currency(array_sum(array_map(fn($cost) => $cost['qty'] * $cost['price'], $get('main_costs'))), 'IDR', 'id')),

        Forms\Components\Placeholder::make('total_cashbacks')
          ->content(fn(Get $get) => Number::currency(array_sum(array_map(fn($cost) => $cost['qty'] * $cost['cashback'], $get('main_costs'))), 'IDR', 'id')),

        Forms\Components\Placeholder::make('total_net_transactions')
          ->content(fn(Get $get) => Number::currency(array_sum(array_map(fn($cost) => ($cost['qty'] * $cost['price']) - ($cost['qty'] * $cost['cashback']), $get('main_costs'))), 'IDR', 'id')),
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
