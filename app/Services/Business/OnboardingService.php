<?php

namespace App\Services\Business;

use App\Models\Business;

class OnboardingService
{
    /**
     * Get business onboarding progress
     *
     * @param \App\Models\Business $business
     * @return array
     */
    public function getBusinessProgress(Business $business)
    {
        $steps = [
            'contact_info' => true,
            'business_info' => (bool) $business->name,
            'email_verified' => (bool) $business->email_verified_at,
            'documents_uploaded' => $this->checkDocumentsUploaded($business),
            'debtors_added' => $this->hasDebtorsWithDocuments($business)
        ];

        $completedSteps = count(array_filter($steps));
        $totalSteps = count($steps);

        return [
            'steps' => $steps,
            'percentage' => ($completedSteps / $totalSteps) * 100,
            'next_step' => $this->determineNextStep($steps)
        ];
    }

    /**
     * Check if business has uploaded all required documents
     *
     * @param \App\Models\Business $business
     * @return bool
     */
    protected function checkDocumentsUploaded(Business $business)
    {
        $requiredDocumentTypes = [
            'certificate_of_incorporation',
            'tax_pin',
            'cr12_cr13'
        ];

        $uploadedDocumentTypes = $business->documents()
            ->whereIn('type', $requiredDocumentTypes)
            ->get()
            ->map(function ($document) {
                return $document->type instanceof \App\Enums\DocumentType
                    ? $document->type->value
                    : $document->type;
            })
            ->toArray();

        return count(array_intersect($requiredDocumentTypes, $uploadedDocumentTypes)) === count($requiredDocumentTypes);
    }

    /**
     * Check if business has debtors with supporting documents
     *
     * @param \App\Models\Business $business
     * @return bool
     */
    protected function hasDebtorsWithDocuments(Business $business)
    {
        return $business->debtors()
                ->whereHas('documents')
                ->count() > 0;
    }

    /**
     * Determine the next step in the onboarding process
     *
     * @param array $steps
     * @return string|null
     */
    protected function determineNextStep(array $steps)
    {
        if (!$steps['business_info']) {
            return 'business_info';
        }

        if (!$steps['email_verified']) {
            return 'email_verification';
        }

        if (!$steps['documents_uploaded']) {
            return 'document_upload';
        }

        if (!$steps['debtors_added']) {
            return 'add_debtors';
        }

        return null; // All steps completed
    }

    /**
     * Get URL for the next onboarding step
     *
     * @param string $step
     * @return string
     */
    public function getNextStepUrl($step)
    {
        $urls = [
            'business_info' => route('filament.client.auth.business-information'),
            'email_verification' => route('filament.client.auth.email-verification'),
            'document_upload' => route('filament.client.auth.document-upload'),
            'add_debtors' => route('filament.client.resources.debtors.create'),
        ];

        return $urls[$step] ?? route('filament.client.pages.dashboard');
    }
}
