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
use App\Enums\SleeveType;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ShirtMaterial;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
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
        static::getShirtTabs(),
        static::getTotalSection(),
        Checkbox::make('confirmation')->confirmation()
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
        Tables\Columns\TextColumn::make('status')
          ->label('Status')
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

  public static function getShirtTabs(): Tabs
  {
    return Tabs::make('Shirt')
      ->columnSpanFull()
      ->contained(false)
      ->tabs([
        static::getShirtSizeTab('child', 'Kaos Anak'),
        static::getShirtSizeTab('adult', 'Kaos Dewasa'),
        static::getShirtSizeTab('male_teacher', 'Kaos Guru Laki-laki'),
        static::getShirtSizeTab('female_teacher', 'Kaos Guru Perempuan'),
      ]);
  }

  public static function getTotalSection(): Section
  {
    return Section::make('Total')
      ->columns(2)
      ->schema([
        Placeholder::make('child_total')
          ->label('Baju Anak')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_sum(array_map(fn($total) => $total['qty'], $get('child'))) ?: 0;
            $set($component, $total);
            return $total;
          }),
        Placeholder::make('adult_total')
          ->label('Baju Dewasa')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_sum(array_map(fn($total) => $total['qty'], $get('adult'))) ?: 0;
            $set($component, $total);
            return $total;
          }),
        Placeholder::make('male_teacher_total')
          ->label('Baju Guru Laki-laki')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_sum(array_map(fn($total) => $total['qty'], $get('male_teacher'))) ?: 0;
            $set($component, $total);
            return $total;
          }),
        Placeholder::make('female_teacher_total')
          ->label('Baju Guru Perempuan')
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = array_sum(array_map(fn($total) => $total['qty'], $get('female_teacher'))) ?: 0;
            $set($component, $total);
            return $total;
          }),
        Placeholder::make('total')
          ->label('Seluruh Baju Wisata')
          ->dehydrated()
          ->content(function (Get $get, Set $set, Placeholder $component) {
            $total = $get('child_total') + $get('adult_total') + $get('male_teacher_total') + $get('female_teacher_total');
            $set($component, $total);
            return $total;
          }),
      ]);
  }

  public static function getShirtSizeTab(string $name, string $label, string $icon = 'fas-shirt'): Tab
  {
    return Tab::make($label)
      ->icon($icon)
      ->schema([
        Section::make($label)
          ->schema([
            Group::make([
              ColorPicker::make("{$name}_color")
                ->required()
                ->default('#FFFFFF')
                ->label('Warna'),
              Select::make("{$name}_sleeve")
                ->required()
                ->label('Jenis Lengan')
                ->options(SleeveType::class),
              Select::make("{$name}_material")
                ->required()
                ->label('Bahan')
                ->default(ShirtMaterial::PE->value)
                ->options(ShirtMaterial::class),
            ])->columns(3)
              ->visible(fn(Get $get) => $get($name)),
            Repeater::make($name)
              ->itemLabel(fn(array $state): string => filled($state['size']) && filled($state['qty']) ? strtoupper($state['size']) . ': ' . $state['qty'] : '')
              ->addActionLabel('Tambah Kaos')
              ->hiddenLabel()
              ->collapsible()
              ->default([])
              ->schema([
                Select::make('size')
                  ->required()
                  ->options(ShirtSize::class)
                  ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                TextInput::make('qty')
                  ->required()
                  ->default(0)
                  ->qty(),
              ])->columns(2)
          ])
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
