<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\SalesVisit;
use Filament\Tables\Table;
use App\Enums\CustomerStatus;
use App\Enums\EmployeeRole;
use Filament\Resources\Resource;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SalesVisitResource\Pages;
use App\Filament\Resources\SalesVisitResource\RelationManagers;
use Filament\Forms\Components\Section;

class SalesVisitResource extends Resource
{
  protected static ?string $model = SalesVisit::class;

  protected static ?string $navigationIcon = 'fas-handshake';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Section::make('Customer')
          ->schema([
            Select::make('customer_id')
              ->relationship('customer', 'name')
              // ->relationship('customer', 'name', fn(Builder $query) => $query->where('status', CustomerStatus::CANDIDATE->value), true)
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
              ->options([
                'done' => 'Sudah',
                'not_yet' => 'Belum',
              ])
              ->afterStateUpdated(fn(Set $set) => $set('employee_id', null)),
            Select::make('employee_id')
              ->required()
              ->label('Visited By')
              ->relationship('employee', 'name')
              // ->relationship('employee', 'name', fn(Builder $query) => $query->where('role', '!=', EmployeeRole::TOUR_LEADER->value), true)
              ->visible(fn(Get $get) => $get('visit_status') === 'done'),
          ])
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('customer.code')
          ->label('Customer Code')
          ->sortable(),
        Tables\Columns\TextColumn::make('customer.name')
          ->label('Customer Name')
          ->sortable(),
        Tables\Columns\TextColumn::make('employee.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('priority')
          ->searchable(),
        Tables\Columns\TextColumn::make('visit_status')
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
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
