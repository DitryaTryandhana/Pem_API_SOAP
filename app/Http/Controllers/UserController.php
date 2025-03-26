<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="User API",
 *     version="1.0",
 *     description="API untuk manajemen pengguna"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register user baru",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User berhasil dibuat"),
     *     @OA\Response(response=400, description="Input tidak valid")
     * )
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json($user, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login berhasil, token dikembalikan"),
     *     @OA\Response(response=401, description="Kredensial salah")
     * )
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user->token = Str::uuid()->toString();
        $user->save();

        return response()->json($user);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Ambil data user yang sedang login",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Data user yang sedang login"),
     *     @OA\Response(response=401, description="User belum login")
     * )
     */
    public function get(): JsonResponse
    {
        return response()->json(Auth::user());
    }

    /**
     * @OA\Put(
     *     path="/api/user/update",
     *     summary="Update data user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="newuser123"),
     *             @OA\Property(property="email", type="string", example="newemail@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User berhasil diperbarui"),
     *     @OA\Response(response=401, description="User belum login")
     * )
     */
    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $user->update($request->validated());
        return response()->json($user);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User berhasil logout"),
     *     @OA\Response(response=401, description="User belum login")
     * )
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();
        $user->token = null;
        $user->save();

        return response()->json(['message' => 'Logged out'], 200);
    }
}
