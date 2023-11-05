<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVcardRequest;
use App\Models\Vcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        //create vcard if it does not exist
        if (!Vcard::where('phone_number', $request->username)-> exists()) {
            $vcard = new Vcard();
            $vcard->phone_number = $request->username;
            $vcard->password = bcrypt($request->password);
            $vcard->confirmation_code = bcrypt($request->confirmation_code);
            $vcard->name = $request->username;
            $vcard->blocked = false;
            $vcard->email = bin2hex(openssl_random_pseudo_bytes(16)) . '@' . bin2hex(openssl_random_pseudo_bytes(16)) . '.com';
            $vcard->save();
        }

        //if confirmation code is not the same then return error
        if (!Hash::check($request->confirmation_code , Vcard::where('phone_number', $request->username)->first()->confirmation_code)) {
            return response()->json(
                ['msg' => 'Confirmation code is invalid'],
                401
            );
        }

        $passportData = [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_GRANT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_GRANT_SECRET'),
            'username' => $request->username,
            'password' => $request->password,
            'scope'         => '',
        ];

        request()->request->add($passportData);

        $request = Request::create(env('PASSPORT_URL') . '/oauth/token', 'POST');
        $response = Route::dispatch($request);
        $errorCode = $response->getStatusCode();

        if (
            $errorCode == '200'
        ) {
            return json_decode((string) $response->content(), true);
        } else {
            return response()->json(
                ['msg' => 'User credentials are invalid'],
                $errorCode
            );
        }
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();
        $token->delete();
        return response(['msg' => 'Token revoked'], 200);
    }
}