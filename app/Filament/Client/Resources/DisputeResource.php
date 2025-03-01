<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\DisputeResource\Pages;
use App\Models\Dispute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DisputeResource extends Resource
{
    protected static ?string $model = Dispute::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Disputes';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $businessId = Auth::user()->businesses()->first()?->id;

        return parent::getEloquentQuery()
            ->where(function ($query) use ($businessId) {
                $query->where('business_id', $businessId)
                    ->orWhereHas('debtor', function ($q) use ($businessId) {
                        $q->where('business_id', $businessId);
                    });
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('debtor_id')
                    ->required(),

                Forms\Components\Section::make('Dispute Information')
                    ->schema([
                        Forms\Components\Select::make('dispute_type')
                            ->label('Nature of Dispute')
                            ->options([
                                'wrong_amount' => 'Wrong Amount',
                                'no_debt' => 'No Debt Exists',
                                'already_paid' => 'Already Paid',
                                'wrong_business' => 'Wrong Business Listed',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Explanation')
                            ->required()
                            ->maxLength(1000),
                    ]),

                Forms\Components\Section::make('Supporting Documents')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->label('Supporting Documents')
                            ->helperText('Upload proof of payment, corrected invoices, or any other relevant documents')
                            ->multiple()
                            ->directory('dispute-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->required(),
                    ]),

                Forms\Components\Section::make('Terms & Conditions')
                    ->schema([
                        Forms\Components\Checkbox::make('liability_confirmation')
                            ->label('I confirm that all the information provided is accurate and I bear full liability for its correctness')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('debtor.name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Listed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dispute_type')
                    ->label('Nature of Dispute')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'wrong_amount' => 'Wrong Amount',
                        'no_debt' => 'No Debt Exists',
                        'already_paid' => 'Already Paid',
                        'wrong_business' => 'Wrong Business Listed',
                        'other' => 'Other',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'under_review',
                        'success' => 'resolved_approved',
                        'danger' => 'resolved_rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dispute Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'under_review' => 'Under Review',
                        'resolved_approved' => 'Resolved (Approved)',
                        'resolved_rejected' => 'Resolved (Rejected)',
                    ]),

                Tables\Filters\SelectFilter::make('dispute_type')
                    ->options([
                        'wrong_amount' => 'Wrong Amount',
                        'no_debt' => 'No Debt Exists',
                        'already_paid' => 'Already Paid',
                        'wrong_business' => 'Wrong Business Listed',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('respond')
                    ->label('Respond')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('primary')
                    ->url(fn (Dispute $record): string => route('filament.client.resources.disputes.respond', ['record' => $record->id]))
                    ->visible(fn (Dispute $record): bool =>
                        $record->business_id === Auth::user()->businesses()->first()?->id &&
                        $record->status === 'pending'
                    ),
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
            'index' => Pages\ListDisputes::route('/'),
            'create' => Pages\CreateDispute::route('/create'),
            'view' => Pages\ViewDispute::route('/{record}'),
//            'respond' => Pages\RespondToDispute::route('/{record}/respond'),
        ];
    }
}
