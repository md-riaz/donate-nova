@extends('layouts.app')

@section('title', 'Thank You for Your Donation')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl shadow-lg p-8 md:p-12 text-center">
            <!-- Success Icon -->
            <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                Thank You!
            </h1>

            <p class="text-lg text-gray-600 mb-8">
                Your donation helps us continue our mission to make a difference in the lives of those we serve.
            </p>

            @if(isset($donation))
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Donation Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-semibold text-gray-800">{{ $donation->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-semibold text-green-600">৳{{ number_format($donation->amount, 2) }}</span>
                        </div>
                        @if($donation->transaction_id)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Transaction ID:</span>
                                <span class="font-mono text-sm text-gray-800">{{ $donation->transaction_id }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <a href="{{ route('landing') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors duration-200">
                    Back to Home
                </a>
            </div>

            <!-- Social Share (Optional) -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Share your good deed</p>
                <div class="flex justify-center space-x-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('landing')) }}" target="_blank" class="text-gray-400 hover:text-blue-600 transition-colors">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
