<?php

/**
 * Rounds a number up to a specified precision.
 *
 * This function calculates the ceiling of a number at a specific decimal place.
 * Note: The precision parameter determines the length of the multiplier string.
 * For example, a precision of 3 generates a multiplier of 100 (10^2), resulting in 2 decimal places.
 *
 * @param float|int $number    The numeric value to be rounded.
 * @param int       $precision The length of the padding for the multiplier (determines decimal places).
 * Default is 2 (which rounds to 1 decimal place: 10^1).
 * @return float|int The rounded number.
 */
function round_up($number, $precision = 2)
{
    $fig = (int) str_pad('1', $precision, '0');
    return (ceil($number * $fig) / $fig);
}