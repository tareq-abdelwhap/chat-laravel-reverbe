<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Events\OnlineStatus;

class UsersController extends Controller
{
    public function index()
    {
        $authId = Auth::user()->id;

        $users = DB::table('friend_user')
            ->join('users as user', function ($join) {
                $join->on('friend_user.user_id', '=', 'user.id');
            })
            ->join('users as friend', function ($join) {
                $join->on('friend_user.friend_id', '=', 'friend.id');
            })
            ->select(
                'friend_user.user_id',
                DB::raw('JSON_ARRAYAGG(DISTINCT JSON_OBJECT(
                    "id",       IF(friend_user.user_id = '.$authId.', friend.id, user.id),
                    "name",     IF(friend_user.user_id = '.$authId.', friend.name, user.name),
                    "email",    IF(friend_user.user_id = '.$authId.', friend.email, user.email),
                    "accepted", friend_user.accepted
                )) as friends')
            )
            ->where(fn ($query) => 
                $query->where([
                    ['friend_user.user_id', $authId],
                    ['friend_user.accepted', true],
                ])->orWhere('friend_user.friend_id', $authId)
            )
            ->groupBy('friend_user.user_id')
            ->first();
        
        return Response::json([
            'users' => json_decode($users->friends ?? '[]')
        ]);
    }

    public function update(Request $request, $user_id)
    {
        $request->validate(['accepted' => 'required|boolean']);

        DB::table('friend_user')
            ->where([
                ['friend_id', Auth::user()->id],
                ['user_id', $user_id]
            ])
            ->update([
                'accepted' => $request->accepted,
                'updated_at' => now()
            ]);
        
        broadcast(new OnlineStatus(Auth::user()))->toOthers();

        return Response::json(['message' => 'Friend request updated']);
    }

    public function search(Request $request)
    {
        $authId = Auth::user()->id;
        $users = DB::table('users')
            ->leftJoin('friend_user', function ($join) {
                $join->on('friend_user.friend_id', '=', 'users.id');
            })
            ->where(fn ($query) => 
                $query->where('users.name', 'like', '%'.$request->searchQuery.'%')
                    ->orWhere('users.email', $request->searchQuery)
            )
            ->whereNot('users.id', Auth::user()->id)
            ->whereNotIn(
                'users.id', 
                DB::table('friend_user')
                    ->where(fn ($query) => $query->where('user_id', $authId)->orWhere('friend_id', $authId))
                    ->where('accepted', true)
                    ->select(DB::raw('IF(friend_id = '.$authId.', user_id, friend_id) as friend_id'))
                    ->pluck('friend_id')
            )
            ->select('users.id', 'users.name', 'users.email', DB::raw('IF(friend_user.friend_id, true, false) as sent'))
            ->get();
        

        return Response::json([
            'users' => $users
        ]);
    }


    public function add(Request $request)
    {
        $request->validate(['friend_id' => 'required']);
        $authId = Auth::user()->id;
        DB::table('friend_user')->insert([
            'user_id' => $authId,
            'friend_id' => $request->friend_id,
            'accepted' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        broadcast(new OnlineStatus([...Auth::user()->toArray(), "accepted" => false]))->toOthers();

        return Response::json(['message' => 'Friend request sent']);
    }
}
