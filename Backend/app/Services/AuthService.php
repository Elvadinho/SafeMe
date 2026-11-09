<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /** Creates a citizen account and returns [user, token]. */
    public function register(array $data): array
    {
        // 'role' is not fillable, so the database default ("citizen") is always used.
        $user = User::create($data)->refresh();

        return [$user, $this->createToken($user)];
    }

    /** Checks email + password and returns [user, token]. */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        // Same message for "unknown email" and "wrong password":
        // an attacker must not learn which emails exist.
        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        return [$user, $this->createToken($user)];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    private function createToken(User $user): string
    {
        // The token expires after 7 days; the user simply logs in again.
        return $user->createToken('safeme', ['*'], now()->addDays(7))->plainTextToken;
    }
}
