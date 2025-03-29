<?php

namespace App\Filament\Client\Resources;

use App\Enums\DebtorStatus;
use App\Filament\Client\Resources\DebtorResource\Pages;
use App\Models\Debtor;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DebtorResource extends Resource
{
    protected static ?string $model = Debtor::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Debtors';
    protected static ?string $navigationGroup = 'Records';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $businessId = Auth::user()->businesses()->first()?->id;

        return parent::getEloquentQuery()
            ->whereHas('businesses', function ($query) use ($businessId) {
                $query->where('businesses.id', $businessId);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kra_pin')
                    ->label('KRA PIN')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount_owed')
                    ->label('Amount Owed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                        DebtorStatus::ACTIVE->value => DebtorStatus::ACTIVE->label(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        // Export logic
                    }),
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
            'index' => Pages\ListDebtors::route('/'),
            'create' => Pages\CreateDebtor::route('/create'),
            'import' => Pages\ImportDebtors::route('/import'),
            'import-payments' => Pages\ImportPayments::route('/import-payments'),
            'edit' => Pages\EditDebtor::route('/{record}/edit'),
            'view' => Pages\ViewDebtor::route('/{record}'),
            'payment' => Pages\UpdatePayment::route('/{record}/payment'),
        ];
    }
}
