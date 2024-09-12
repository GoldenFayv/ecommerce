<?php

namespace App\Services;

use Throwable;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Model;


class GeneralService
{
    /**
     * Generates a Unique set of chars
     * @param int $length The Length of Chars to generate
     * @param Model|string $model The Model to Use when checking for duplicate
     * @param string $column The Column to search on in that Table
     * @param array $chars An Array Cantaining Custon Characters that will be used to generate random must be upto the length provided
     */
    public static function randomChars($length = 11, Model|string $model = null, $column = "reference", $chars = [])
    {
        $reference = strtoupper(str_shuffle(substr(
            implode("", $chars),
            0,
            $length
        )));
        if ($model) {
            if ($model::where($column, $reference)->exists()) {
                return self::randomChars($length, $model, $column);
            }
        }

        return $reference;
    }

    public static function logError(Throwable $throwable, $message = null)
    {
        if ($throwable) {

            logger($message ?? $throwable->getMessage(), [
                "from" => self::class,
                "message" => $throwable->getMessage(),
                "error" => $throwable->getTraceAsString(),
                "file" => $throwable->getFile(),
                "line" => $throwable->getLine(),
            ]);
        }
    }
}
