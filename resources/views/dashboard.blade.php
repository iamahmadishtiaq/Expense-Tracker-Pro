<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Financial Overview') }} — {{ \Carbon\Carbon::now()->format('F Y') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">
                + Add Transaction
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Metrics Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Net Balance</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">PKR {{ number_format($totalBalance, 2) }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Monthly Income</p>
                    <p class="text-2xl font-black text-green-600 mt-1">PKR {{ number_format($monthlyIncome, 2) }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-rose-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Monthly Expenses</p>
                    <p class="text-2xl font-black text-rose-600 mt-1">PKR {{ number_format($monthlyExpense, 2) }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 {{ $monthlySavings >= 0 ? 'border-emerald-500' : 'border-amber-500' }}">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Net Savings</p>
                    <p class="text-2xl font-black {{ $monthlySavings >= 0 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                        PKR {{ number_format($monthlySavings, 2) }}
                    </p>
                </div>
            </div>

            <!-- Category Breakdown & Recent Transactions Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Category Expenses -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-1">
                    <h3 class="text-base font-bold text-gray-900 border-b pb-3 mb-4">Expenses by Category</h3>
                    <div class="space-y-4">
                        @forelse($expensesByCategory as $cat)
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-gray-700">{{ $cat->category_name }}</span>
                                    <span class="text-gray-900 font-bold">PKR {{ number_format($cat->total_amount, 2) }}</span>
                                </div>
                                @php
                                    $percentage = $monthlyExpense > 0 ? round(($cat->total_amount / $monthlyExpense) * 100, 1) : 0;
                                @endphp
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400 mt-0.5 block text-right">{{ $percentage }}%</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 py-4 text-center">No expenses recorded this month.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                    <div class="flex justify-between items-center border-b pb-3 mb-4">
                        <h3 class="text-base font-bold text-gray-900">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="text-xs text-indigo-600 hover:underline">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead>
                                <tr class="text-xs text-gray-400 uppercase text-left">
                                    <th class="pb-2">Date</th>
                                    <th class="pb-2">Account</th>
                                    <th class="pb-2">Details</th>
                                    <th class="pb-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentTransactions as $tx)
                                    <tr>
                                        <td class="py-3 text-gray-500 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M') }}
                                        </td>
                                        <td class="py-3 text-gray-800 whitespace-nowrap">
                                            {{ $tx->account->name }}
                                            @if($tx->type === 'transfer' && $tx->toAccount)
                                                → <span class="text-indigo-600">{{ $tx->toAccount->name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-gray-600 whitespace-nowrap">
                                            <span class="inline-block px-1.5 py-0.5 rounded text-xs font-semibold mr-1.5
                                                @if($tx->type === 'income') bg-green-100 text-green-700
                                                @elseif($tx->type === 'expense') bg-rose-100 text-rose-700
                                                @else bg-blue-100 text-blue-700 @endif">
                                                {{ ucfirst($tx->type) }}
                                            </span>
                                            {{ $tx->category?->name ?? 'Transfer' }}
                                        </td>
                                        <td class="py-3 text-right font-bold whitespace-nowrap
                                            @if($tx->type === 'income') text-green-600
                                            @elseif($tx->type === 'expense') text-rose-600
                                            @else text-blue-600 @endif">
                                            {{ $tx->type === 'expense' ? '-' : '+' }} PKR {{ number_format($tx->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400">No recent transactions.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>