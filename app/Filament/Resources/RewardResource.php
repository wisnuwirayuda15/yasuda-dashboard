<?php

namespace App\Filament\Resources;

use Closure;
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
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\RewardResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
          $color = $balance === 0 ? 'warning' : ($balance < 0 ? 'danger' : 'success');
          return view('filament.components.badges.default', ['text' => idr($balance), 'color' => $color, 'big' => true]);
        }),
      Placeholder::make('final_balance')
        ->label('Saldo Akhir')
        ->hiddenOn('view')
        ->content(function (Get $get, Set $set, Placeholder $component) {
          $balance = $get('balance') - $get('amount');
          $set($component, $balance);
          $color = $balance === 0 ? 'warning' : ($balance < 0 ? 'danger' : 'success');
          return view('filament.components.badges.default', ['text' => idr($balance), 'color' => $color, 'big' => true]);
        }),
      Select::make('customer_id')
        ->required()
        ->live()
        ->allowHtml()
        ->relationship('customer', 'name'),
      TextInput::make('amount')
        ->required()
        ->rules([
          fn(Get $get): Closure => function (string $attribute, float $value, Closure $fail) use ($get) {
            if ($value > (float) $get('balance')) {
              $fail('Saldo tidak mencukupi.');
            }
          },
        ])
        ->currency(minValue: 1),
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
          ->limit(100),
        TextColumn::make('cash_status'),
        TextColumn::make('date')
          ->sortable()
          ->label('Tanggal Pelaksanaan')
          ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
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
