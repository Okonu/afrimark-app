<?php

namespace App\Traits;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\BusinessDocument;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasDocuments
{
    public function getDocumentByType(DocumentType $type): ?BusinessDocument
    {
        return $this->documents()
            ->where('type', $type->value)
            ->first();
    }

    public function hasVerifiedDocument(DocumentType $type): bool
    {
        return $this->documents()
            ->where('type', $type->value)
            ->where('status', DocumentStatus::VERIFIED->value)
            ->exists();
    }

    public function getRequiredDocumentTypes(): array
    {
        return array_filter(
            DocumentType::cases(),
            fn (DocumentType $type) => $type->isRequired()
        );
    }

    public function hasAllRequiredDocuments(): bool
    {
        foreach ($this->getRequiredDocumentTypes() as $type) {
            if (!$this->hasVerifiedDocument($type)) {
                return false;
            }
        }

        return true;
    }

    public function getMissingRequiredDocuments(): array
    {
        return array_filter(
            $this->getRequiredDocumentTypes(),
            fn (DocumentType $type) => !$this->hasVerifiedDocument($type)
        );
    }

    public function getPendingDocuments(): array
    {
        return $this->documents()
            ->where('status', DocumentStatus::PENDING->value)
            ->get()
            ->all();
    }

    public function getVerifiedDocuments(): array
    {
        return $this->documents()
            ->where('status', DocumentStatus::VERIFIED->value)
            ->get()
            ->all();
    }

    public function getRejectedDocuments(): array
    {
        return $this->documents()
            ->where('status', DocumentStatus::REJECTED->value)
            ->get()
            ->all();
    }
}
