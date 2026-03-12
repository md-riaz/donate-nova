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
            'phone' => ['required', 'string', 'max:14', 'regex:/^(?:\+88|88)?01[3-9]\d{8}$/'],
            'amount' => 'required|numeric|min:10',
        ], [
            'phone.regex' => 'Please enter a valid Bangladeshi mobile number (e.g. +8801XXXXXXXXX, 8801XXXXXXXXX, or 01XXXXXXXXX).',
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
                $redirectUrl = (string) $response['bkashURL'];

                if (! $this->isTrustedBkashRedirectUrl($redirectUrl)) {
                    Log::warning('Untrusted bKash redirect URL blocked', [
                        'donation_id' => $donation->id,
                        'host' => parse_url($redirectUrl, PHP_URL_HOST),
                    ]);

                    $donation->update(['status' => 'failed']);

                    return redirect()->route('landing')
                        ->withErrors(['error' => 'Payment redirect validation failed. Please try again.']);
                }

                // Update donation with bKash payment ID
                $donation->update([
                    'bkash_payment_id' => $response['paymentID'] ?? $response['paymentId'] ?? null,
                ]);

                // Extract and store the callback signature bKash embeds in the callback URLs.
                // This is validated on callback to reject fabricated requests.
                $callbackSignature = null;
                $successCallbackURL = (string) ($response['successCallbackURL'] ?? '');
                if ($successCallbackURL !== '') {
                    parse_str((string) parse_url($successCallbackURL, PHP_URL_QUERY), $callbackParams);
                    $callbackSignature = $callbackParams['signature'] ?? null;
                }
                $request->session()->put('bkash_signature', $callbackSignature);

                return redirect()->away($redirectUrl);
            }

            // If payment creation failed
            Log::error('bKash payment creation failed', [
                'donation_id' => $donation->id,
                'statusCode' => $response['statusCode'] ?? null,
                'statusMessage' => $response['statusMessage'] ?? $response['message'] ?? null,
            ]);
            $donation->update(['status' => 'failed']);

            return redirect()->route('landing')
                ->withErrors(['error' => 'Payment initialization failed. Please try again.']);

        } catch (\Exception $e) {
            Log::error('Donation creation error', [
                'donation_id' => $donation->id ?? null,
                'error' => $e->getMessage(),
            ]);

            if (isset($donation)) {
                $donation->update(['status' => 'failed']);
            }

            return redirect()->route('landing')
                ->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }

    private function isTrustedBkashRedirectUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        $allowedHosts = array_map(
            static fn ($item) => strtolower(trim((string) $item)),
            (array) config('bkash.allowed_redirect_hosts', [])
        );

        foreach ($allowedHosts as $allowedHost) {
            if ($allowedHost !== '' && ($host === $allowedHost || str_ends_with($host, '.'.$allowedHost))) {
                return true;
            }
        }

        return false;
    }
}
