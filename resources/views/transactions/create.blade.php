<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Transaction') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-md text-sm">
                    <p class="font-semibold">Error:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg" x-data="{ currentType: '{{ old('type', 'expense') }}' }">
                <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Transaction Type</label>
                        <select name="type" @change="currentType = $event.target.value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="expense" @selected(old('type', 'expense') === 'expense')>Expense</option>
                            <option value="income" @selected(old('type') === 'income')>Income</option>
                            <option value="transfer" @selected(old('type') === 'transfer')>Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" x-text="currentType === 'transfer' ? 'From Account (Source)' : 'Account'"></label>
                        <select name="account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('account_id') == $acc->id)>
                                    {{ $acc->name }} (PKR {{ number_format($acc->current_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Destination Account for Transfers -->
                    <template x-if="currentType === 'transfer'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">To Account (Destination)</label>
                            <select name="to_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Target Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" @selected(old('to_account_id') == $acc->id)>
                                        {{ $acc->name }} (PKR {{ number_format($acc->current_balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <!-- Category (Only for Expense & Income) -->
                    <template x-if="currentType !== 'transfer'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                        {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (PKR)</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('transactions.index') }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>