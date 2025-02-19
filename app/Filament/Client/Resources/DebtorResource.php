<?php

namespace App\Filament\Client\Resources;

use App\Enums\DebtorStatus;
use App\Filament\Client\Resources\DebtorResource\Pages;
use App\Models\Debtor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DebtorResource extends Resource
{
    protected static ?string $model = Debtor::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Debtors';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('KES')
                                    ->maxValue(42949672.95),

                                Forms\Components\TextInput::make('amount_paid')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->maxValue(42949672.95)
                                    ->default(0),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Payment Due Date')
                                    ->default(now()->addDays(30)),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('KES')
                    ->sortable(),

//                Tables\Columns\ProgressColumn::make('payment_progress')
//                    ->label('Progress')
//                    ->color(fn (float $state): string => match (true) {
//                        $state === 0 => 'danger',
//                        $state === 100 => 'success',
//                        default => 'warning',
//                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (DebtorStatus $state) => $state->label())
                    ->color(fn (DebtorStatus $state) => $state->color())
                    ->icon(fn (DebtorStatus $state) => $state->icon())
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn (?string $state) =>
                    $state && now()->greaterThan($state) ? 'danger' : 'success'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partially Paid',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\TrashedFilter::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDebtors::route('/'),
            'create' => Pages\CreateDebtor::route('/create'),
            'edit' => Pages\EditDebtor::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereHas('business.users', function (Builder $query) {
                $query->where('users.id', auth()->id());
            });
    }
}
