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
        $paymentID = (string) $request->query('paymentID', '');
        $status = strtolower((string) $request->query('status', ''));

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

            if ($paymentID === '') {
                Log::warning('Invalid callback: missing paymentID', [
                    'donation_id' => $donation->id,
                ]);

                $donation->update(['status' => 'failed']);

                return redirect()->route('landing')
                    ->withErrors(['error' => 'Invalid payment callback.']);
            }

            if (! empty($donation->bkash_payment_id) && $donation->bkash_payment_id !== $paymentID) {
                Log::warning('Payment ID mismatch on callback', [
                    'donation_id' => $donation->id,
                    'stored_payment_id_suffix' => substr((string) $donation->bkash_payment_id, -8),
                    'callback_payment_id_suffix' => substr($paymentID, -8),
                ]);

                $donation->update(['status' => 'failed']);

                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment verification failed.']);
            }

            if ($donation->status === 'success') {
                return redirect()->route('thank-you');
            }

            // Check if payment was cancelled
            if ($status === 'cancel' || $status === 'failure') {
                $donation->update(['status' => 'failed']);
                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment was cancelled or failed.']);
            }

            // Execute payment
            if ($status === 'success') {
                $response = $this->bkash->executePayment($paymentID);
                $responsePaymentId = (string) ($response['paymentID'] ?? $response['paymentId'] ?? '');
                $responseAmount = number_format((float) ($response['amount'] ?? 0), 2, '.', '');
                $donationAmount = number_format((float) $donation->amount, 2, '.', '');

                // Verify payment was successful
                if (
                    isset($response['transactionStatus'])
                    && strtolower((string) $response['transactionStatus']) === 'completed'
                    && $responsePaymentId === $paymentID
                    && $responseAmount === $donationAmount
                ) {
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
                Log::error('Payment execution failed', [
                    'donation_id' => $donation->id,
                    'payment_id_suffix' => substr($paymentID, -8),
                    'transaction_status' => $response['transactionStatus'] ?? null,
                    'statusCode' => $response['statusCode'] ?? null,
                ]);
                $donation->update(['status' => 'failed']);

                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment verification failed.']);
            }

            // Invalid callback
            Log::warning('Invalid callback status', [
                'donation_id' => $donation->id,
                'payment_id_suffix' => substr($paymentID, -8),
                'status' => $status,
            ]);
            $donation->update(['status' => 'failed']);

            return redirect()->route('landing')
                ->withErrors(['error' => 'Invalid payment callback.']);

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'donation_id' => $donation->id ?? null,
                'payment_id_suffix' => $paymentID !== '' ? substr($paymentID, -8) : null,
                'error' => $e->getMessage(),
            ]);

            if (isset($donation)) {
                $donation->update(['status' => 'failed']);
            }

            return redirect()->route('landing')
                ->withErrors(['error' => 'An error occurred processing your payment.']);
        }
    }
}
