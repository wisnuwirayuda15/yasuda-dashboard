<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Reward;
use App\Enums\CashFlow;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\RewardResource\Pages;
use App\Filament\Resources\RewardResource\RelationManagers;

class RewardResource extends Resource
{
  protected static ?string $model = Reward::class;

  protected static ?string $navigationIcon = 'fluentui-reward-12';

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MARKETING->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      Placeholder::make('balance')
        ->label('Saldo Awal')
        ->hiddenOn('view')
        ->content(function (Get $get, Set $set, string $operation, Placeholder $component) use ($form) {
          $balance = Customer::find($get('customer_id'))?->getBalance() ?? 0;
          if ($operation === 'edit') {
            $balance += $form->getRecord()?->amount ?? 0;
          }
          $set($component, $balance);
          $color = match (true) {
            $balance === 0 => 'warning',
            $balance < 0 => 'danger',
            default => 'success',
          };
          return view('filament.components.badges.default', ['text' => idr($balance), 'color' => $color, 'big' => true]);
        }),
      Placeholder::make('final_balance')
        ->label('Saldo Akhir')
        ->hiddenOn('view')
        ->content(function (Get $get, Set $set, Placeholder $component) {
          $balance = $get('balance');
          if (is_numeric($get('amount'))) {
            $balance -= $get('amount');
          }
          $set($component, $balance);
          $color = match (true) {
            $balance === 0 => 'warning',
            $balance < 0 => 'danger',
            default => 'success',
          };
          return view('filament.components.badges.default', ['text' => idr($balance), 'color' => $color, 'big' => true]);
        }),
      Select::make('customer_id')
        ->required()
        ->live()
        ->allowHtml()
        ->relationship('customer', 'name', fn(Builder $query) => $query->whereHas('orders.invoice.loyaltyPoint')),
      TextInput::make('amount')
        ->required()
        ->default(0)
        ->currency(minValue: false, minusValidation: false),
      DatePicker::make('date')
        ->default(today())
        ->required(),
      ToggleButtons::make('cash_status')
        ->required()
        ->inline()
        ->disabled()
        ->dehydrated()
        ->options(CashFlow::class)
        ->default(CashFlow::OUT->value),
      RichEditor::make('description')
        ->hidden(fn(string $operation, ?string $state) => ($operation === 'view' && blank($state)))
        ->columnSpanFull(),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('customer.code')
          ->searchable()
          ->badge()
          ->label('Customer Code'),
        TextColumn::make('customer.name')
          ->searchable()
          ->label('Customer Name')
          ->sortable(),
        TextColumn::make('description')
          ->searchable()
          ->html()
          ->placeholder('No description')
          ->limit(100),
        TextColumn::make('cash_status')
          ->badge(),
        TextColumn::make('date')
          ->sortable()
          ->label('Tanggal')
          ->date('d/m/Y'),
        TextColumn::make('amount')
          ->money('IDR')
          ->sortable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageRewards::route('/'),
    ];
  }
}
