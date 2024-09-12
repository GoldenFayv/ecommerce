<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Generates a Unique set of chars
 * @param int $length The Length of Chars to generate
 * @param Model|string $model The Model to Use when checking for duplicate
 * @param string $column The Column to search on in that Table
 */
function generateRef($length = 11, Model|string $model = null, $column = "reference")
{
    $reference = substr(
        rand((int)str_repeat(0, $length * 2), (int)str_repeat(9, $length * 2)),
        0,
        $length
    );
    if ($model) {
        if ($model::where($column, $reference)->exists()) {
            return generateRef($length, $model, $column);
        }
    }

    return $reference;
}
