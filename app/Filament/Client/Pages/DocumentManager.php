<?php

namespace App\Filament\Client\Pages;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Business;
use App\Models\BusinessDocument;
use App\Models\Debtor;
use App\Models\DebtorDocument;
use App\Models\DisputeDocument;
use App\Services\DocumentProcessingService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class DocumentManager extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Documents';
    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Document Management';
    protected static ?string $navigationGroup = 'Records';

    protected static string $view = 'filament.client.pages.document-manager';

    public $document;
    public $category;
    public $debtorId = null;
    public $businessId = null;
    public $documentType = null;
    public $documentName = null;
    public $activeTab = 'business-documents';
    public bool $isUploadModalOpen = false;

    // Store the data collections
    public $businessDocuments = [];
    public $debtorDocuments = [];
    public $disputeDocuments = [];
    public $debtors = [];

    // For document details
    public $selectedDocument = null;
    public $isViewModalOpen = false;
    public $processingResult = null;

    protected $queryString = ['activeTab'];

    protected $listeners = ['openUploadModal', 'closeUploadModal'];

    public function mount(): void
    {
        $this->activeTab = request()->query('tab', 'business-documents');
        $this->form->fill();
        $this->loadData();
    }

    public function form(Form $form): Form
    {
        $business = Auth::user()->businesses()->first();
        $this->businessId = $business?->id;

        $debtorOptions = [];
        if ($business) {
            $this->debtors = Debtor::whereHas('businesses', function ($query) use ($business) {
                $query->where('businesses.id', $business->id);
            })->get();

            foreach ($this->debtors as $debtor) {
                $debtorOptions[$debtor->id] = $debtor->name;
            }
        }

        // Get document types from enum
        $businessDocumentTypes = [
            DocumentType::CERTIFICATE_OF_INCORPORATION->value => DocumentType::CERTIFICATE_OF_INCORPORATION->label(),
            DocumentType::TAX_PIN->value => DocumentType::TAX_PIN->label(),
            DocumentType::CR12_CR13->value => DocumentType::CR12_CR13->label(),
            DocumentType::REGISTRATION->value => DocumentType::REGISTRATION->label(),
            DocumentType::TAX->value => DocumentType::TAX->label(),
            DocumentType::LICENSE->value => DocumentType::LICENSE->label(),
            'other' => 'Other Document',
        ];

        $debtorDocumentTypes = [
            'invoice' => 'Invoice',
            'contract' => 'Contract',
            'payment_proof' => 'Payment Proof',
            'statement' => 'Account Statement',
            'other' => 'Other Document',
        ];

        return $form
            ->schema([
                Select::make('category')
                    ->label('Document Category')
                    ->options([
                        'business' => 'Business Document',
                        'debtor' => 'Debtor Document',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function () {
                        $this->debtorId = null;
                        $this->documentType = null;
                    }),

                Select::make('debtorId')
                    ->label('Select Debtor')
                    ->options($debtorOptions)
                    ->searchable()
                    ->required()
                    ->visible(fn ($get) => $get('category') === 'debtor'),

                Select::make('documentType')
                    ->label('Document Type')
                    ->options(function ($get) use ($businessDocumentTypes, $debtorDocumentTypes) {
                        if ($get('category') === 'business') {
                            return $businessDocumentTypes;
                        } elseif ($get('category') === 'debtor') {
                            return $debtorDocumentTypes;
                        }
                        return [];
                    })
                    ->helperText(function ($get) {
                        if ($get('documentType')) {
                            // Get description if available in the enum
                            if ($get('category') === 'business') {
                                try {
                                    $type = DocumentType::from($get('documentType'));
                                    return $type->description();
                                } catch (\ValueError $e) {
                                    return 'Other business-related document';
                                }
                            } elseif ($get('category') === 'debtor' && $get('documentType') === 'invoice') {
                                return 'Invoice sent to the debtor';
                            } elseif ($get('category') === 'debtor' && $get('documentType') === 'contract') {
                                return 'Contract or agreement with the debtor';
                            }
                        }
                        return 'Select a document type';
                    })
                    ->required(),

                TextInput::make('documentName')
                    ->label('Document Name')
                    ->placeholder('Enter a descriptive name for this document')
                    ->required(),

                FileUpload::make('document')
                    ->label('Upload Document')
                    ->acceptedFileTypes(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->maxSize(5120) // 5MB
                    ->required()
                    ->helperText('Upload PDF, Word, or Excel files (max 5MB)')
            ]);
    }

    /**
     * Load all required data for the current tab
     */
    public function loadData(): void
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            Log::warning("No business found for user", ['user_id' => Auth::id()]);
            return;
        }

        Log::info("Loading document data for business: " . $business->name);

        // Load business documents
        $this->businessDocuments = BusinessDocument::where('business_id', $business->id)
            ->latest()
            ->get();

        // Load debtor documents
        $this->debtorDocuments = DebtorDocument::whereHas('debtor.businesses', function ($query) use ($business) {
            $query->where('businesses.id', $business->id);
        })
            ->latest()
            ->get();

        // Load dispute documents
        $this->disputeDocuments = DisputeDocument::whereHas('dispute.debtor.businesses', function ($query) use ($business) {
            $query->where('businesses.id', $business->id);
        })
            ->latest()
            ->get();

        Log::info("Loaded document counts: business={$this->businessDocuments->count()}, debtor={$this->debtorDocuments->count()}, dispute={$this->disputeDocuments->count()}");
    }

    /**
     * Open the upload modal
     */
    public function openUploadModal(): void
    {
        $this->resetForm();
        $this->isUploadModalOpen = true;

        Notification::make()
            ->title('Debug: Modal Status')
            ->body('Upload modal should be open now. isUploadModalOpen = ' . ($this->isUploadModalOpen ? 'true' : 'false'))
            ->info()
            ->send();

        $this->dispatch('open-modal', id: 'upload-document-modal');
    }

    /**
     * Close the upload modal
     */
    public function closeUploadModal(): void
    {
        $this->isUploadModalOpen = false;
        $this->resetForm();
    }

    /**
     * Reset the form fields
     */
    private function resetForm(): void
    {
        $this->reset(['category', 'debtorId', 'documentType', 'documentName', 'document']);
        $this->form->fill();
    }

    /**
     * Submit the document upload form
     */
    public function submit(): void
    {
        Notification::make()
            ->title('Debug: Submit Clicked')
            ->body('Form submission started')
            ->info()
            ->send();

        $data = $this->form->getState();

        $business = Auth::user()->businesses()->first();

        if (!$business) {
            Notification::make()
                ->title('Error')
                ->body('No business profile found for your account.')
                ->danger()
                ->send();
            return;
        }

        $file = $data['document'];

        // Check if $file is already a string (path) or a file upload object
        if (is_string($file)) {
            $originalFilename = basename($file);
            $path = $file;
        } else {
            // It's a file upload object
            $originalFilename = $file->getClientOriginalName();
            $path = $file->store('documents/' . $business->id);
        }

        $documentName = $data['documentName'];

        try {
            if ($data['category'] === 'business') {
                try {
                    // Check if the document type exists in the enum
                    $documentType = DocumentType::from($data['documentType']);

                    $document = BusinessDocument::create([
                        'business_id' => $business->id,
                        'type' => $documentType,
                        'file_path' => $path,
                        'original_filename' => $originalFilename,
                        'status' => DocumentStatus::PENDING,
                    ]);
                } catch (\ValueError $e) {
                    // If not in enum, store as string
                    $document = BusinessDocument::create([
                        'business_id' => $business->id,
                        'type' => $data['documentType'],
                        'file_path' => $path,
                        'original_filename' => $originalFilename,
                        'status' => DocumentStatus::PENDING,
                    ]);
                }
            } elseif ($data['category'] === 'debtor') {
                $document = DebtorDocument::create([
                    'debtor_id' => $data['debtorId'],
                    'type' => $data['documentType'],
                    'file_path' => $path,
                    'original_filename' => $originalFilename,
                    'uploaded_by' => Auth::id(),
                ]);
            }

            Notification::make()
                ->title('Document Uploaded')
                ->body('Your document has been uploaded and will be processed shortly.')
                ->success()
                ->send();

            $this->closeUploadModal();
            $this->loadData();
        } catch (\Exception $e) {
            Log::error("Error uploading document: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'business_id' => $business->id,
                'exception' => $e
            ]);

            Notification::make()
                ->title('Error')
                ->body('There was an error uploading your document: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * View document details
     */
    public function viewDocument($documentId, $type): void
    {
        $this->selectedDocument = null;
        $this->processingResult = null;

        if ($type === 'business') {
            $this->selectedDocument = BusinessDocument::findOrFail($documentId);
        } elseif ($type === 'debtor') {
            $this->selectedDocument = DebtorDocument::findOrFail($documentId);
        } elseif ($type === 'dispute') {
            $this->selectedDocument = DisputeDocument::findOrFail($documentId);
        }

        if ($this->selectedDocument && $this->selectedDocument->isProcessed()) {
            $this->processingResult = $this->selectedDocument->getExtractedData();
        }

        $this->isViewModalOpen = true;
    }

    /**
     * Close document details modal
     */
    public function closeViewModal(): void
    {
        $this->isViewModalOpen = false;
        $this->selectedDocument = null;
        $this->processingResult = null;
    }

    /**
     * Download a document
     */
    public function downloadDocument($documentId, $type)
    {
        try {
            if ($type === 'business') {
                $document = BusinessDocument::findOrFail($documentId);
            } elseif ($type === 'debtor') {
                $document = DebtorDocument::findOrFail($documentId);
            } elseif ($type === 'dispute') {
                $document = DisputeDocument::findOrFail($documentId);
            } else {
                Notification::make()
                    ->title('Error')
                    ->body('Invalid document type specified.')
                    ->danger()
                    ->send();
                return;
            }

            if (!Storage::exists($document->file_path)) {
                Notification::make()
                    ->title('Error')
                    ->body('Document file not found.')
                    ->danger()
                    ->send();
                return;
            }

            $filename = $document->original_filename ?? basename($document->file_path);

            return response()->streamDownload(function () use ($document) {
                echo Storage::get($document->file_path);
            }, $filename);
        } catch (\Exception $e) {
            Log::error('Error downloading document: ' . $e->getMessage(), [
                'document_id' => $documentId,
                'type' => $type,
                'exception' => $e
            ]);

            Notification::make()
                ->title('Error')
                ->body('Failed to download document: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Reprocess a document
     */
    public function reprocessDocument($documentId, $type): void
    {
        if ($type === 'business') {
            $document = BusinessDocument::findOrFail($documentId);
        } elseif ($type === 'debtor') {
            $document = DebtorDocument::findOrFail($documentId);
        } elseif ($type === 'dispute') {
            $document = DisputeDocument::findOrFail($documentId);
        } else {
            return;
        }

        if ($document->queueForProcessing()) {
            Notification::make()
                ->title('Document Queued')
                ->body('Your document has been queued for reprocessing.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error')
                ->body('Unable to queue document for reprocessing.')
                ->danger()
                ->send();
        }
    }

    /**
     * Format processing status for display with appropriate badge color
     */
    public function formatProcessingStatus($status): array
    {
        $colorMap = [
            'queued' => 'primary',
            'processing' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
        ];

        $labelMap = [
            'queued' => 'Queued',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
        ];

        return [
            'color' => $colorMap[$status] ?? 'secondary',
            'label' => $labelMap[$status] ?? ucfirst($status),
        ];
    }

    /**
     * Format document type for display
     */
    public function formatDocumentType($type): string
    {
        // If it's an enum value, use its label
        if (is_string($type)) {
            try {
                $documentType = DocumentType::from($type);
                return $documentType->label();
            } catch (\ValueError $e) {
                // Not an enum value, continue to manual mapping
            }
        }

        // Manual mapping for non-enum types
        $types = [
            'invoice' => 'Invoice',
            'contract' => 'Contract',
            'registration' => 'Registration Certificate',
            'identification' => 'Business Identification',
            'financial' => 'Financial Statement',
            'tax' => 'Tax Document',
            'payment_proof' => 'Payment Proof',
            'statement' => 'Account Statement',
            'other' => 'Other Document',
        ];

        if (is_string($type) && isset($types[$type])) {
            return $types[$type];
        }

        // Fall back to a human-readable version of the value
        if (is_string($type)) {
            return ucwords(str_replace('_', ' ', $type));
        }

        // Last resort
        return 'Unknown Document Type';
    }

    /**
     * Format date
     */
    public function formatDate($date): string
    {
        if (!$date) return 'N/A';
        return $date->format('M d, Y H:i');
    }

    /**
     * Get file icon based on file extension
     */
    public function getFileIcon($filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return match(strtolower($extension)) {
            'pdf' => 'heroicon-o-document-text',
            'doc', 'docx' => 'heroicon-o-document',
            'xls', 'xlsx' => 'heroicon-o-table-cells',
            default => 'heroicon-o-document',
        };
    }

    /**
     * Get document list visibility status
     */
    public function hasDocuments(): bool
    {
        if ($this->activeTab === 'business-documents') {
            return $this->businessDocuments->isNotEmpty();
        } elseif ($this->activeTab === 'debtor-documents') {
            return $this->debtorDocuments->isNotEmpty();
        } elseif ($this->activeTab === 'dispute-documents') {
            return $this->disputeDocuments->isNotEmpty();
        }

        return false;
    }

    /**
     * Get current document collection count
     */
    public function getDocumentCount(): int
    {
        if ($this->activeTab === 'business-documents') {
            return $this->businessDocuments->count();
        } elseif ($this->activeTab === 'debtor-documents') {
            return $this->debtorDocuments->count();
        } elseif ($this->activeTab === 'dispute-documents') {
            return $this->disputeDocuments->count();
        }

        return 0;
    }
}
