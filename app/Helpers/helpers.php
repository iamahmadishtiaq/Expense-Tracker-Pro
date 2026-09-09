<?php

if (! function_exists('format_currency')) {
    /**
     * Format any amount to standard PKR representation.
     */
    function format_currency(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $amount = (float) ($amount ?? 0);
        $formatted = number_format($amount, 2);

        return $withSymbol ? 'PKR ' . $formatted : $formatted;
    }
}

if (! function_exists('format_date')) {
    /**
     * Standard human-readable date format.
     */
    function format_date(\DateTimeInterface|string|null $date, string $format = 'd M, Y'): string
    {
        if (! $date) {
            return '-';
        }

        return \Carbon\Carbon::parse($date)->format($format);
    }
}