<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Events\ChatEvent;

class ChatController extends Controller
{

    private function baseQuery()
    {
        return DB::table('chats')->select('id', 'sender_id', 'receiver_id', 'group_id', 'message', 'created_at');
    }

    public function send(Request $request)
    {
        $request->validate([
            'message'       => 'required|string|max:255',
            'receiver_id'   => 'nullable|integer',
            'group_id'      =>   'nullable|integer'
        ]);

        $message_id = $this->baseQuery()->insertGetId([
            'sender_id'     => Auth::user()->id,
            'receiver_id'   => $request->receiver_id,
            'group_id'      => $request->group_id,
            'message'       => $request->message
        ]);

        $message = $this->baseQuery()->find($message_id);
        
        broadcast(new ChatEvent($message))->toOthers();

        return Response::json([
            'message' => $message
        ]);
    }

    public function chat(string $receiver_id)
    {
        $messages = $this->baseQuery()
            ->where([
                ['sender_id', Auth::user()->id],
                ['receiver_id', $receiver_id]
            ])
            ->orWhere([
                ['sender_id', $receiver_id],
                ['receiver_id', Auth::user()->id]
            ])
            ->get();
        return Response::json([
            'messages' => $messages
        ]);
    }
}
