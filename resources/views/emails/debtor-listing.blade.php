<x-mail::message>
    # You Have Been Listed as a Debtor

    Dear Business Owner,

    This is to inform you that your business has been listed as a debtor on the Afrimark Business Portal by:

    **Business Name:** {{ $debtor->business->name }}
    **Outstanding Amount:** {{ number_format($debtor->amount_owed, 2) }} KES
    **Invoice Number:** {{ $debtor->invoice_number ?? 'N/A' }}

    ### Important Notice:
    This listing will become publicly visible in **7 days** unless resolved.

    <x-mail::button :url="route('debtor.dispute', ['id' => $debtor->id])">
        Dispute This Listing
    </x-mail::button>

    If you believe this listing is incorrect, please click the button above to submit a dispute. You will need to create an account or log in to proceed.

    If you have any questions, please contact us directly.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
