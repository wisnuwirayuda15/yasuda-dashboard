<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Shirt;
use App\Models\Invoice;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Enums\ShirtSize;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ShirtResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ShirtResource\RelationManagers;

class ShirtResource extends Resource
{
  protected static ?string $model = Shirt::class;

  protected static ?string $navigationIcon = 'fas-shirt';

  protected static ?Invoice $invoice = null;

  protected static ?int $totalShirt = null;

  public static function form(Form $form): Form
  {
    $record = $form->getRecord();

    if (blank($record)) {
      $invoice = request('invoice');

      if (blank($invoice) && Route::current()->getName() === 'livewire.update') {
        $parameters = getUrlQueryParameters(url()->previous());
        $invoice = $parameters['invoice'];
      }

      static::$invoice = Invoice::where('code', $invoice)->doesntHave('shirt')->with(['order', 'order.customer'])->firstOrFail();
    } else {
      static::$invoice = $record->invoice;
    }

    return $form
      ->schema([
        static::getGeneralInformationSection(),
        static::getShirtSection()
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('invoice.code')
          ->sortable(),
        Tables\Columns\TextColumn::make('total')
          ->label('Total Baju')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('invoice.order.trip_date')
          ->label('Tanggal')
          ->sortable(),
        Tables\Columns\TextColumn::make('invoice.order.customer.name')
          ->sortable(),
        Tables\Columns\TextColumn::make('invoice.order.customer.address')
          ->label('Alamat')
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
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
        ])
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
      'index' => Pages\ListShirts::route('/'),
      'create' => Pages\CreateShirt::route('/create'),
      'view' => Pages\ViewShirt::route('/{record}'),
      'edit' => Pages\EditShirt::route('/{record}/edit'),
    ];
  }

  public static function getGeneralInformationSection(): Section
  {
    return Section::make('General Information')
      ->schema([
        Select::make('invoice_id')
          ->required()
          ->unique(ignoreRecord: true)
          ->allowHtml()
          ->disabled()
          ->dehydrated()
          ->live(true)
          ->columnSpanFull()
          ->prefixIcon(InvoiceResource::getNavigationIcon())
          ->default(fn() => static::$invoice->id)
          ->relationship('invoice')
          ->getOptionLabelFromRecordUsing(fn(Invoice $record) => view('filament.components.badges.invoice', compact('record'))),
        Group::make([
          Placeholder::make('invoice_code')
            ->label('Invoice :')
            ->inlineLabel()
            ->content(fn() => static::$invoice->code),
          Placeholder::make('customer_name')
            ->label('Customer :')
            ->inlineLabel()
            ->content(fn() => static::$invoice->order->customer->name),
          Placeholder::make('trip_date')
            ->label('Tanggal :')
            ->inlineLabel()
            ->content(fn() => static::$invoice->order->trip_date->translatedFormat('d F Y')),

          Fieldset::make('Jumlah Orang')
            ->schema([
              static::getCustomerPlaceholder('Program'),
              static::getCustomerPlaceholder('Ibu & Anak Pangku'),
              static::getCustomerPlaceholder('Tambahan Orang'),
              static::getCustomerPlaceholder('Pembina'),
              Placeholder::make('total_customer')
                ->inlineLabel()
                ->columnSpanFull()
                ->content(fn() => static::$totalShirt),
            ])

        ])->hidden(fn(Get $get) => blank($get('invoice_id')))
      ]);
  }

  public static function getShirtSizeRepeater(string $name): Repeater
  {
    return Repeater::make($name)
      ->itemLabel(fn(array $state): ?string => 'Ukuran: ' . strtoupper($state['size']))
      ->label('Kaos ' . ($name === 'child' ? 'Anak' : 'Dewasa'))
      ->addActionLabel('Tambah Ukuran')
      ->schema([
        Select::make('size')
          ->required()
          ->options(ShirtSize::class)
          ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
        TextInput::make('qty')
          ->required()
          ->default(0)
          ->qty(),
      ])
      ->columns(2);
  }

  public static function getShirtSection(): Section
  {
    return Section::make('Shirt')
      ->columns(2)
      ->schema([
        static::getShirtSizeRepeater('child'),
        static::getShirtSizeRepeater('adult'),
        Placeholder::make('child_total')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_sum(array_map(fn($total) => $total['qty'], $get('child'))) ?: 0;
            $set($component, $total);
            return $total;
          }),
        Placeholder::make('adult_total')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_sum(array_map(fn($total) => $total['qty'], $get('adult'))) ?: 0;
            $set($component, $total);
            return $total;
          }),
        Placeholder::make('total')
          ->label('Total Seluruh Baju Wisata')
          ->dehydrated()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('child_total') + $get('adult_total');
            $set($component, $total);
            return $total;
          }),
      ]);
  }

  public static function getCustomerPlaceholder(string $label): Placeholder
  {
    $slug = Str::slug($label);

    return Placeholder::make($slug)
      ->label("$label :")
      ->inlineLabel()
      ->columnSpanFull()
      ->content(function () use ($slug) {
        $qty = collect(static::$invoice->main_costs)->firstWhere('slug', $slug)['qty'];
        static::$totalShirt += $qty;
        return $qty;
      });
  }
}
