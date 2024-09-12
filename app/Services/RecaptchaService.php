<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function tokenIsValid($token)
    {
        $secret = env("reCAPTCHA");
        try {
            /**
             * Success Response Sample
             * ```json
             * {
             *  "success": true,
             *  "challenge_ts": "2023-11-02T12:04:23Z",
             *  "hostname": "www.naijakobomarket.com"
             * }
             * ```
             */
            $response = Http::withoutVerifying()->post("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token")->json();
            if ($response) {
                if ($response["success"] == true) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
