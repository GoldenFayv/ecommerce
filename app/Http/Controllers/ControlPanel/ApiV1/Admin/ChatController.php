<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Admin;

use App\Events\ChatEvent;
use App\Models\admin\Chat;
use App\Models\admin\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AwsService;
use App\Services\cpanel\ChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ChatService $chatService)
    {
        $admin = Auth::guard("admin")->user();
        $admin_id = $admin->id;
        $chats = Chat::where("sender_chat_id", $admin_id)->orWhere("receiver_chat_id", $admin_id)->get();


        $result = $chats->map(fn($chat_id) => $chatService->getChat($chat_id));
        return $this->successResponse("", $result);
    }

    /**
     * This Updates a user connection id,
     *
     */
    public function updateConnectionId(Request $request)
    {
        /** VERIFY THIS HAS THE BROADCAST TOKEN */
        // $broadcast_key = $request->header("BROADCAST_KEY");
        // if ($broadcast_key == env("AWS_WEBSOCKET_BROADCAST_KEY")) {
        //     $request->validate([
        //         "connection_id" => "required"
        //     ]);
        //     /** @var Admin */
        //     $admin = Auth::guard("admin")->user();
        //     AwsService::setUserConnectionId(Admin::class, $admin->id, $request["connection_id"]);

        //     return $this->successResponse("Connection Id Updated");
        // } else {
        //     return $this->failureResponse("INVALID BROADCAST KEY", 401);
        // }

        /** @var Admin */
        $admin = Auth::guard("admin")->user();
        AwsService::setUserConnectionId(Admin::class, $admin->id, $request["connection_id"]);

        return $this->successResponse("Connection Id Updated");
    }
    public function chattedUsers(ChatService $chatService)
    {
        return $this->successResponse("", $chatService->getChatUser());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ChatService $chatService)
    {

        $request->validate([
            "receiver_chat_id" => "required|exists:admins,chat_id",
            "message" => "required",
            "file" => "image"
        ]);
        $data = $request->all();
        $receiver_chat_id = $request['receiver_chat_id'];
        $admin_chat_id = Auth::guard("admin")->user()->chat_id;
        $data['sender_chat_id'] = $admin_chat_id;

        $data['session_id'] = $this->getChatSessionId($receiver_chat_id, $admin_chat_id);
        $chat = Chat::create($data);

        $receiver_connection_id = Admin::where("chat_id", $receiver_chat_id)->first()->connection_id;
        if ($receiver_connection_id) {
            AwsService::notifyUser($receiver_connection_id, ["sender_chat_id" => $admin_chat_id]);
        }
        // $event = new ChatEvent($receiver_chat_id, $chat->id);
        // event($event);
    }


    private function getChatSessionId($chat_id_1, $chat_id_2)
    {
        // check if this is the first chat
        $chat = Chat::whereRaw("(sender_chat_id = '$chat_id_1' and receiver_chat_id = '$chat_id_2') or (receiver_chat_id = '$chat_id_1' and sender_chat_id = '$chat_id_2')")->first();
        if ($chat) {
            return $chat->session_id;
        } else {
            return strtolower(Str::random(15));
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $recipient_chat_id, ChatService $chatService)
    {
        $admin = Auth::guard("admin")->user();
        $current_admin_chat_id = $admin->chat_id;
        $chats = Chat::whereRaw("sender_chat_id = '$current_admin_chat_id' and receiver_chat_id = '$recipient_chat_id'")->orWhereRaw("sender_chat_id = '$recipient_chat_id' and receiver_chat_id = '$current_admin_chat_id'")->orderBy("created_at", "ASC")->get();

        $chats = array_map(function ($chat_id) use ($chatService) {
            return $chatService->getChat($chat_id);
        }, $chats->toArray());

        $recipient = Admin::where("chat_id", $recipient_chat_id)->first(["first_name", 'last_name', 'profile_picture']);
        $result = [
            "recipient" => [
                "chat_id" => $recipient_chat_id,
                "name" => $recipient->first_name . ", " . $recipient->last_name,
                "profile_picture" => Storage::url("uploads/profile-pictures/" . $recipient->profile_picture)

            ],
            "chats" => $chats
        ];
        return $this->successResponse("", $result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
