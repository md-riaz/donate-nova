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

            if (! $donationId) {
                Log::error('Donation ID not found in session');
                return redirect()->route('landing')
                    ->withErrors(['error' => 'Session expired. Please try again.']);
            }

            $donation = Donation::find($donationId);

            if (! $donation) {
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

            // Cancel callback URL scenario (user closed bKash page).
            if (in_array($status, ['cancel', 'cancelled'], true)) {
                $donation->update(['status' => 'failed']);

                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment Cancelled by User.']);
            }

            // Failure callback URL scenario (e.g. wrong OTP multiple times).
            if (in_array($status, ['failure', 'failed'], true)) {
                $donation->update(['status' => 'failed']);

                return redirect()->route('landing')
                    ->withErrors(['error' => 'Payment Failed. Invalid OTP or authorization issue. Please try again.']);
            }

            // Execute payment
            if ($status === 'success') {
                $queryResponse = $this->bkash->queryPayment($paymentID);
                $finalResponse = $queryResponse;

                // Execute only when query shows payment is not completed yet.
                if (! $this->isSuccessfulPaymentForDonation($queryResponse, $donation, $paymentID)) {
                    $executeResponse = $this->bkash->executePayment($paymentID);
                    $finalResponse = $executeResponse;

                    $queryAfterExecute = $this->bkash->queryPayment($paymentID);
                    $finalResponse = $queryAfterExecute;
                }

                $trxId = (string) ($finalResponse['trxID'] ?? $finalResponse['trxId'] ?? '');
                if ($trxId !== '') {
                    $searchResponse = $this->bkash->searchTransaction($trxId);

                    if (! empty($searchResponse) && isset($searchResponse['transactionStatus'])) {
                        $finalResponse = $searchResponse;
                    }
                }

                if ($this->isSuccessfulPaymentForDonation($finalResponse, $donation, $paymentID)) {
                    $resolvedTrxId = $finalResponse['trxID'] ?? $finalResponse['trxId'] ?? null;

                    $donation->update([
                        'status' => 'success',
                        'transaction_id' => $resolvedTrxId,
                        'bkash_payment_id' => $paymentID,
                    ]);

                    Log::info('Payment successful', [
                        'donation_id' => $donation->id,
                        'transaction_id' => $resolvedTrxId,
                    ]);

                    $request->session()->put('donation_id', $donation->id);

                    return redirect()->route('thank-you');
                }

                Log::error('Payment verification failed after query/execute/search', [
                    'donation_id' => $donation->id,
                    'payment_id_suffix' => substr($paymentID, -8),
                    'transaction_status' => $finalResponse['transactionStatus'] ?? null,
                    'statusCode' => $finalResponse['statusCode'] ?? null,
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
                ->withErrors(['error' => $this->toUserFriendlyMessage($e->getMessage())]);
        }
    }

    private function isSuccessfulPaymentForDonation(array $response, Donation $donation, string $paymentID): bool
    {
        $responsePaymentId = (string) ($response['paymentID'] ?? $response['paymentId'] ?? '');
        $responseAmount = number_format((float) ($response['amount'] ?? 0), 2, '.', '');
        $donationAmount = number_format((float) $donation->amount, 2, '.', '');
        $transactionStatus = strtolower((string) ($response['transactionStatus'] ?? ''));

        return $transactionStatus === 'completed'
            && $responsePaymentId === $paymentID
            && $responseAmount === $donationAmount;
    }

    private function toUserFriendlyMessage(string $error): string
    {
        $normalized = strtolower($error);

        if (str_contains($normalized, 'cancel')) {
            return 'Payment Cancelled by User.';
        }

        if (str_contains($normalized, 'otp')) {
            return 'Payment Failed. Invalid OTP or authorization issue. Please try again.';
        }

        return trim($error) !== ''
            ? $error
            : 'An error occurred processing your payment.';
    }
}
