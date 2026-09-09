<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transactions') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium transition">
                + New Transaction
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ receiptModal: false, activeReceipt: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <select name="type" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="income" @selected(request('type') === 'income')>Income</option>
                        <option value="expense" @selected(request('type') === 'expense')>Expense</option>
                        <option value="transfer" @selected(request('type') === 'transfer')>Transfer</option>
                    </select>

                    <select name="account_id" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>
                        @endforeach
                    </select>

                    <select name="category_id" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="From Date">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="To Date">

                    <div class="md:col-span-5 flex justify-end items-center space-x-2 pt-1">
                        <a href="{{ route('transactions.export', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-semibold transition shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export CSV
                        </a>
                        <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Reset</a>
                        <button type="submit" class="px-4 py-1.5 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white rounded-md text-xs font-semibold transition">Filter</button>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Account</th>
                            <th class="px-6 py-3 text-left">Category / Details</th>
                            <th class="px-6 py-3 text-center">Receipt</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ format_date($t->transaction_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                        @if($t->type === 'income') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 
                                        @elseif($t->type === 'expense') bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 
                                        @else bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 @endif">
                                        {{ ucfirst($t->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800 dark:text-gray-200">
                                    {{ $t->account->name }}
                                    @if($t->type === 'transfer' && $t->toAccount)
                                        &rarr; <span class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $t->toAccount->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $t->category?->name ?? 'Transfer' }}</span>
                                    @if($t->description)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block mt-0.5">{{ $t->description }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($t->receipt_path)
                                        <button 
                                            type="button" 
                                            @click="activeReceipt = '{{ asset('storage/' . $t->receipt_path) }}'; receiptModal = true"
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900 transition border border-indigo-200 dark:border-indigo-800"
                                            title="View Attached Receipt">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            View
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold 
                                    @if($t->type === 'income') text-emerald-600 dark:text-emerald-400 
                                    @elseif($t->type === 'expense') text-rose-600 dark:text-rose-400 
                                    @else text-sky-600 dark:text-sky-400 @endif">
                                    @if($t->type === 'income') +
                                    @elseif($t->type === 'expense') -
                                    @endif
                                    {{ format_currency($t->amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form action="{{ route('transactions.destroy', $t) }}" method="POST" onsubmit="return confirm('Revert and delete this transaction?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium transition">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($transactions->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700 dark:bg-gray-800">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Receipt Modal Preview -->
        <div 
            x-show="receiptModal" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
            @click.self="receiptModal = false"
            @keydown.escape.window="receiptModal = false">
            <div class="relative max-w-2xl w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Transaction Receipt</h3>
                    <div class="flex items-center space-x-3">
                        <a :href="activeReceipt" target="_blank" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            Open Full Tab &rarr;
                        </a>
                        <button type="button" @click="receiptModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold leading-none">&times;</button>
                    </div>
                </div>
                <div class="p-4 flex items-center justify-center max-h-[75vh] overflow-auto bg-gray-100 dark:bg-gray-900">
                    <img :src="activeReceipt" alt="Attached Receipt" class="max-h-[70vh] w-auto rounded object-contain shadow">
                </div>
            </div>
        </div>
    </div>
</x-app-layout>