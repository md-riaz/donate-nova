@extends('layouts.app')

@section('title', 'Donate to Nova Foundation - Transform Lives Today')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Hero Section -->
    <div class="text-white py-24 px-4 relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(11,63,119,0.82) 0%, rgba(10,75,143,0.8) 45%, rgba(11,53,104,0.86) 100%), url('https://www.nova.org.bd/Content/img/banner/event-banner.jpg'); background-size: cover; background-position: center;">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgb(199 199 199 / 59%) 0%, rgb(17 17 17 / 45%) 100%);"></div>
        <div class="max-w-5xl mx-auto text-center relative z-10">
            <div class="mb-6">
                <span class="inline-block bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium">
                    Nova Foundation
                </span>
            </div>
            <div class="mb-6 flex justify-center">
                <a href="https://www.nova.org.bd/" target="_blank" rel="noopener">
                    <img src="https://www.nova.org.bd/Content/img/logo/nova-foundation.svg" alt="Nova Foundation Logo" class="h-14 md:h-16 w-auto" />
                </a>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                Transform Lives,<br/>Build Hope
            </h1>
            <p class="text-xl md:text-2xl mb-4 text-green-50 max-w-3xl mx-auto leading-relaxed">
                Your generosity empowers communities, educates children, and creates lasting change across Bangladesh
            </p>
            <p class="text-lg mb-8 text-green-100 max-w-2xl mx-auto">
                Join thousands of donors making a real difference every day
            </p>
            <a href="#donate-form" class="inline-block bg-pink-600 hover:bg-pink-700 text-white font-bold px-10 py-5 rounded-lg text-lg transition-all duration-200 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                Donate Now - Start Making Impact
            </a>
        </div>
    </div>

    <!-- Donation Form Section -->
    <div id="donate-form" class="bg-gradient-to-br from-green-600 to-teal-700 py-20 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white p-8 md:p-10 rounded-2xl shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold mb-3 text-gray-800">Make Your Donation</h2>
                    <p class="text-gray-600">Your contribution takes just 2 minutes - secure, fast, and impactful</p>

                    <!-- bKash Logo Section -->
                    <div class="mt-6 flex items-center justify-center gap-3">
                        <span class="text-gray-600 font-medium">Donate with</span>
                        <img src="{{ asset('bkash-icon.svg') }}" alt="bKash icon" class="h-8 w-8" />
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Left Column - Amount Selection -->
                    <div>
                        <!-- Quick Amount Selector -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-3">Choose an amount or enter your own</label>
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <button type="button" onclick="setAmount(500)" class="amount-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg transition-all">
                                    ৳500
                                </button>
                                <button type="button" onclick="setAmount(1000)" class="amount-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg transition-all">
                                    ৳1,000
                                </button>
                                <button type="button" onclick="setAmount(2000)" class="amount-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg transition-all">
                                    ৳2,000
                                </button>
                                <button type="button" onclick="setAmount(5000)" class="amount-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg transition-all">
                                    ৳5,000
                                </button>
                            </div>
                            <button type="button" onclick="setAmount(10000)" class="amount-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg transition-all w-full">
                                ৳10,000
                            </button>
                        </div>

                        <form method="POST" action="{{ route('donate.submit') }}" id="donationForm">
                            @csrf

                            <div class="mb-5">
                                <label for="amount" class="block text-gray-700 font-semibold mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Donation Amount (BDT)
                                    </span>
                                </label>
                                <input
                                    type="number"
                                    name="amount"
                                    id="amount"
                                    value="{{ old('amount') }}"
                                    placeholder="Enter custom amount"
                                    min="10"
                                    step="1"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    required
                                />
                                <p class="text-sm text-gray-500 mt-2">Minimum donation: ৳10</p>
                            </div>
                    </div>

                    <!-- Right Column - Personal Info -->
                    <div>
                        <div class="mb-5">
                            <label for="name" class="block text-gray-700 font-semibold mb-2">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Your Name
                                </span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                placeholder="Enter your full name"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                required
                            />
                        </div>

                        <div class="mb-6">
                            <label for="phone" class="block text-gray-700 font-semibold mb-2">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Phone Number
                                </span>
                            </label>
                            <input
                                type="text"
                                name="phone"
                                id="phone"
                                value="{{ old('phone') }}"
                                placeholder="01XXXXXXXXX"
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                required
                            />
                            <p class="text-sm text-gray-500 mt-2">Your contact phone number for donation updates</p>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center group"
                        >
                            <span class="text-lg flex items-center gap-2">
                                Complete Donation with
                                <img src="{{ asset('bkash-icon-white.svg') }}" alt="bKash icon" class="h-6 w-6" />
                                bKash
                            </span>
                            <svg class="w-6 h-6 ml-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>

                        <div class="mt-4 flex items-center justify-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Secured by 256-bit SSL encryption
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Donation Notice Section -->
    <div class="bg-white py-10 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-100 rounded-xl px-5 py-5 md:px-8 md:py-6 text-center shadow-sm">
                <p class="text-sm md:text-base text-gray-700 leading-relaxed">
                    After giving your donation, please inform us through SMS or phone (01742-190704).
                    Share the donated amount and specify the project where you directed your contribution.
                </p>
            </div>
        </div>
    </div>

    <!-- Impact Stats Section -->
    <div class="bg-gradient-to-b from-green-50 to-white py-12 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div class="p-6">
                    <div class="text-4xl md:text-5xl font-bold text-green-600 mb-2">10,000+</div>
                    <p class="text-gray-700 font-medium">Lives Impacted</p>
                </div>
                <div class="p-6">
                    <div class="text-4xl md:text-5xl font-bold text-green-600 mb-2">50+</div>
                    <p class="text-gray-700 font-medium">Active Projects</p>
                </div>
                <div class="p-6">
                    <div class="text-4xl md:text-5xl font-bold text-green-600 mb-2">25+</div>
                    <p class="text-gray-700 font-medium">Districts Reached</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Story Section -->
    <div class="bg-white py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-6 text-gray-800">
                Every Donation Creates a Ripple of Change
            </h2>
            <p class="text-lg text-gray-600 text-center mb-12 max-w-3xl mx-auto">
                At Nova Foundation, we believe in the power of collective action. Your contribution, no matter the size,
                helps us provide education, healthcare, and essential support to underprivileged communities across Bangladesh.
            </p>

            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <div class="bg-green-50 p-6 rounded-xl">
                    <div class="flex items-start mb-4">
                        <div class="bg-green-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold flex-shrink-0">
                            1
                        </div>
                        <div class="ml-4">
                            <h3 class="font-bold text-lg mb-2 text-gray-800">Education for All</h3>
                            <p class="text-gray-600">Providing books, supplies, and scholarships to children who dream of a better future</p>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 p-6 rounded-xl">
                    <div class="flex items-start mb-4">
                        <div class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold flex-shrink-0">
                            2
                        </div>
                        <div class="ml-4">
                            <h3 class="font-bold text-lg mb-2 text-gray-800">Healthcare Access</h3>
                            <p class="text-gray-600">Organizing medical camps and providing essential medicines to remote villages</p>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 p-6 rounded-xl">
                    <div class="flex items-start mb-4">
                        <div class="bg-purple-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold flex-shrink-0">
                            3
                        </div>
                        <div class="ml-4">
                            <h3 class="font-bold text-lg mb-2 text-gray-800">Community Development</h3>
                            <p class="text-gray-600">Building infrastructure and creating sustainable livelihood opportunities</p>
                        </div>
                    </div>
                </div>
                <div class="bg-orange-50 p-6 rounded-xl">
                    <div class="flex items-start mb-4">
                        <div class="bg-orange-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold flex-shrink-0">
                            4
                        </div>
                        <div class="ml-4">
                            <h3 class="font-bold text-lg mb-2 text-gray-800">Emergency Relief</h3>
                            <p class="text-gray-600">Rapid response to natural disasters with food, shelter, and medical aid</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trust & Transparency Section -->
    <div class="bg-gray-50 py-16 px-4">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Why Donors Trust Nova Foundation</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-md text-center hover:shadow-xl transition-shadow">
                    <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.018.088A9.001 9.001 0 1012 21 9.001 9.001 0 0021.088 12.018"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl mb-3 text-gray-800">100% Secure</h3>
                    <p class="text-gray-600">Bank-level encryption and secure payment gateway through bKash</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center hover:shadow-xl transition-shadow">
                    <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl mb-3 text-gray-800">Fully Transparent</h3>
                    <p class="text-gray-600">Track every taka - see exactly how your donation creates impact</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center hover:shadow-xl transition-shadow">
                    <div class="bg-pink-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl mb-3 text-gray-800">Real Impact</h3>
                    <p class="text-gray-600">99% of donations go directly to programs that change lives</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Proof Section -->
    <div class="bg-white py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold mb-12 text-gray-800">Join Our Community of Changemakers</h2>
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <div class="p-6">
                    <div class="text-5xl mb-4">👥</div>
                    <div class="text-3xl font-bold text-green-600 mb-2">5,000+</div>
                    <p class="text-gray-600 font-medium">Active Donors</p>
                </div>
                <div class="p-6">
                    <div class="text-5xl mb-4">⭐</div>
                    <div class="text-3xl font-bold text-green-600 mb-2">4.9/5</div>
                    <p class="text-gray-600 font-medium">Donor Satisfaction</p>
                </div>
                <div class="p-6">
                    <div class="text-5xl mb-4">🎯</div>
                    <div class="text-3xl font-bold text-green-600 mb-2">100%</div>
                    <p class="text-gray-600 font-medium">Transparency Score</p>
                </div>
            </div>

            <div class="bg-green-50 p-8 rounded-xl max-w-3xl mx-auto">
                <p class="text-lg text-gray-700 italic mb-4">
                    "Nova Foundation has consistently demonstrated accountability and transparency. Every donation makes a measurable impact."
                </p>
                <p class="font-semibold text-gray-800">- Verified Donor, Dhaka</p>
            </div>
        </div>
    </div>

    <!-- Contact Us Section -->
    <div class="bg-gradient-to-b from-white to-green-50/40 py-24 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white border border-green-100 rounded-2xl p-6 md:p-10 shadow-sm">
                <div class="text-center">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Contact Us for Donation</h2>
                    <p class="text-gray-600 mb-8">We are here to help you complete your contribution smoothly.</p>
                </div>

                <div class="mt-14 grid md:grid-cols-2 gap-6">
                    <div class="h-full bg-gray-50 border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Phone</p>
                                <p class="text-gray-900 font-semibold text-lg">09613-825925</p>
                            </div>
                        </div>
                    </div>

                    <div class="h-full bg-gray-50 border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.94 6.34A2 2 0 014.5 5h11a2 2 0 011.56 1.34L10 10.74 2.94 6.34z"></path>
                                    <path d="M18 8.12l-7.4 4.18a1.25 1.25 0 01-1.2 0L2 8.12V14a2 2 0 002 2h12a2 2 0 002-2V8.12z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Email</p>
                                <p class="text-gray-900 font-semibold text-lg">info@nova.org.bd</p>
                            </div>
                        </div>
                    </div>

                    <div class="h-full bg-gray-50 border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">USA Office</p>
                                <p class="text-gray-900 font-semibold leading-snug">7200 Lake Ellenor Dr, Suite 108 Orlando, FL-32809</p>
                            </div>
                        </div>
                    </div>

                    <div class="h-full bg-gray-50 border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Bangladesh Office</p>
                                <p class="text-gray-900 font-semibold leading-snug">H# Padmarag, Rahman Nagar, Bogura - 5800</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-gray-300 px-4 pt-14" style="background:#111722;">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-4 gap-10 pb-14">
                <div>
                    <a href="https://www.nova.org.bd/" target="_blank" rel="noopener" class="inline-block">
                        <img src="{{ asset('nova-foundation.svg') }}" alt="Nova Foundation" class="h-11 w-auto" />
                    </a>
                    <p class="mt-4 text-sm leading-7 text-gray-400 max-w-sm">
                        The Nova Foundation is a Bangladesh nonprofit social development organization. We work to improve healthcare, poverty reduction, education, environment and livelihoods.
                    </p>
                    <div class="mt-4 flex items-center gap-2">
                        <a href="https://www.facebook.com/nova.org.bd" target="_blank" rel="noopener" class="w-7 h-7 rounded bg-green-600 hover:bg-green-500 text-white text-xs flex items-center justify-center">f</a>
                        <a href="https://www.linkedin.com/company/nova-foundation-bd" target="_blank" rel="noopener" class="w-7 h-7 rounded bg-green-600 hover:bg-green-500 text-white text-[10px] font-semibold flex items-center justify-center">in</a>
                        <a href="https://www.youtube.com/@novafoundationbd" target="_blank" rel="noopener" class="w-7 h-7 rounded bg-green-600 hover:bg-green-500 text-white text-[10px] flex items-center justify-center">▶</a>
                    </div>
                </div>

                <div>
                    <h4 class="text-gray-200 text-base font-medium mb-4">Projects</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="https://www.nova.org.bd/self-dependent-program/" target="_blank" rel="noopener" class="hover:text-green-400">Self-Dependent Program</a></li>
                        <li><a href="https://www.nova.org.bd/free-healthcare/" target="_blank" rel="noopener" class="hover:text-green-400">Free Healthcare</a></li>
                        <li><a href="https://www.nova.org.bd/clean-water-project/" target="_blank" rel="noopener" class="hover:text-green-400">Clean Water Project</a></li>
                        <li><a href="https://www.nova.org.bd/orphanage-center/" target="_blank" rel="noopener" class="hover:text-green-400">Orphanage Center</a></li>
                        <li><a href="https://www.nova.org.bd/zakat-distribution/" target="_blank" rel="noopener" class="hover:text-green-400">Zakat Distribution</a></li>
                        <li><a href="https://www.nova.org.bd/blood-donation/" target="_blank" rel="noopener" class="hover:text-green-400">Blood Donation</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-gray-200 text-base font-medium mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="https://www.nova.org.bd/about-us/" target="_blank" rel="noopener" class="hover:text-green-400">About Us</a></li>
                        <li><a href="https://www.nova.org.bd/events/" target="_blank" rel="noopener" class="hover:text-green-400">Events</a></li>
                        <li><a href="https://www.nova.org.bd/media-coverage/" target="_blank" rel="noopener" class="hover:text-green-400">Media Coverage</a></li>
                        <li><a href="https://www.nova.org.bd/videos/" target="_blank" rel="noopener" class="hover:text-green-400">Videos</a></li>
                        <li><a href="https://www.nova.org.bd/volunteer/" target="_blank" rel="noopener" class="hover:text-green-400">Volunteer</a></li>
                        <li><a href="https://www.nova.org.bd/contact-us/" target="_blank" rel="noopener" class="hover:text-green-400">Contact Us</a></li>
                        <li><a href="https://www.nova.org.bd/privacy-policy/" target="_blank" rel="noopener" class="hover:text-green-400">Privacy Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-gray-200 text-base font-medium mb-4">Events</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="https://www.nova.org.bd/events/" target="_blank" rel="noopener" class="hover:text-green-400">Nova Foundation Events</a></li>
                        <li><a href="https://www.nova.org.bd/iftar-ramadan-food-distribution/" target="_blank" rel="noopener" class="hover:text-green-400">Iftar & Ramadan Food</a></li>
                        <li><a href="https://www.nova.org.bd/zakat-distribution/" target="_blank" rel="noopener" class="hover:text-green-400">Zakat Distribution</a></li>
                        <li><a href="https://www.nova.org.bd/qurbani-meat-distribution/" target="_blank" rel="noopener" class="hover:text-green-400">Qurbani Meat Distribution</a></li>
                    </ul>
                </div>
            </div>

            <div class="py-5 text-center text-xs md:text-sm text-gray-500" style="border-top:1px solid #1f2937;">
                <p>© {{ date('Y') }} Nova Foundation. All Rights Reserved.</p>
                <p class="mt-1">Website Design &amp; Hosted by <a href="https://alpha.net.bd/" target="_blank" rel="noopener" class="hover:text-green-400">Alpha Net</a>.</p>
            </div>
        </div>
    </footer>
</div>

<script>
function setAmount(value) {
    document.getElementById('amount').value = value;
    // Highlight selected button
    document.querySelectorAll('.amount-btn').forEach(btn => {
        btn.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        btn.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
    });
    event.target.classList.remove('bg-green-50', 'text-green-700', 'border-green-200');
    event.target.classList.add('bg-green-600', 'text-white', 'border-green-600');
}

// Smooth scroll to form
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
@endsection
