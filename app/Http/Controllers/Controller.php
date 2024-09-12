<?php

namespace App\Http\Controllers;

use App\Mail\UserMail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function successResponse($message, $data = null, $status = 200)
    {
        if (str_contains(strtolower($message), "created")) {
            $status = 201;
        }
        return response()->json(
            [
                'status' => true,
                'message' => $message,
                'data' => $data
            ],
            $status
        );
    }
    public function failureResponse($message, $errors = null, $status = 400, \Throwable $th = null)
    {
        if (str_contains(strtolower($message), "not found")) {
            $status = 404;
        }
        if ($th) {
            logger(
                $message,
                [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                ]
            );
        }
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'errors' => $errors
            ],
            $status
        );
    }

    /**
     * @param string $email
     * @param string $view
     * @param array $data
     */
    public function sendMail($email, $subject, $view, $data)
    {
        try {
            //code...
            Mail::to($email)->send(new UserMail($subject, $data, $view));
        } catch (\Throwable $th) {
            $this->failureResponse('', null, 500, $th);
        }
    }

    public function uploadFile(UploadedFile $image, $path)
    {
        $filename = rand(00000000, 99999999) . '.' . $image->getClientOriginalExtension();
        $destination = $path . $filename;
        $image->storeAs($destination);

        return $filename;
    }


}
enum Otp: string
{
    case AccountActivation = 'account_activation';
    case AccountDeletion = 'account_deletion';
    case EmailVerification = 'email_verification';
}
;
