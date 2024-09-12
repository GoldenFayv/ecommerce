<?php

namespace App\Services;

use WebSocket\Client;

class AwsService
{
    /**
     * @param string $model Model To Where this User Is Saved eg `App\Model\User::class`
     * @param string $id ID of this user in the model eg `1`
     * @param string $socket_id Key The Socket Id
     * @param string $socket_id_key Key Holding the Socket Id in the Model|Table
     */
    public static function setUserConnectionId(string $model, $id, $socket_id, $socket_id_key = "aws_connection_id")
    {
        $model::where("id", $id)->update([
            $socket_id_key => $socket_id
        ]);
    }

    public static function notifyUser($connection_id, $payload = null, $action = null)
    {
        $client = new Client(env("AWS_WEBSOCKET"));
        $client->send(json_encode([
            "key" => env("AWS_WEBSOCKET_BROADCAST_KEY"),
            "action" => $action ?? "newMessageAlert",
            "connectionId" => $connection_id,
            ...($payload ? $payload : [])
        ]));
        $client->disconnect();
    }
}
