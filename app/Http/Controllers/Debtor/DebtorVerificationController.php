<?php

namespace App\Http\Controllers\Debtor;

use App\Http\Controllers\Controller;
use App\Models\Debtor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DebtorVerificationController extends Controller
{
    /**
     * Verify debtor token and redirect to appropriate page
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $debtorId = $request->query('debtor_id');
        $token = $request->query('token');

        if (!$debtorId || !$token) {
            return redirect()->route('filament.client.auth.login')
                ->with('error', 'Invalid verification link.');
        }

        $debtor = Debtor::find($debtorId);

        if (!$debtor || !$debtor->validateToken($token)) {
            return redirect()->route('filament.client.auth.login')
                ->with('error', 'Invalid or expired verification link.');
        }

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

        return redirect()->route('filament.client.auth.register');
    }
}
