<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\BkashV2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    public function __construct(private readonly BkashV2Service $bkash)
    {
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:10',
        ]);

        try {
            // Create donation record
            $donation = Donation::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'amount' => $validated['amount'],
                'status' => 'pending',
            ]);

            // Store donation ID in session
            $request->session()->put('donation_id', $donation->id);

            // Initialize bKash payment
            $invoice = 'NOVA-' . time() . '-' . $donation->id;

            $response = $this->bkash->createPayment([
                'amount' => $validated['amount'],
                'merchant_invoice_number' => $invoice,
                'callback_url' => route('bkash.callback'),
                'payer_reference' => $validated['phone'],
            ]);

            if (isset($response['bkashURL'])) {
                // Update donation with bKash payment ID
                $donation->update([
                    'bkash_payment_id' => $response['paymentID'] ?? $response['paymentId'] ?? null,
                ]);

                return redirect()->away($response['bkashURL']);
            }

            // If payment creation failed
            Log::error('bKash payment creation failed', ['response' => $response]);
            $donation->update(['status' => 'failed']);

            return redirect()->route('landing')
                ->withErrors(['error' => 'Payment initialization failed. Please try again.']);

        } catch (\Exception $e) {
            Log::error('Donation creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($donation)) {
                $donation->update(['status' => 'failed']);
            }

            return redirect()->route('landing')
                ->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }
}
