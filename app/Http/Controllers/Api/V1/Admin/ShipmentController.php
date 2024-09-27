<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Enums\ShipmentStatus;
use App\Services\UserService;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    public $userService;
    public $user;

    public function __construct(UserService $userService)  // Inject the service for better testability
    {
        if (Auth::check()) {
            $this->user = Auth::user();
            if (!$this->user->isAdmin) {
                throw new CustomException("Access Denied", 403);
            }
            $this->userService = $userService;
        }

    }

    public function approveShipment($shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);

        if($shipment->status == ShipmentStatus::APPROVED()){
            return $this->successResponse("Shipment Already approved");
        }
        $shipment->status = ShipmentStatus::APPROVED();
        $shipment->save();

        return $this->successResponse('Shipment Approved');
    }

    public function rejectShipment($shipmentId, Request $request)
    {
        $request->validate([
            'reason' => 'nullable'
        ]);

        $shipment = Shipment::findOrFail($shipmentId);

        if($shipment->status == ShipmentStatus::REJECTED()){
            return $this->successResponse("Shipment Already rejected");
        }
        $shipment->status = ShipmentStatus::REJECTED();
        $shipment->save();

        return $this->successResponse('Shipment rejected');
    }

}
