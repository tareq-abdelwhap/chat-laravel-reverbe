<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function index()
    {
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->whereNot('id', Auth::user()->id)
            ->get();
        return Response::json([
            'users' => $users
        ]);
    }
}
