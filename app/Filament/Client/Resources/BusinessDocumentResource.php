<?php

namespace App\Filament\Client\Resources;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Filament\Client\Resources\BusinessDocumentResource\Pages;
use App\Models\BusinessDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessDocumentResource extends Resource
{
    protected static ?string $model = BusinessDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Documents';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'original_filename';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload Document')
                    ->description('Upload a new document for your business')
                    ->schema([
                        Select::make('type')
                            ->label('Document Type')
                            ->options(collect(DocumentType::cases())->mapWithKeys(fn ($type) => [
                                $type->value => [
                                    'label' => $type->label(),
                                    'description' => $type->description(),
                                ]
                            ]))
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label('Document')
                            ->required()
                            ->disk('public')
                            ->directory(fn ($record) => 'business-documents/' . ($record?->type?->value ?? 'general'))
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->previewable(false)
                            ->columnSpanFull()
                            ->helperText('Upload PDF files only. Maximum size: 10MB'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'Unknown')
                    ->color(fn ($state) => match ($state) {
                        DocumentType::REGISTRATION => 'success',
                        DocumentType::TAX => 'info',
                        DocumentType::LICENSE => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('original_filename')
                    ->label('File Name')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'Pending')
                    ->color(fn ($state) => $state?->color() ?? 'gray')
                    ->icon(fn ($state) => $state?->icon() ?? 'heroicon-o-clock')
                    ->sortable(),

                IconColumn::make('file_path')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn ($record) => $record && $record->file_path
                        ? Storage::disk('public')->url($record->file_path)
                        : null
                    )
                    ->openUrlInNewTab(),

                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not verified'),

                TextColumn::make('verifiedBy.name')
                    ->label('Verified By')
                    ->visible(fn ($record) => $record && $record->verified_at && $record->verifiedBy)
                    ->placeholder('Not verified'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(DocumentType::cases())->mapWithKeys(fn ($type) => [
                        $type->value => $type->label()
                    ])),
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(fn ($status) => [
                        $status->value => $status->label()
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn ($record) => "Document Details: {$record->original_filename}")
                    ->modalDescription(fn ($record) => $record->type?->description() ?? ''),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record && $record->isPending()),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No documents yet')
            ->emptyStateDescription('Upload your business documents here')
            ->emptyStateIcon('heroicon-o-document-plus');
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
            'index' => Pages\ListBusinessDocuments::route('/'),
            'create' => Pages\CreateBusinessDocument::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('business.users', function (Builder $query) {
                $query->where('users.id', auth()->id());
            });
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->where('status', DocumentStatus::PENDING)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getEloquentQuery()
            ->where('status', DocumentStatus::PENDING)
            ->exists() ? 'warning' : 'success';
    }
}
