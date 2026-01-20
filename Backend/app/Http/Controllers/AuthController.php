<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Storage\UserRepository;
use App\Services\TotpService;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\cookie;

class AuthController extends Controller {

    public function __construct(private UserRepository $repo, private TotpService $totpService,
    private JwtService $jwtService) {

    }

    public function login(Request $request) {
        $email=filter_var($request->input('email'), FILTER_VALIDATE_EMAIL);
        $code=filter_var($request->input('code'), FILTER_VALIDATE_INT);

        //hämta användare via epost
        $user=$this->repo->getUserByEmail($email);
        if(!$user) {
            return response()->json(['error'=>'invalid credentials'], 401);
        }

        //verifiera code
        $totpValid=$this->totpService->verify($user->secret, $code);
        if(!$totpValid){
            return response()->json(['error'=>'invalid credentials'], 401);
        }
        $accessToken=$this->jwtService->createAccessToken($user->id);
        $refreshToken=$this->jwtService->createRefreshToken();
        $expiresAt=\Carbon\Carbon::now()->addDays(30)->format("y-m-d H:i:s");

        //spara refresh token i databasen
        $this->repo->saveRefreshToken($user->id, $refreshToken, $expiresAt);


        //sätt cookies
        $cookie=\Symfony\Component\HttpFoundation\Cookie::create(
        'refresh_token',
        $refreshToken,
        $expiresAt,
        'refresh',
        null,
        true,
        true,
        false,
        'lax'
    );
        return response()->json([
        'access_token'=>$accessToken,
        'token_type'=>'Bearer',
        'expires_in'=>900,
        '$user'=>[
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email
        ]
        ])->withCookie($cookie);
    }

    public function refresh(Request $request) {
        //läs refreshtoken från cookie
        $refreshToken=$request->cookie('refresh_token');

        if(!$refreshToken) {
            return response()->json(['error'=>'missing refreshtoken'], 401);
        }

        //kontrollera att angivet token finns i databasen
        $user=$this->repo->getUserByRefreshToken($refreshToken);
        if(!$user) {
            return response()->json(['error'=>'invalid refreshtoken (no user)']);
        }
        //skapa nya tokens för användaren
        $accessToken=$this->jwtService->createAccessToken($user->id);
        $newRefreshToken=$this->jwtService->createRefreshToken();
        $expiresAt=\Carbon\Carbon::now()->addDays(30)->format("y-m-d H:i:s");
        $this->repo->saveRefreshToken($user->id, $newRefreshToken, $expiresAt);

        //sätt cookies
        $cookie=\Symfony\Component\HttpFoundation\Cookie::create(
        'refresh_token',
        $newRefreshToken,
        $expiresAt,
        'refresh',
        null,
        true,
        true,
        false,
        'lax'
        );

        return response()->json([
        'access_token'=>$accessToken,
        'token_type'=>'Bearer',
        'expires_in'=>900,
        '$user'=>[
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email
        ]
        ])->withCookie($cookie);
    }
    }

