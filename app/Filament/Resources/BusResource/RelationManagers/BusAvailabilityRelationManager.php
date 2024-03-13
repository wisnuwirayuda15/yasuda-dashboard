<?php

namespace App\Filament\Resources\BusResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use App\Enums\BusStatus;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BusAvailabilityRelationManager extends RelationManager
{
  protected static string $relationship = 'busAvailability';

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Select::make('status')
          ->required()
          ->native(false)
          ->options(BusStatus::class)
          ->default('available')
          ->live()
          ->prefixIcon(function ($state) :string {
            if ($state === 'available') {
              return 'heroicon-m-check-circle';
            } else if ($state === 'on_trip') {
              return 'heroicon-m-information-circle';
            } else if ($state === 'canceled') {
              return 'heroicon-m-x-circle';
            } else {
              return '';
            }
          })
          ->prefixIconColor(function ($state) :string {
            if ($state === 'available') {
              return 'success';
            } else if ($state === 'on_trip') {
              return 'info';
            } else if ($state === 'canceled') {
              return 'danger';
            } else {
              return '';
            }
          }),
        Forms\Components\DatePicker::make('date')
          ->required()
          ->native(false)
          ->closeOnDateSelection()
          ->default(now())
          ->prefixIcon('heroicon-m-calendar-days'),
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('Ketersediaan')
      ->columns([
        Tables\Columns\TextColumn::make('date')
          ->date()
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('status')
          ->badge(),
      ])
      ->filters([
        //
      ])
      ->headerActions([
        Tables\Actions\CreateAction::make(),
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }
}
