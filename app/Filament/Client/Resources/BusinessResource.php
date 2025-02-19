<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\BusinessResource\Pages;
use App\Filament\Client\Resources\BusinessResource\Widgets;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'My Business';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business Name')
                    ->size('lg')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Registration Number')
                    ->icon('heroicon-m-identification'),

                Tables\Columns\TextColumn::make('phone')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Business Details')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Business Name')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('registration_number')
                                    ->label('Registration Number')
                                    ->icon('heroicon-m-identification'),

                                TextEntry::make('email')
                                    ->icon('heroicon-m-envelope'),

                                TextEntry::make('phone')
                                    ->icon('heroicon-m-phone'),

                                TextEntry::make('address')
                                    ->icon('heroicon-m-map-pin')
                                    ->columnSpanFull(),

                                TextEntry::make('created_at')
                                    ->label('Member Since')
                                    ->dateTime('d M Y')
                                    ->icon('heroicon-m-calendar'),
                            ])
                            ->columnSpan(2),

                        Group::make()
                            ->schema([
                                Section::make('Team Summary')
                                    ->icon('heroicon-o-users')
                                    ->schema([
                                        TextEntry::make('users_count')
                                            ->label('Total Members')
                                            ->state(fn (Business $record): int => $record->users()->count()),
                                    ]),

                                Section::make('Documents')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        TextEntry::make('verified_docs')
                                            ->label('Verified Documents')
                                            ->state(fn (Business $record): int => $record->documents()->where('status', 'verified')->count())
                                            ->color('success'),

                                        TextEntry::make('pending_docs')
                                            ->label('Pending Verification')
                                            ->state(fn (Business $record): int => $record->documents()->where('status', 'pending')->count())
                                            ->color('warning'),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'view' => Pages\ViewBusiness::route('/{record}'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\BusinessStatsOverview::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereHas('users', function (Builder $query) {
                $query->where('users.id', auth()->id());
            });
    }
}
