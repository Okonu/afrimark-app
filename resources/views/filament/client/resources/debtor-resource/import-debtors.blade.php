<x-filament-panels::page>
    <x-filament::section>
        <div class="px-4 py-6">
            <div class="mb-8">
                <h2 class="text-xl font-bold">Import Debtors</h2>
                <p class="text-gray-500">
                    Upload an Excel or CSV file with your debtor information. Download the template to ensure your file is in the correct format.
                </p>
            </div>

            <form wire:submit="submit">
                <div class="bg-white px-6 py-4 rounded-lg border border-gray-200">
                    {{ $this->form }}
                </div>

                <div class="mt-8">
                    <div class="bg-blue-50 px-6 py-4 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800">Important Notes</h3>
                        <ul class="list-disc list-inside text-blue-700 mt-2">
                            <li>Upload an Excel (.xlsx) or CSV file containing your debtor data.</li>
                            <li>Ensure your file contains the required columns: name, kra_pin, email, and amount_owed.</li>
                            <li>After import, you'll need to add supporting documents for each debtor separately.</li>
                            <li>Each debtor will remain in a pending state for 7 days before being publicly listed.</li>
                            <li>Debtors will receive an email notification about the listing.</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit" color="primary">
                        Import Debtors
                    </x-filament::button>
                </div>
            </form>
        </div>
    </x-filament::section>
</x-filament-panels::page>
