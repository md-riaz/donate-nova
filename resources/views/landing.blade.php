@extends('layouts.app')

@section('title', 'Support Nova - Make a Donation')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                Support Nova
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-green-100">
                Your donation helps us continue our mission to make a difference
            </p>
            <a href="#donate-form" class="inline-block bg-pink-600 hover:bg-pink-700 text-white font-semibold px-8 py-4 rounded-lg text-lg transition-colors duration-200">
                Donate Now
            </a>
        </div>
    </div>

    <!-- Trust Section -->
    <div class="bg-white py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Why Your Donation Matters</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Trusted</h3>
                    <p class="text-gray-600">100% secure payment through bKash</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Transparent</h3>
                    <p class="text-gray-600">Every taka goes to our mission</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Impactful</h3>
                    <p class="text-gray-600">Making real change in lives</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Donation Form Section -->
    <div id="donate-form" class="bg-gray-100 py-16 px-4">
        <div class="max-w-lg mx-auto">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Make Your Donation</h2>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('donate.submit') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Your Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="Enter your name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required
                        />
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone') }}"
                            placeholder="01XXXXXXXXX"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required
                        />
                    </div>

                    <div class="mb-6">
                        <label for="amount" class="block text-gray-700 font-medium mb-2">Donation Amount (BDT)</label>
                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            value="{{ old('amount') }}"
                            placeholder="500"
                            min="10"
                            step="1"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required
                        />
                        <p class="text-sm text-gray-500 mt-1">Minimum donation: ৳10</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold py-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
                    >
                        <span>Pay with bKash</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <p>&copy; {{ date('Y') }} Nova. All rights reserved.</p>
            <p class="mt-2 text-gray-400">Secure payment powered by bKash</p>
        </div>
    </footer>
</div>
@endsection
