<?php

namespace App\Filament\Resources;

use App\Enums\CostDetailCategory;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\CostDetail;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CostDetailResource\Pages;
use App\Filament\Resources\CostDetailResource\RelationManagers;

class CostDetailResource extends Resource
{
  protected static ?string $model = CostDetail::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(255),
        Forms\Components\ToggleButtons::make('category')
          ->required()
          ->inline()
          ->default(CostDetailCategory::DEFAULT->value)
          ->options(CostDetailCategory::class),
        Forms\Components\TextInput::make('price')
          ->required()
          ->numeric()
          ->default(0)
          ->prefix('Rp'),
        Forms\Components\TextInput::make('cashback')
          ->required()
          ->numeric()
          ->default(0)
          ->prefix('Rp'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('price')
          ->money()
          ->sortable(),
        Tables\Columns\TextColumn::make('cashback')
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
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
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
      'index' => Pages\ListCostDetails::route('/'),
      'create' => Pages\CreateCostDetail::route('/create'),
      'view' => Pages\ViewCostDetail::route('/{record}'),
      'edit' => Pages\EditCostDetail::route('/{record}/edit'),
    ];
  }
}
