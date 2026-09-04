<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transactions') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">
                + New Transaction
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters Bar -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <select name="type" class="rounded-md border-gray-300 text-sm">
                        <option value="">All Types</option>
                        <option value="income" @selected(request('type') === 'income')>Income</option>
                        <option value="expense" @selected(request('type') === 'expense')>Expense</option>
                        <option value="transfer" @selected(request('type') === 'transfer')>Transfer</option>
                    </select>

                    <select name="account_id" class="rounded-md border-gray-300 text-sm">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->name }}</option>
                        @endforeach
                    </select>

                    <select name="category_id" class="rounded-md border-gray-300 text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="rounded-md border-gray-300 text-sm" placeholder="From Date">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="rounded-md border-gray-300 text-sm" placeholder="To Date">

                    <div class="md:col-span-5 flex justify-end space-x-2">
                        <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 border rounded-md text-xs text-gray-600">Reset</a>
                        <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white rounded-md text-xs font-semibold">Filter</button>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Account</th>
                            <th class="px-6 py-3 text-left">Category / Details</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        @forelse($transactions as $t)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $t->transaction_date->format('d M, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold 
                                        @if($t->type === 'income') bg-green-100 text-green-800 
                                        @elseif($t->type === 'expense') bg-rose-100 text-rose-800 
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($t->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">
                                    {{ $t->account->name }}
                                    @if($t->type === 'transfer' && $t->toAccount)
                                        → <span class="text-indigo-600">{{ $t->toAccount->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ $t->category?->name ?? 'Transfer' }}
                                    @if($t->description)
                                        <span class="text-xs text-gray-400 block">{{ $t->description }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold 
                                    @if($t->type === 'income') text-green-600 
                                    @elseif($t->type === 'expense') text-rose-600 
                                    @else text-blue-600 @endif">
                                    {{ $t->type === 'expense' ? '-' : '+' }} PKR {{ number_format($t->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form action="{{ route('transactions.destroy', $t) }}" method="POST" onsubmit="return confirm('Revert and delete this transaction?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-4 border-t">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>