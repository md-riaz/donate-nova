<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\BkashV2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BkashController extends Controller
{
    public function __construct(private readonly BkashV2Service $bkash)
    {
    }

    public function callback(Request $request)
    {
        $paymentID = $request->query('paymentID');
        $status = $request->query('status');

        try {
            // Get donation from session
            $donationId = $request->session()->get('donation_id');

            if (!$donationId) {
                Log::error('Donation ID not found in session');
                return redirect()->route('landing')
                    ->withErrors(['error' => 'Session expired. Please try again.']);
            }

            $donation = Donation::find($donationId);

            if (!$donation) {
                Log::error('Donation not found', ['id' => $donationId]);
                return redirect()->route('landing')
                    ->withErrors(['error' => 'Donation not found.']);
            }

            // Check if payment was cancelled
            if ($status === 'cancel' || $status === 'failure') {
                $donation->update(['status' => 'failed']);
                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment was cancelled or failed.']);
            }

            // Execute payment
            if ($paymentID && $status === 'success') {
                $response = $this->bkash->executePayment($paymentID);

                // Verify payment was successful
                if (isset($response['transactionStatus']) && $response['transactionStatus'] === 'Completed') {
                    // Update donation status
                    $donation->update([
                        'status' => 'success',
                        'transaction_id' => $response['trxID'] ?? $response['trxId'] ?? null,
                        'bkash_payment_id' => $paymentID,
                    ]);

                    Log::info('Payment successful', [
                        'donation_id' => $donation->id,
                        'transaction_id' => $response['trxID'] ?? $response['trxId'] ?? null,
                    ]);

                    // Store donation ID for thank you page
                    $request->session()->put('donation_id', $donation->id);

                    return redirect()->route('thank-you');
                }

                // Payment execution failed
                Log::error('Payment execution failed', ['response' => $response]);
                $donation->update(['status' => 'failed']);

                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment verification failed.']);
            }

            // Invalid callback
            Log::error('Invalid callback', ['paymentID' => $paymentID, 'status' => $status]);
            $donation->update(['status' => 'failed']);

            return redirect()->route('landing')
                ->withErrors(['error' => 'Invalid payment callback.']);

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($donation)) {
                $donation->update(['status' => 'failed']);
            }

            return redirect()->route('landing')
                ->withErrors(['error' => 'An error occurred processing your payment.']);
        }
    }
}
