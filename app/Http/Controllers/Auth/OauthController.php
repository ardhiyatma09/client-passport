<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client as Guzzle;

class OauthController extends Controller
{
    protected $client;

    public function __construct(Guzzle $client)
    {
        $this->middleware('auth');
        $this->client = $client;
    }

    public function redirect()
    {
        $query = http_build_query([
            'client_id' => '3',
            'redirect_uri' => 'http://127.0.0.1:8000/auth/passport/callback',
            'response_type' => 'code',
            'scope' => 'view-tweet post-tweet'
        ]);

        return redirect('http://passport.local/oauth/authorize?'. $query);
    }
    public function callback(Request $request)
    {
        // dd($request);
        $response = $this->client->post('http://passport.local/oauth/token',[
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => '3',
                'client_secret' => '9ae0ynEkgYZ9gVXwWwA8sxpRC67UlumKYupgnVEh',
                'redirect_uri' => 'http://127.0.0.1:8000/auth/passport/callback',
                'code' => $request->code,
            ]
        ]);
        $response = json_decode($response->getBody());


        $request->user()->token()->delete();
        // dd($response);
        $request->user()->token()->create([
            'access_token' => $response->access_token,
            'expired_in' => $response->expires_in,
            'refresh_token' => $response->refresh_token
        ]);

        return redirect()->route('home');
    }

    public function refresh(Request $request)
    {
        $response = $this->client->post('http://passport.local/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->user()->token->refresh_token,
                'client_id' => '3',
                'client_secret' => '9ae0ynEkgYZ9gVXwWwA8sxpRC67UlumKYupgnVEh',
                'scope' => 'view-tweet post-tweet'
            ]
        ]);

        $response = json_decode($response->getBody());

        $request->user()->token->update([
            'access_token' => $response->access_token,
            'expired_in' => $response->expires_in,
            'refresh_token' => $response->refresh_token
        ]);

        return redirect()->back();
    }
}
