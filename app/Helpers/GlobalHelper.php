<?php

use Illuminate\Support\Str;

if (!function_exists('formatPrice')) {
    /**
     * Format the price with currency symbol and position.
     *
     * @param mixed $price
     * @param string $symbol
     * @param string $position
     * @return string
     */
    function formatPrice($price, $symbol, $position = 'left')
    {
        $formattedAmount = number_format((float) $price, 2);

        if ($position === 'right') {
            return $formattedAmount . ' ' . $symbol;
        }

        return $symbol . ' ' . $formattedAmount;
    }
}

if (!function_exists('formattedSeconds')) {
    /**
     * Seconds to Human Readable Time Format
     *
     * @param int $seconds
     * @return array
     */
    function formattedSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $sec = $seconds % 60;

        return [
            'seconds' => $seconds,
            'short_readable' => "$hours:$minutes:$sec",
            'detailed_readable' => $hours > 0
                ? "$hours Hrs $minutes Min"
                : ($minutes > 0 ? "$minutes Min $sec Sec" : "$sec Sec")
        ];
    }
}

if (!function_exists('calculateSpeedPerHour')) {
    /**
     * Calculate questions speed per hour
     *
     * @param int|float $totalAnswered
     * @param int|float $totalSeconds
     * @return float|int
     */
    function calculateSpeedPerHour($totalAnswered, $totalSeconds)
    {
        if ($totalAnswered === 0 || $totalSeconds === 0) {
            return 0;
        }
        return ($totalAnswered * 3600) / $totalSeconds;
    }
}

if (!function_exists('calculateAccuracy')) {
    /**
     * Calculate accuracy of a test
     *
     * @param int|float $correctAnswered
     * @param int|float $totalAnswered
     * @return float|int
     */
    function calculateAccuracy($correctAnswered, $totalAnswered)
    {
        return $totalAnswered != 0 ? ($correctAnswered / $totalAnswered) * 100 : 0;
    }
}

if (!function_exists('calculatePercentage')) {
    /**
     * Calculate percentage
     *
     * @param int|float $x
     * @param int|float $y
     * @return float|int
     */
    function calculatePercentage($x, $y)
    {
        return $y != 0 ? ($x / $y) * 100 : 0;
    }
}

if (!function_exists('calculatePercentileRank')) {
    /**
     * Calculate percentile
     *
     * @param array $array
     * @param mixed $key
     * @return float
     */
    function calculatePercentileRank($array, $key)
    {
        $n = count($array);
        if ($n === 0) return 0;

        // Use the binary search helper
        $l = binarySearchCount($array, $n, $key);

        return round(($l / $n) * 100, 0);
    }
}

if (!function_exists('binarySearchCount')) {
    /**
     * A binary search function to return number of elements less than or equal to given key
     *
     * @param array $arr
     * @param int $n
     * @param mixed $key
     * @return int
     */
    function binarySearchCount($arr, $n, $key)
    {
        $left = 0;
        $right = $n - 1;
        $count = 0;

        while ($left <= $right) {
            $mid = (int) round(($right + $left) / 2, 0);

            // Check if middle element is less than or equal to key
            if ($arr[$mid] <= $key) {
                // At least (mid + 1) elements are there whose values are less than or equal to key
                $count = $mid + 1;
                $left = $mid + 1;
            } else {
                // If key is smaller, ignore right half
                $right = $mid - 1;
            }
        }
        return $count;
    }
}

if (!function_exists('hex2rgba')) {
    /**
     * Convert hex color to rgb(a)
     *
     * @param string $color
     * @param bool|float $opacity
     * @return string
     */
    function hex2rgba($color, $opacity = false)
    {
        $default = 'rgb(0,0,0)';

        if (empty($color)) {
            return $default;
        }

        // Sanitize $color if "#" is provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        // Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) == 3) {
            $hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            return $default;
        }

        // Convert hexadec to rgb
        $rgb = array_map('hexdec', $hex);

        // Check if opacity is set(rgba or rgb)
        if ($opacity) {
            if (abs($opacity) > 1) {
                $opacity = 1.0;
            }
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }

        return $output;
    }
}
