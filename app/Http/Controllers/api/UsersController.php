<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
  /**
   * Display a listing of the resource.
   */

  public function settings()
  {
    $settings = Settings::getSettings();
    return response()->json($settings);
  }

  public function configs()
  {
    $authUser = Auth::user();
    $id = $authUser->id;
    $configs = Config::leftJoin('users', 'configs.assigned_to', '=', 'users.id')
      ->where(function ($query) use ($id) {
        $query->where('users.id', $id)
          ->orWhereNull('configs.assigned_to');
      })
      ->where('configs.active', "true")
      ->select('configs.id', 'configs.title', 'configs.internet_type', 'configs.config', 'configs.order')
      ->get();
    $configs = $configs->sortBy('order');
    return response()->json($configs);
  }

  public function user(Request $request)
  {
    $user = Auth::user();
    $user->token = str_replace('Bearer ', '', $request->header()['authorization'][0]);
    return response()->json($user);
  }
  public function getClientSecret($clientId)
  {
    $client = DB::table('oauth_clients')->where('id', $clientId)->first();
    if ($client) {
      return $client->secret;
    }
    return null;
  }
  public function login(Request $request)
  {
    $request->validate([
      'username' => 'required|string',
      'password' => 'required|string',
    ]);

    $user = User::where('username', $request->username)->first();

    if (!$user || $request->password !== $user->password) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    if (!$user->incrementActiveSessions()) {
      return response()->json(['error' => 'Maximum active sessions reached'], 403);
    }

    $token = $user->createToken('Personal Access Token')->accessToken;
    $user->token = $token;
    return response()->json($user);
  }
  public function logout(Request $request)
  {
    $user = Auth::user();
    if (!$user) {
      return response()->json(['error' => 'Not authenticated'], 401);
    }

    // Find and revoke all tokens for the user
    $tokenString = $request->bearerToken();

    if ($tokenString) {
      $config = Configuration::forSymmetricSigner(
        new \Lcobucci\JWT\Signer\Hmac\Sha256(),
        InMemory::base64Encoded($this->getClientSecret(1))
      );

      $parser = $config->parser();
      $token = $parser->parse($tokenString);
      $tokenId = $token->claims()->get('jti');

      $tokenInstance = $user->tokens()->where('id', $tokenId)->first();
      if ($tokenInstance) {
        $tokenInstance->revoke();
      }
    }


    $user->decrementActiveSessions();

    return response()->json(['message' => 'Successfully logged out']);
  }

  public function index()
  {
    // $settings = Settings::getSettings();
    // return response()->json($settings);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
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

  }
}
