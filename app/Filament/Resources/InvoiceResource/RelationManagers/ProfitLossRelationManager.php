<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProfitLossResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProfitLossRelationManager extends RelationManager
{
  protected static string $relationship = 'profitLoss';

  public function form(Form $form): Form
  {
    return ProfitLossResource::form($form);
  }

  public function table(Table $table): Table
  {
    return ProfitLossResource::table($table);
  }
}
