<?php

namespace App\Exceptions;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Log;

class CustomException extends Exception
{
    /**
     * Custom properties for the exception.
     */
    protected $statusCode;
    protected $err;

    /**
     * Constructor to pass custom message, status code, and optional error array.
     */
    public function __construct($message = "Something went wrong", $statusCode = 500, $err = [])
    {
        // Call the parent constructor to set the message and the status code as 'code'.
        parent::__construct($message, $statusCode);

        // Set the custom error array and status code.
        $this->statusCode = $statusCode;
        $this->err = $err;
    }

    /**
     * Report the exception.
     */
    public function report()
    {
        // Log the error message and additional error details if provided.
        Log::error("CustomException: {$this->message}", [
            'status_code' => $this->statusCode,
            'error_details' => $this->err
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        // Return a failure response from the Controller class
        return (new Controller())->failureResponse(
            $this->message, // Optional message you want to pass
            $this->err,     // The additional error details (if any)
            $this->statusCode // The status code for the error
        );
    }
}
