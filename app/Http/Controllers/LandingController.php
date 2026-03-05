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
        $donation = null;

        if ($donationId) {
            $donation = Donation::find($donationId);
            $request->session()->forget('donation_id');
        }

        return view('thank-you', compact('donation'));
    }
}
