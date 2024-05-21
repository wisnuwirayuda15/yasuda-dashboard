<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Filament\Resources\ProfitLossResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfitLossRelationManager extends RelationManager
{
  protected static string $relationship = 'profitLoss';

  public function isReadOnly(): bool
  {
    return false;
  }

  public function form(Form $form): Form
  {
    return ProfitLossResource::form($form);
  }

  public function table(Table $table): Table
  {
    return ProfitLossResource::table($table);
  }
}
