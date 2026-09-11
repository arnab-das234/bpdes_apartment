<?php

if (!function_exists('format_indian_currency')) {
    /**
     * Format a number in the Indian currency format (e.g. ₹ 12,34,567.89).
     */
    function format_indian_currency(mixed $amount): string
    {
        $amount = (float) $amount;
        $isNegative = $amount < 0;
        $amount = abs($amount);

        // Split into integer and decimal parts
        $parts = explode('.', sprintf('%.2f', $amount));
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '00';

        $lastThreeDigits = substr($integerPart, -3);
        $remainingDigits = substr($integerPart, 0, -3);

        if ($remainingDigits !== '') {
            // Group by twos for the remaining digits (Lakhs, Crores, etc.)
            $remainingDigits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $remainingDigits) . ',';
        }

        return ($isNegative ? '-' : '') . '₹ ' . $remainingDigits . $lastThreeDigits . '.' . $decimalPart;
    }
}
