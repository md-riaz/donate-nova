<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function thankYou(Request $request)
    {
        $donationId = $request->session()->get('donation_id');
        $paymentData = $request->session()->get('payment');
        $donation = null;

        if ($donationId) {
            $donation = Donation::find($donationId);

            // If we have payment data from bKash callback, update the donation
            if ($donation && $paymentData) {
                $donation->update([
                    'status' => 'success',
                    'transaction_id' => $paymentData['trxID'] ?? null,
                    'bkash_payment_id' => $paymentData['paymentID'] ?? $donation->bkash_payment_id,
                ]);
            }

            // Clear the session
            $request->session()->forget('donation_id');
            $request->session()->forget('payment');
        }

        return view('thank-you', compact('donation'));
    }
}
