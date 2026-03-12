<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\BkashV2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BkashController extends Controller
{
    private const CALLBACK_API_DELAY_MS = 2000;

    public function __construct(private readonly BkashV2Service $bkash)
    {
    }

    public function callback(Request $request)
    {
        $paymentID = (string) $request->query('paymentID', '');
        $status = strtolower((string) $request->query('status', ''));
        $incomingSignature = (string) $request->query('signature', '');

        Log::info('bKash callback received', [
            'method' => $request->method(),
            'full_url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'query_params' => $request->query(),
            'request_payload' => $request->all(),
            'raw_body' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);

        try {
            // Validate bKash callback signature before any processing.
            // The signature was stored in the session at payment creation time,
            // extracted from the successCallbackURL bKash returned.
            $storedSignature = (string) $request->session()->get('bkash_signature', '');

            if ($storedSignature === '' || $incomingSignature === '' || !hash_equals($storedSignature, $incomingSignature)) {
                Log::warning('bKash callback signature mismatch - possible fabricated callback', [
                    'payment_id_suffix' => $paymentID !== '' ? substr($paymentID, -8) : null,
                    'status' => $status,
                    'has_stored_signature' => $storedSignature !== '',
                    'has_incoming_signature' => $incomingSignature !== '',
                ]);

                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Invalid payment callback.']);
            }

            // Get donation from session
            $donationId = $request->session()->get('donation_id');

            if (! $donationId) {
                Log::error('Donation ID not found in session');
                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Session expired. Please try again.']);
            }

            $donation = Donation::find($donationId);

            if (! $donation) {
                Log::error('Donation not found', ['id' => $donationId]);
                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Donation not found.']);
            }

            if ($paymentID === '') {
                Log::warning('Invalid callback: missing paymentID', [
                    'donation_id' => $donation->id,
                ]);

                $donation->update(['status' => 'failed']);

                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Invalid payment callback.']);
            }

            if (! empty($donation->bkash_payment_id) && $donation->bkash_payment_id !== $paymentID) {
                Log::warning('Payment ID mismatch on callback', [
                    'donation_id' => $donation->id,
                    'stored_payment_id_suffix' => substr((string) $donation->bkash_payment_id, -8),
                    'callback_payment_id_suffix' => substr($paymentID, -8),
                ]);

                $donation->update(['status' => 'failed']);

                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Payment verification failed.']);
            }

            if ($donation->status === 'success') {
                return redirect()->route('thank-you');
            }

            // Cancel callback URL scenario (user closed bKash page).
            if (in_array($status, ['cancel', 'cancelled'], true)) {
                $donation->update(['status' => 'failed']);

                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Payment Cancelled by User.']);
            }

            // Failure callback URL scenario (e.g. wrong OTP multiple times).
            if (in_array($status, ['failure', 'failed'], true)) {
                $donation->update(['status' => 'failed']);

                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Payment Failed. Invalid OTP or insufficient balance. Please try again.']);
            }

            // Execute payment (per bKash: execute first, handle timeout by querying)
            if ($status === 'success') {
                $this->delayBeforeConsistencySensitiveApiCall();
                
                // Execute with timeout detection - on timeout, queries immediately
                $finalResponse = $this->executePaymentWithTimeoutHandling($paymentID, $donation);

                if ($finalResponse === null) {
                    Log::error('Payment execution/query failed - no valid response', [
                        'donation_id' => $donation->id,
                        'payment_id_suffix' => substr($paymentID, -8),
                    ]);

                    $donation->update(['status' => 'failed']);

                    return redirect()->to($this->landingWithDonateFormAnchor())
                        ->withErrors(['error' => 'Payment verification failed.']);
                }

                // Handle "Initiated" status - transaction still processing at bKash
                if ($this->isPaymentInitiated($finalResponse)) {
                    $donation->update(['status' => 'pending']);

                    Log::info('Payment pending - transaction initiated at bKash', [
                        'donation_id' => $donation->id,
                        'payment_id_suffix' => substr($paymentID, -8),
                    ]);

                    // Redirect to thank-you but with pending status; background job can retry
                    $request->session()->put('donation_id', $donation->id);

                    return redirect()->route('thank-you')
                        ->with('notice', 'Your payment is being processed. You will receive confirmation soon.');
                }

                // Handle "Completed" status
                if ($this->isPaymentCompleted($finalResponse, $donation, $paymentID)) {
                    $executeResponse = $finalResponse;
                    $resolvedTrxId = (string) ($executeResponse['trxID'] ?? $executeResponse['trxId'] ?? '');

                    // Independently verify completion with bKash Query API.
                    // Execute response is authoritative, but query provides a second confirmation.
                    // bKash is eventually consistent: query may return 2033 "Transaction Not Found"
                    // immediately after execute — queryPaymentWithRetry returns null in that case.
                    // We trust execute if query cannot confirm due to eventual consistency.
                    $this->delayBeforeConsistencySensitiveApiCall();
                    $queryVerification = $this->queryPaymentWithRetry($paymentID, 3, 2000);

                    if ($queryVerification !== null && !$this->isPaymentCompleted($queryVerification, $donation, $paymentID)) {
                        // Query returned a response but it contradicts execute — do not mark success.
                        Log::error('Payment status conflict: execute says Completed but query disagrees', [
                            'donation_id' => $donation->id,
                            'payment_id_suffix' => substr($paymentID, -8),
                            'query_status' => $queryVerification['transactionStatus'] ?? null,
                            'query_statusCode' => $queryVerification['statusCode'] ?? null,
                        ]);

                        $donation->update(['status' => 'failed']);

                        return redirect()->to($this->landingWithDonateFormAnchor())
                            ->withErrors(['error' => 'Payment verification failed.']);
                    }

                    if ($queryVerification === null) {
                        // All query attempts failed (likely bKash eventual consistency / 2033).
                        // Execute response is still authoritative; proceed with a warning.
                        Log::warning('Query verification unavailable after execute — trusting execute response', [
                            'donation_id' => $donation->id,
                            'payment_id_suffix' => substr($paymentID, -8),
                        ]);
                    }

                    $donation->update([
                        'status' => 'success',
                        'transaction_id' => $resolvedTrxId ?: null,
                        'bkash_payment_id' => $paymentID,
                    ]);

                    Log::info('Payment successful', [
                        'donation_id' => $donation->id,
                        'transaction_id' => $resolvedTrxId ?: null,
                        'query_verified' => $queryVerification !== null,
                    ]);

                    $request->session()->put('donation_id', $donation->id);

                    return redirect()->route('thank-you');
                }

                Log::error('Payment verification failed - unexpected status', [
                    'donation_id' => $donation->id,
                    'payment_id_suffix' => substr($paymentID, -8),
                    'transaction_status' => $finalResponse['transactionStatus'] ?? null,
                    'statusCode' => $finalResponse['statusCode'] ?? null,
                ]);

                $donation->update(['status' => 'failed']);

                return redirect()->to($this->landingWithDonateFormAnchor())
                    ->withErrors(['error' => 'Payment verification failed.']);
            }

            // Invalid callback
            Log::warning('Invalid callback status', [
                'donation_id' => $donation->id,
                'payment_id_suffix' => substr($paymentID, -8),
                'status' => $status,
            ]);
            $donation->update(['status' => 'failed']);

            return redirect()->to($this->landingWithDonateFormAnchor())
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

            return redirect()->to($this->landingWithDonateFormAnchor())
                ->withErrors(['error' => $this->toUserFriendlyMessage($e->getMessage())]);
        }
    }

    private function executePaymentWithTimeoutHandling(string $paymentID, Donation $donation): ?array
    {
        try {
            // Execute payment - bKash timeout is 30 seconds
            // If we get a response within 30 sec, it's trustworthy
            $executeResponse = $this->bkash->executePayment($paymentID);

            return $executeResponse;
        } catch (\Throwable $executeError) {
            $errorMsg = strtolower($executeError->getMessage());

            // Check if this is a timeout error (no response within 30 sec)
            $isTimeout = str_contains($errorMsg, 'timeout')
                || str_contains($errorMsg, 'timed out')
                || str_contains($errorMsg, 'unable to reach')
                || str_contains($errorMsg, 'connection reset')
                || str_contains($errorMsg, 'no response');

            if ($isTimeout) {
                Log::warning('Execute Payment API timeout - querying payment status', [
                    'donation_id' => $donation->id,
                    'payment_id_suffix' => substr($paymentID, -8),
                    'error' => $executeError->getMessage(),
                ]);

                // Per bKash: On timeout, MUST call queryPayment to check actual status
                $this->delayBeforeConsistencySensitiveApiCall();

                return $this->queryPaymentWithRetry($paymentID, 3, 1000);
            }

            // Non-timeout error
            Log::error('Execute Payment API error', [
                'donation_id' => $donation->id,
                'payment_id_suffix' => substr($paymentID, -8),
                'error' => $executeError->getMessage(),
            ]);

            throw $executeError;
        }
    }

    private function isPaymentInitiated(array $response): bool
    {
        $transactionStatus = strtolower((string) ($response['transactionStatus'] ?? ''));

        return $transactionStatus === 'initiated';
    }

    private function isPaymentCompleted(array $response, Donation $donation, string $paymentID): bool
    {
        $responsePaymentId = (string) ($response['paymentID'] ?? $response['paymentId'] ?? '');
        $responseAmount = number_format((float) ($response['amount'] ?? 0), 2, '.', '');
        $donationAmount = number_format((float) $donation->amount, 2, '.', '');
        $transactionStatus = strtolower((string) ($response['transactionStatus'] ?? ''));

        return $transactionStatus === 'completed'
            && $responsePaymentId === $paymentID
            && $responseAmount === $donationAmount;
    }

    private function queryPaymentWithRetry(string $paymentID, int $retries, int $delayMs): ?array
    {
        $attempt = 0;

        while ($attempt <= $retries) {
            try {
                return $this->bkash->queryPayment($paymentID);
            } catch (\Throwable $queryError) {
                Log::warning('Query payment attempt failed', [
                    'payment_id_suffix' => substr($paymentID, -8),
                    'attempt' => $attempt + 1,
                    'max_attempts' => $retries + 1,
                    'error' => $queryError->getMessage(),
                ]);

                if ($attempt >= $retries) {
                    break;
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }

            $attempt++;
        }

        return null;
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

    private function delayBeforeConsistencySensitiveApiCall(): void
    {
        usleep(self::CALLBACK_API_DELAY_MS * 1000);
    }

    private function landingWithDonateFormAnchor(): string
    {
        return route('landing') . '#donate-form';
    }
}
