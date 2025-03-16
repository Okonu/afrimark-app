<x-filament-panels::page>
    <x-filament::section>
        <div class="px-4 py-6">
            <div class="mb-8">
                <h2 class="text-xl font-bold">Import Invoices</h2>
                <p class="text-gray-500">
                    Upload an Excel or CSV file with your invoice information. Download the template to ensure your file is in the correct format.
                </p>
            </div>

            <div class="bg-white px-6 py-4 rounded-lg border border-gray-200">
                {{ $this->form }}
            </div>

            <div class="mt-8">
                <div class="bg-blue-50 px-6 py-4 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800">Important Notes</h3>
                    <ul class="list-disc list-inside text-blue-700 mt-2">
                        <li>Upload an Excel (.xlsx) or CSV file containing your invoice data.</li>
                        <li>Ensure your file contains the required columns: debtor_kra_pin, invoice_number, invoice_date, due_date, invoice_amount.</li>
                        <li>For new debtors, include debtor_name and debtor_email columns.</li>
                        <li>Use the YYYY-MM-DD format for dates (e.g., 2025-03-15).</li>
                        <li>All monetary values should be in KES without the currency symbol.</li>
                        <li>The system will automatically calculate payment terms, days overdue, and DBT ratio.</li>
                        <li>If due_amount is not provided, it will default to the invoice_amount (indicating nothing has been paid yet).</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
