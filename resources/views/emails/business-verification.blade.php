<x-mail::message>
    # Verify Your Business Email

    Hello,

    Thank you for registering your business on the Afrimark Business Portal. To complete your registration and ensure the security of your account, please verify your email address by clicking the button below:

    <x-mail::button :url="route('business.verify', ['token' => $token])">
        Verify Business Email
    </x-mail::button>

    This verification link will expire in 24 hours.

    If you did not register your business on our platform, please ignore this email.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
