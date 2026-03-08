@extends('layouts.app')

@section('title', 'Thank You for Your Donation')

@section('content')
<div class="min-h-screen flex flex-col bg-gradient-to-b from-green-50 to-white">
    <div class="py-10 px-4">
        <div class="max-w-5xl mx-auto text-center">
            <a href="https://www.nova.org.bd/" target="_blank" rel="noopener" class="inline-block">
                <img src="https://www.nova.org.bd/Content/img/logo/nova-foundation.svg" alt="Nova Foundation Logo" class="h-12 md:h-14 w-auto mx-auto" />
            </a>
        </div>
    </div>

    <main class="flex-1 flex items-center justify-center px-4 pb-16">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-xl shadow-lg p-8 md:p-12 text-center">
                <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Thank You!</h1>

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

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-4">Share your good deed</p>
                    <div class="flex justify-center space-x-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('landing')) }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
    </div>
</div>
@endsection
