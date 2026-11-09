<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Super admin only: manage moderator / police accounts. */
class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->users->list($request->query('role'), $request->query('search'))
        );
    }

    public function storeAdmin(StoreAdminRequest $request): JsonResponse
    {
        return response()->json(['user' => $this->users->createAdmin($request->validated())], 201);
    }

    public function updateRole(UpdateRoleRequest $request, User $user): JsonResponse
    {
        $user = $this->users->changeRole($user, UserRole::from($request->input('role')), $request->user());

        return response()->json(['user' => $user]);
    }
}
