<?php

namespace App\Services;

use Google\Auth\CredentialsLoader;
use Illuminate\Support\Facades\Http;

class FirebaseNotification
{
    public function getFirebaseAccessToken()
    {

        $scope = 'https://www.googleapis.com/auth/firebase.messaging';
        $credentials = CredentialsLoader::makeCredentials($scope, json_decode(file_get_contents(
            base_path(env("FIREBASE_DETAILS_FILE_PATH"))
        ), true));

        $token = $credentials->fetchAuthToken();
        return $token["access_token"];
    }
    public function sendNotification($title, $message, $fcm_token)
    {
        $project_id = env("FIREBASE_PROJECT_ID");
        $headers = [
            "Authorization" => "Bearer " . $this->getFirebaseAccessToken()
        ];
        $request = Http::withoutVerifying()->withHeaders($headers)->post("https://fcm.googleapis.com/v1/projects/{$project_id}/messages:send", [
            "message" => [
                "token" => $fcm_token,
                "notification" => [
                    "body" => $message,
                    "title" => $title
                ]
            ]
        ])->json();
    }
}
