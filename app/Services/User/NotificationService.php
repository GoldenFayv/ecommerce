<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Models\User\UserNotification;
use App\Services\FirebaseNotification;

class NotificationService
{
    public function create($title, $message, $user_id, $target = null, $target_id = null)
    {
        /**
         * @var User
         */
        $user = User::where("id", $user_id)->first();
        if ($user->fcm_token) {
            $firebaseNotification = new FirebaseNotification();
            $firebaseNotification->sendNotification($title, $message, $user->fcm_token);
        }
        $user->notifications()->create([
            "title" => $title,
            "message" => $message,
            "target" => $target,
            "target_id" => $target_id,
        ]);
    }
    public function getDetails($id = null, $notification = null)
    {
        if (!$notification) {
            $notification = UserNotification::where("id", $id)->first();
        }
        if ($notification) {
            return [
                "title" => $notification["title"],
                "message" => $notification["message"],
                "date" => $notification["created_at"],
            ];
        }
    }
    public function saveUserFcmToken($user_id, $fcm_token)
    {
        User::Where("id", $user_id)->update([
            "fcm_token" => $fcm_token
        ]);
    }
}
