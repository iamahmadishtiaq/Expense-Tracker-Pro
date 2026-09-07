<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Accounts / Wallets') }}
            </h2>
            <a href="{{ route('accounts.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium transition">
                + Add Account
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($accounts as $account)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500 border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $account->type }}</span>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $account->name }}</h3>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('accounts.edit', $account) }}" class="text-gray-400 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm transition">Edit</a>
                                <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 text-sm transition">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current Balance</p>
                            <p class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">PKR {{ number_format($account->current_balance, 2) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">No accounts added yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>