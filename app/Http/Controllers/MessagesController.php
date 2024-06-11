<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

class MessagesController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
            'receiver_id' => 'nullable|integer',
            'group_id' => 'nullable|integer'
        ]);

        $message_id = DB::table('messages')->insertGetId([
            'sender_id' => Auth::user()->id,
            'receiver_id' => $request->receiver_id,
            'group_id' => $request->group_id,
            'message' => $request->message
        ]);

        $message = DB::table('messages')->find($message_id);
        
        broadcast(new MessageSent($message))->toOthers();

        // MessageSent::dispatch($message);

        return Response::json([
            'message' => $message
        ]);
    }

    public function chat(string $receiver_id)
    {
        $messages = DB::table('messages')
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
