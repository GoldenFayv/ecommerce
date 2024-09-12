<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RecaptchaService;
use Symfony\Component\HttpFoundation\Response;

class RecaptchaChecker
{
    /**
     * This Verifies Routes That requires recaptcha
     *
     * @param Request $request
     * @param \Closure $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * Skip CAPTCHA if it's from Mobile
         */
        $mobile_key = env("mobileCaptchaToken");
        $request_token_pass = $request["captchaToken"] ?? null;
        if ($request['signup_platform'] == "Mobile" && $request_token_pass == $mobile_key) {
            return $next($request);
        } else {
            $request->validate([
                "captchaToken" => "required|string"
            ]);
            $recaptchaService = new RecaptchaService();
            if ($recaptchaService->tokenIsValid($request["captchaToken"])) {
                return $next($request);
            } else {
                return response()->json(
                    [
                        "status" => false,
                        "message" => "Invalid Recaptcha Response"
                    ],
                    401
                );
            }
        }
    }
}
