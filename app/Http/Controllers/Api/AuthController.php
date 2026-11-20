<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /** How long a token stays valid before the person must sign in again. */
    private const TOKEN_DAYS = 7;

 
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username'       => 'required|string|max:255',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = $this->findAdmin($request->username, $request->password)
            ?? $this->findStaff($request->username, $request->password);

        if (!$user) {
            Log::info('API login failed', ['username' => $request->username]);

            return response()->json(['message' => 'Phone/email or password is incorrect.'], 422);
        }


        if ($user->isStaff() && !$user->shop_id) {
            return response()->json(['message' => 'Your account is not assigned to a shop yet. Contact your Supervisor.'], 403);
        }

        $type = $user->isStaff() ? 'staff' : 'admin';
        $token = $user->createToken(
            $request->device_name ?: 'mobile',
            [$type],
            now()->addDays(self::TOKEN_DAYS),
        );

        Log::info('API login success', ['user_id' => $user->id, 'type' => $type]);

        return response()->json([
            'message'    => 'Signed in.',
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user'       => new UserResource($this->withRelations($user)),
        ]);
    }

  
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($this->withRelations($request->user())),
        ]);
    }

 
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    /**
     * Admin: email or username, same roles as the web login
     * (role 1/2 or super user).
     */
    private function findAdmin(string $username, string $password): ?Users
    {
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = Users::notStaff()->where($field, $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return (in_array($user->role_id, [1, 2]) || $user->super_user) ? $user : null;
    }

    /** Staff: phone number, as on the web POS login. */
    private function findStaff(string $username, string $password): ?Users
    {
        $user = Users::where('user_type', Users::TYPE_STAFF)->where('phone', $username)->first();

        return $user && Hash::check($password, $user->password) ? $user : null;
    }

    private function withRelations(Users $user): Users
    {
        return $user->loadMissing($user->isStaff() ? ['role.permissions', 'shop'] : ['role.permissions']);
    }
}
