<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\PriorityLevel;
use App\Enums\ShippingMode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    public function register_shipment(Request $request)
    {
        $request->validate([
            'shipment_date' => ['required', 'after:'. now()->addWeek(), 'date'],
            'shipment_mode' => ['required', 'in:'. implode(',', ShippingMode::getValues())],
            'priority_level' => ['required', 'in'. implode(',', PriorityLevel::getValues())],
            'package_description' => 'required',
            'sender_name' => 'required',
            'sender_email' => 'required|email',
            'sender_number' => 'required|regex:/^\d+$',
            'pickup_address' => 'required',
            'pickup_date_time' => 'date|nullable',
            'pickup_instruction' => 'nullable'

        ]);
        $user  = Auth::user();
    }
}
