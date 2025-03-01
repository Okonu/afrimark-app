<x-filament::section>
    <h3 class="text-base font-semibold text-gray-900 mb-4">Business Documents</h3>

    <div class="space-y-4">
        @if($this->business->documents->isEmpty())
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-500">No business documents uploaded yet.</p>
                <div class="mt-3">
                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.client.auth.document-upload') }}"
                        color="primary"
                        size="sm"
                    >
                        Upload Documents
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="overflow-hidden border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->business->documents as $document)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $document->type->name ?? $document->type }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if($document->verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Verified
                                        </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            {{ $document->status }}
                                        </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $document->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                <a href="{{ Storage::url($document->file_path) }}"
                                   target="_blank"
                                   class="text-primary-600 hover:text-primary-900">
                                    View
                                </a>

                                <a href="{{ route('filament.client.resources.business-documents.edit', ['record' => $document->id]) }}"
                                   class="ml-3 text-primary-600 hover:text-primary-900">
                                    Replace
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-right">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.client.auth.document-upload') }}"
                    color="gray"
                    size="sm"
                >
                    Manage Documents
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament::section>
