<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\SalesVisit;
use Filament\Tables\Table;
use App\Enums\EmployeeRole;
use Illuminate\Support\Str;
use App\Enums\CustomerStatus;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SalesVisitResource\Pages;
use App\Filament\Resources\SalesVisitResource\RelationManagers;

class SalesVisitResource extends Resource
{
  protected static ?string $model = SalesVisit::class;

  protected static ?string $navigationIcon = 'fas-handshake';

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
    return $form
      ->schema([
        Section::make('Customer')
          ->schema([
            Select::make('customer_id')
              ->relationship('customer', 'name')
              ->required(),
            Radio::make('priority')
              ->required()
              ->inline()
              ->inlineLabel(false)
              ->options([
                'yes' => 'Yes',
                'no' => 'No',
              ]),
          ]),
        Section::make('Kunjungan')
          ->schema([
            Radio::make('visit_status')
              ->required()
              ->live()
              ->inline()
              ->label('Sudah dikunjungi?')
              ->inlineLabel(false)
              ->default('not_yet')
              ->options([
                'done' => 'Sudah',
                'not_yet' => 'Belum',
              ])
              ->afterStateUpdated(fn(Set $set) => $set('employee_id', null))
              ->loadingIndicator(),
            Group::make()
              ->visible(fn(Get $get) => $get('visit_status') === 'done')
              ->schema([
                Select::make('employee_id')
                  ->required()
                  ->label('Visited By')
                  ->relationship('employee', 'name', fn(Builder $query) => $query->whereNot('role', EmployeeRole::TOUR_LEADER->value)),
                DatePicker::make('date')
                  ->required()
                  ->label('Tanggal Kunjungan')
                  ->default(today()),
                FileUpload::make('image')
                  ->label('Bukti Kunjungan')
                  ->image()
                  ->imageEditor()
                  ->maxSize(10240)
                  ->directory('sales-visit')
                  ->imageResizeMode('cover')
                  ->columnSpanFull(),
              ])
          ])
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('customer.code')
          ->badge()
          ->searchable()
          ->label('Customer Code')
          ->sortable(),
        TextColumn::make('customer.name')
          ->label('Customer Name')
          ->sortable()
          ->searchable()
          ->tooltip('Lihat Customer')
          ->url(fn(SalesVisit $record) => CustomerResource::getUrl('view', ['record' => $record->customer_id])),
        TextColumn::make('employee.name')
          ->placeholder('Not visited')
          ->searchable()
          ->sortable(),
        TextColumn::make('date')
          ->date()
          ->label('Tanggal Kunjungan')
          ->date('d/m/Y'),
        TextColumn::make('priority')
          ->badge()
          ->color(fn(string $state): string => $state === 'yes' ? 'success' : 'danger')
          ->formatStateUsing(fn(string $state): string => Str::headline($state)),
        TextColumn::make('visit_status')
          ->badge()
          ->color(fn(string $state): string => $state === 'done' ? 'success' : 'danger')
          ->formatStateUsing(fn(string $state): string => Str::headline($state)),
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

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListSalesVisits::route('/'),
      'create' => Pages\CreateSalesVisit::route('/create'),
      'view' => Pages\ViewSalesVisit::route('/{record}'),
      'edit' => Pages\EditSalesVisit::route('/{record}/edit'),
    ];
  }
}
