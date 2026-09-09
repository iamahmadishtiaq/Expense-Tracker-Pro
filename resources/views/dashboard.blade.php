<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Financial Overview') }} — {{ \Carbon\Carbon::now()->format('F Y') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium transition">
                + Add Transaction
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Budget Threshold & Overrun Alerts -->
            @if(!empty($budgetAlerts) && count($budgetAlerts) > 0)
                <div class="space-y-3">
                    @foreach($budgetAlerts as $alert)
                        <div class="p-4 rounded-lg border {{ $alert['is_exceeded'] ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-200' : 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/60 text-amber-800 dark:text-amber-200' }} flex items-start justify-between">
                            <div class="flex items-start space-x-3">
                                <span class="text-xl">
                                    {{ $alert['is_exceeded'] ? '🚨' : '⚠️' }}
                                </span>
                                <div>
                                    <h4 class="font-semibold text-sm">
                                        {{ $alert['is_exceeded'] ? 'Budget Exceeded!' : 'Budget Warning (80% Reached)' }}
                                    </h4>
                                    <p class="text-xs mt-0.5 opacity-90">
                                        <strong>{{ $alert['category'] }}</strong>: You have spent 
                                        <strong>{{ format_currency($alert['spent']) }}</strong> of your 
                                        <strong>{{ format_currency($alert['budget']) }}</strong> limit ({{ $alert['percentage'] }}%).
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('budgets.index') }}" class="text-xs font-semibold underline hover:opacity-75 whitespace-nowrap ml-4">
                                View Budgets &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Metrics Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-indigo-500 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Net Balance</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1">{{ format_currency($totalBalance) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-emerald-500 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Income</p>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ format_currency($monthlyIncome) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 border-rose-500 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Expenses</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ format_currency($monthlyExpense) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border-l-4 {{ $monthlySavings >= 0 ? 'border-emerald-500' : 'border-amber-500' }} border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Savings</p>
                    <p class="text-2xl font-black {{ $monthlySavings >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} mt-1">
                        {{ $monthlySavings < 0 ? '-' : '' }}{{ format_currency(abs($monthlySavings)) }}
                    </p>
                </div>
            </div>

            <!-- Category Breakdown & Recent Transactions Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Category Expenses -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 lg:col-span-1 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">Expenses by Category</h3>
                    <div class="space-y-4">
                        @forelse($expensesByCategory as $cat)
                            @php
                                $percentage = $monthlyExpense > 0 ? round(($cat->total_amount / $monthlyExpense) * 100, 1) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $cat->category_name }}</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-bold">{{ format_currency($cat->total_amount) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 block text-right">{{ $percentage }}%</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">No expenses recorded this month.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 lg:col-span-2 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View All &rarr;</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            <thead>
                                <tr class="text-xs text-gray-400 dark:text-gray-500 uppercase text-left">
                                    <th class="pb-2">Date</th>
                                    <th class="pb-2">Account</th>
                                    <th class="pb-2">Details</th>
                                    <th class="pb-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($recentTransactions as $tx)
                                    <tr>
                                        <td class="py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ format_date($tx->transaction_date, 'd M') }}
                                        </td>
                                        <td class="py-3 text-gray-800 dark:text-gray-200 whitespace-nowrap font-medium">
                                            {{ $tx->account->name }}
                                            @if($tx->type === 'transfer' && $tx->toAccount)
                                                &rarr; <span class="text-indigo-600 dark:text-indigo-400">{{ $tx->toAccount->name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold mr-1.5
                                                @if($tx->type === 'income') bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300
                                                @elseif($tx->type === 'expense') bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300
                                                @else bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 @endif">
                                                {{ ucfirst($tx->type) }}
                                            </span>
                                            {{ $tx->category?->name ?? 'Transfer' }}
                                        </td>
                                        <td class="py-3 text-right font-bold whitespace-nowrap
                                            @if($tx->type === 'income') text-emerald-600 dark:text-emerald-400
                                            @elseif($tx->type === 'expense') text-rose-600 dark:text-rose-400
                                            @else text-blue-600 dark:text-blue-400 @endif">
                                            @if($tx->type === 'income') +
                                            @elseif($tx->type === 'expense') -
                                            @endif
                                            {{ format_currency($tx->amount) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400 dark:text-gray-500">No recent transactions.</td>
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