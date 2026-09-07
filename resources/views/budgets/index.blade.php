<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Category Budgets') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-700 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-red-100 border border-red-300 text-red-700 rounded-md text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Add Budget Form -->
                <div class="bg-white p-6 shadow-sm rounded-lg h-fit">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Set Category Budget</h3>
                    <form action="{{ route('budgets.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Expense Category</label>
                            <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Budget Limit (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="e.g. 25000" required>
                        </div>

                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md text-sm">
                            Save Budget
                        </button>
                    </form>
                </div>

                <!-- Budgets List & Progress -->
                <div class="bg-white p-6 shadow-sm rounded-lg lg:col-span-2">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-gray-800">
                            Budgets for {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
                        </h3>

                        <!-- Month & Year Filter -->
                        <form method="GET" action="{{ route('budgets.index') }}" class="flex items-center space-x-2">
                            <select name="month" class="rounded-md border-gray-300 text-xs py-1">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected($month == $m)>
                                        {{ \Carbon\Carbon::create()->month($m)->format('M') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="year" class="rounded-md border-gray-300 text-xs py-1">
                                @for($y = 2025; $y <= 2027; $y++)
                                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                            <button type="submit" class="px-3 py-1 bg-gray-800 text-white rounded-md text-xs font-semibold">Filter</button>
                        </form>
                    </div>

                    <div class="space-y-5">
                        @forelse($budgets as $b)
                            <div class="p-4 border rounded-lg {{ $b->is_exceeded ? 'border-red-300 bg-red-50/50' : ($b->is_warning ? 'border-amber-300 bg-amber-50/50' : 'border-gray-200') }}">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <span class="font-bold text-gray-900">{{ $b->category->name }}</span>
                                        @if($b->is_exceeded)
                                            <span class="ml-2 px-2 py-0.5 bg-red-600 text-white text-[10px] font-extrabold rounded uppercase tracking-wider">Over Budget</span>
                                        @elseif($b->is_warning)
                                            <span class="ml-2 px-2 py-0.5 bg-amber-500 text-white text-[10px] font-extrabold rounded uppercase tracking-wider">Warning 80%+</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('budgets.destroy', $b) }}" method="POST" onsubmit="return confirm('Delete this budget?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                    <div class="h-2.5 rounded-full {{ $b->is_exceeded ? 'bg-red-600' : ($b->is_warning ? 'bg-amber-500' : 'bg-indigo-600') }}" style="width: {{ $b->percentage }}%"></div>
                                </div>

                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Spent: <strong class="text-gray-800">PKR {{ number_format($b->spent, 2) }}</strong></span>
                                    <span>Limit: <strong class="text-gray-800">PKR {{ number_format($b->amount, 2) }}</strong></span>
                                    <span>
                                        {{ $b->is_exceeded ? 'Exceeded by:' : 'Remaining:' }}
                                        <strong class="{{ $b->is_exceeded ? 'text-red-600' : 'text-green-600' }}">
                                            PKR {{ number_format(abs($b->remaining), 2) }}
                                        </strong>
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 py-6 text-center">No budgets set for this month.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>