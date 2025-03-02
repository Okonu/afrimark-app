<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Debtor;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Session;

class Register extends BaseRegister
{
    protected static string $view = 'filament.client.pages.auth.register';

    public function mount(): void
    {
        $debtorId = request()->query('debtor_id');
        $token = request()->query('token');

        if ($debtorId && $token) {
            $debtor = Debtor::find($debtorId);

            if ($debtor && $debtor->validateToken($token)) {
                Session::put('debtor_registration', [
                    'id' => $debtor->id,
                    'name' => $debtor->name,
                    'email' => $debtor->email,
                    'kra_pin' => $debtor->kra_pin,
                    'token' => $token
                ]);

                Session::put('redirect_after_registration', [
                    'route' => 'filament.client.pages.disputes-page-manager',
                    'params' => ['tab' => 'disputable-listings']
                ]);

                $this->redirect(route('filament.client.auth.register'));
                return;
            }
        }

        parent::mount();
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.client.auth.register');
    }
}
