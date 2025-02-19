<?php

namespace App\Filament\Client\Resources\RelationManagers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Documents';
    protected static ?string $icon = 'heroicon-o-document';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (DocumentType $state) => $state->label())
                    ->color(fn (DocumentType $state): string => match ($state) {
                        DocumentType::REGISTRATION => 'success',
                        DocumentType::TAX => 'info',
                        DocumentType::LICENSE => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Document Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (DocumentStatus $state) => $state->label())
                    ->color(fn (DocumentStatus $state): string => match ($state) {
                        DocumentStatus::VERIFIED => 'success',
                        DocumentStatus::REJECTED => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not verified'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Uploaded'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
