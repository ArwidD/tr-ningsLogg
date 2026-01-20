<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function protected(request $request)
    {
        $retur = [
            'Message' => "Detta är en rutt som man behöver vara inloggad för",
            'jwt' => $request->attributes->get('jwt_payload'),
            'user' => $request->user()
        ];

         return response()->json($retur, 200);
    }

    public function open(Request $request)
    {
        $retur=['message' => 'detta är en rutt som man inte behöver logga in för att komma åt'];

        if($request->user) {
            $retur['user'] = $request->user()->tojson();
        }
        return response()->json($retur, 200);
    }
}
