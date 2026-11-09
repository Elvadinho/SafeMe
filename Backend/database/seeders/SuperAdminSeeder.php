<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
  public function run(): void
  {
    $config = config('safeme.super_admin');

    if (blank($config['email'] ?? null)) {
      throw new RuntimeException('SUPER_ADMIN_EMAIL is empty. Set it in your .env file.');
    }

    if (blank($config['password'] ?? null)) {
      throw new RuntimeException('SUPER_ADMIN_PASSWORD is empty. Set it in your .env file.');
    }

    if (strlen($config['password']) < 12) {
      throw new RuntimeException('SUPER_ADMIN_PASSWORD must be at least 12 characters.');
    }

    // firstOrNew + forceFill: running the seeder twice never creates a duplicate,
    // and forceFill is the ONLY place a role is assigned (role is not fillable).
    $user = User::firstOrNew(['email' => $config['email']]);
    $user->forceFill([
      'name'              => $config['name'],
      'password'          => $config['password'], // auto-hashed by the 'hashed' cast
      'role'              => UserRole::SuperAdmin,
      'email_verified_at' => now(),
    ])->save();

    $this->command?->info("Super admin ready: {$user->email}");
  }
}
