<x-filament-panels::page>
    <x-filament::section>
        <div class="px-4 py-6">
            <div class="mb-8">
                <h2 class="text-xl font-bold">Import Payments</h2>
                <p class="text-gray-500">
                    Upload an Excel or CSV file with payment information for your debtors. Download the template to ensure your file is in the correct format.
                </p>
            </div>

            <div class="bg-white px-6 py-4 rounded-lg border border-gray-200">
                {{ $this->form }}
            </div>

            <div class="mt-8">
                <div class="bg-blue-50 px-6 py-4 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800">Important Notes</h3>
                    <ul class="list-disc list-inside text-blue-700 mt-2">
                        <li>Upload an Excel (.xlsx) or CSV file containing your payment data.</li>
                        <li>Ensure your file contains the required columns: kra_pin and amount.</li>
                        <li>KRA PIN must match exactly with existing debtors in your account.</li>
                        <li>Payment date should be in YYYY-MM-DD format (defaults to today if not provided).</li>
                        <li>Payment status will be automatically updated based on the remaining balance.</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
