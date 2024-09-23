<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

function generateReference($length = 25)
{
    // Prefix (can be customized)
    $prefix = 'SHIP';

    // Timestamp in the format YYYYMMDDHHMMSS (14 characters)
    $timestamp = Carbon::now()->format('YmdHis');
    logger($timestamp);
    logger($prefix);
    // Calculate the maximum possible length for the random string
    $minRequiredLength = strlen($prefix) + strlen($timestamp) + 2; // 2 is for the hyphens

    // Ensure that the total length requested is sufficient
    if ($length < $minRequiredLength) {
        throw new ValueError('Total length must be greater than ' . $minRequiredLength . ' characters.');
    }

    // Length calculation: subtract prefix and timestamp length from total length
    $randomLength = $length - $minRequiredLength;

    // Generate random string of fixed length
    $randomString = strtoupper(substr(bin2hex(random_bytes(ceil($randomLength / 2))), 0, $randomLength));

    // Final reference with prefix, timestamp, and random string
    $reference = $prefix . '-' . $timestamp . '-' . $randomString;

    return $reference;
}

function userShipperCode()
{
    $timestamp = Carbon::now()->format('YmdHis');
    $randowm = mt_rand(199911, 999999);

    return $timestamp + $randowm;
}
