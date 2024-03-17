<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Regency;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TourPackage;
use Illuminate\Support\Arr;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TourPackageResource\Pages;
use App\Filament\Resources\TourPackageResource\RelationManagers;

class TourPackageResource extends Resource
{
  protected static ?string $model = TourPackage::class;

  protected static ?string $navigationGroup = 'Master Data';

  protected static ?string $recordTitleAttribute = 'name';

  protected static ?string $modelLabel = 'Paket Wisata';

  protected static ?string $navigationLabel = 'Paket Wisata';

  protected static ?string $pluralModelLabel = 'Paket Wisata';

  protected static ?string $navigationIcon = 'heroicon-o-ticket';

  protected static ?string $activeNavigationIcon = 'heroicon-s-ticket';

  protected static ?int $navigationSort = 1;

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\FileUpload::make('image')
          ->required()
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('paket-wisata')
          ->imageCropAspectRatio('1:1')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(255)
          ->columnSpanFull(),
        Forms\Components\TextInput::make('duration')
          ->label('Durasi')
          ->required()
          ->numeric()
          ->columnSpanFull(),
        Forms\Components\Select::make('city')
          ->label('Destinasi')
          ->required()
          ->searchable()
          ->live()
          // ->getSearchResultsUsing(function (string $search): array {
          //   $data = Http::get("https://api.cahyadsn.com/search/$search");
          //   if (isset ($data['error']) || $data['data'] == 'Data not found')
          //     return [];
          //   return Arr::pluck($data['data'], 'nama', 'nama');
          // })
          ->getSearchResultsUsing(fn(string $search): array => Regency::where('name', 'like', "%{$search}%")->limit(5)->pluck('name', 'name')->toArray())
          ->columnSpanFull(),
        Forms\Components\RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        Forms\Components\TextInput::make('price')
          ->required()
          ->numeric()
          ->prefix('Rp')
          ->columnSpanFull(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('image')
          ->simpleLightbox(),
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
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make()->color('primary')
            ->after(function (TourPackage $record) {
              if ($record->isDirty('image')) {
                Storage::disk('public')->delete($record->image);
              }
            }),
          Tables\Actions\DeleteAction::make()
            ->after(function (TourPackage $record) {
              if ($record->image) {
                Storage::disk('public')->delete($record->image);
              }
            })
        ])
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
