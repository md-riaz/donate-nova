<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\View\View;

class AdminDonationController extends Controller
{
    public function index(): View
    {
        $donations = Donation::query()
            ->orderByDesc('created_at')
            ->get();

        return view('admin.donations', compact('donations'));
    }
}
