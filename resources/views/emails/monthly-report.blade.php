<x-mail::message>
# Monthly Financial Report — {{ $reportMonth }}

Hello {{ $user->name }},

Here is the financial summary for the previous month:

<x-mail::panel>
**Total Income:** {{ format_currency($income) }}  
**Total Expense:** {{ format_currency($expense) }}  
**Net Savings:** {{ format_currency($savings) }}
</x-mail::panel>

### Top Spending Categories:
@foreach($topCategories as $cat)
- **{{ $cat->category_name }}**: {{ format_currency($cat->total) }}
@endforeach

<x-mail::button :url="route('dashboard')">
View Full Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>