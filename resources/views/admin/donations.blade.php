@extends('layouts.app')

@section('title', 'Admin Donations')

@section('content')
<div class="min-h-screen py-10 px-4">
    <main class="max-w-6xl mx-auto">
        <header class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Donations</h1>
            <p class="text-sm text-gray-600 mt-1">All donation records (no pagination)</p>
        </header>

        <section class="bg-white shadow rounded-lg overflow-hidden" aria-label="Donation records">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">ID</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">Name</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">Phone</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">Amount</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">Transaction ID</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">bKash Payment ID</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($donations as $donation)
                            <tr>
                                <td class="px-4 py-3">{{ $donation->id }}</td>
                                <td class="px-4 py-3">{{ $donation->name }}</td>
                                <td class="px-4 py-3">{{ $donation->phone }}</td>
                                <td class="px-4 py-3 font-semibold">৳{{ number_format($donation->amount, 2) }}</td>
                                <td class="px-4 py-3 capitalize">{{ $donation->status }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $donation->transaction_id ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $donation->bkash_payment_id ?? '-' }}</td>
                                <td class="px-4 py-3">{{ optional($donation->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">No donations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
