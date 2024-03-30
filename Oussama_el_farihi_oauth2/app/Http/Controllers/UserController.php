<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   required={"name", "email", "password"},
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     format="int64",
 *     description="User ID"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     description="User's name"
 *   ),
 *   @OA\Property(
 *     property="email",
 *     type="string",
 *     format="email",
 *     description="User's email"
 *   ),
 *   @OA\Property(
 *     property="password",
 *     type="string",
 *     format="password",
 *     description="User's password"
 *   ),
 *   @OA\Property(
 *     property="role",
 *     type="string",
 *     description="User's role"
 *   )
 * )
 */


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

            /**
 * @OA\Post(
 *     path="/api/users",
 *     summary="Create a new user",
 *     tags={"Users"},
 *     security={{ "apiAuth": {} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "role"},
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string", format="password"),
 *             @OA\Property(property="role", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request"
 *     )
 * )
 */
    public function store(Request $request)
    {

        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string' // Assurez-vous que le rôle soumis est valide
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Attribuer un rôle à l'utilisateur
        $role = Role::where('name', $request->role)->firstOrFail();
        $user->roles()->attach($role);

        return response()->json($user, 201);
    }

    // Lister tous les utilisateurs (Read)
    public function index()
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::with('roles')->get();
        return response()->json($users);
    }

    // Mettre à jour un utilisateur (Update)
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $user->update($request->all());

        return response()->json($user);
    }

    // Supprimer un utilisateur (Delete)
    public function destroy(User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
