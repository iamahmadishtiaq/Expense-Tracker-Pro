<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Financial Reports & Exports') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">From Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">To Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Account</label>
                        <select name="account_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Type</label>
                        <select name="type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option value="income" @selected(request('type') === 'income')>Income</option>
                            <option value="expense" @selected(request('type') === 'expense')>Expense</option>
                            <option value="transfer" @selected(request('type') === 'transfer')>Transfer</option>
                        </select>
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit" class="w-full py-2 px-4 bg-gray-900 hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-md text-sm font-semibold transition">
                            Filter
                        </button>
                        <a href="{{ route('reports.export', request()->query()) }}" class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-center rounded-md text-sm font-semibold flex items-center justify-center transition">
                            Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <!-- Filtered Period Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border-l-4 border-emerald-500 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Filtered Income</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">PKR {{ number_format($totalIncome, 2) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border-l-4 border-rose-500 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Filtered Expenses</p>
                    <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">PKR {{ number_format($totalExpense, 2) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-lg shadow-sm border-l-4 {{ $netSavings >= 0 ? 'border-indigo-500' : 'border-amber-500' }} border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Net Difference</p>
                    <p class="text-2xl font-bold {{ $netSavings >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400' }} mt-1">
                        {{ $netSavings < 0 ? '-' : '' }}PKR {{ number_format(abs($netSavings), 2) }}
                    </p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-gray-100 text-sm">Filtered Records ({{ $transactions->total() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Type</th>
                                <th class="px-6 py-3 text-left">Account</th>
                                <th class="px-6 py-3 text-left">Category / Details</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M, Y') }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded 
                                            {{ $tx->type === 'income' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : ($tx->type === 'expense' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300') }}">
                                            {{ ucfirst($tx->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                        {{ $tx->account->name }}
                                        @if($tx->type === 'transfer' && $tx->toAccount)
                                            &rarr; <span class="text-indigo-600 dark:text-indigo-400">{{ $tx->toAccount->name }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $tx->category?->name ?? ($tx->type === 'transfer' ? 'Transfer' : '-') }}</td>
                                    <td class="px-6 py-3 text-right font-bold whitespace-nowrap {{ $tx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : ($tx->type === 'expense' ? 'text-rose-600 dark:text-rose-400' : 'text-sky-600 dark:text-sky-400') }}">
                                        {{ $tx->type === 'income' ? '+' : ($tx->type === 'expense' ? '-' : '') }}PKR {{ number_format($tx->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 dark:text-gray-500">No transactions match the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 dark:bg-gray-800">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>