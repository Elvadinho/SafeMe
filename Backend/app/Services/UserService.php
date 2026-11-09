<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** User management, used by the super admin only. */
class UserService
{
    public function list(?string $role, ?string $search): LengthAwarePaginator
    {
        return User::query()
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);
    }

    public function createAdmin(array $data): User
    {
        $user = new User($data);
        $user->forceFill(['role' => UserRole::Admin, 'email_verified_at' => now()])->save();

        return $user;
    }

    public function changeRole(User $target, UserRole $role, User $actor): User
    {
        // The super admin account can never be changed through the API.
        abort_if($target->role === UserRole::SuperAdmin, 403, 'The super admin role cannot be changed.');
        abort_if($target->is($actor), 403, 'You cannot change your own role.');

        $target->forceFill(['role' => $role])->save();

        // A demoted admin loses access immediately: delete their login tokens.
        if ($role === UserRole::Citizen) {
            $target->tokens()->delete();
        }

        return $target;
    }
}
