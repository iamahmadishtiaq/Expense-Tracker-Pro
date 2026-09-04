<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Account') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form action="{{ route('accounts.update', $account) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Name</label>
                        <input type="text" name="name" value="{{ old('name', $account->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Type</label>
                        <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="cash" @selected($account->type === 'cash')>Cash</option>
                            <option value="bank" @selected($account->type === 'bank')>Bank Account</option>
                            <option value="wallet" @selected($account->type === 'wallet')>Digital Wallet</option>
                            <option value="credit_card" @selected($account->type === 'credit_card')>Credit Card</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('accounts.index') }}" class="px-4 py-2 border rounded-md text-gray-600">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>