<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TourPackageResource\Pages;
use App\Filament\Resources\TourPackageResource\RelationManagers;
use App\Models\TourPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TourPackageResource extends Resource
{
  protected static ?string $model = TourPackage::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\FileUpload::make('image')
          ->image()->columnSpanFull(),
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(255)->columnSpanFull(),
        Forms\Components\TextInput::make('city')
          ->required()
          ->maxLength(255)->columnSpanFull(),
        Forms\Components\RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        Forms\Components\TextInput::make('order_total')
          ->required()
          ->numeric()
          ->default(0)->columnSpanFull(),
        Forms\Components\TextInput::make('price')
          ->required()
          ->numeric()
          ->prefix('Rp')->columnSpanFull(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('image'),
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('city')
          ->searchable(),
        Tables\Columns\TextColumn::make('order_total')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('price')
          ->money()
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
      'index' => Pages\ListTourPackages::route('/'),
      'create' => Pages\CreateTourPackage::route('/create'),
      'view' => Pages\ViewTourPackage::route('/{record}'),
      'edit' => Pages\EditTourPackage::route('/{record}/edit'),
    ];
  }
}
