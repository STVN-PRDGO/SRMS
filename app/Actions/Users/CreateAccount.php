<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateAccount
{
    /**
     * @param  array{name: string, email: string, password?: string}  $attributes
     */
    public function __invoke(array $attributes, string $roleName): User
    {
        return DB::transaction(function () use ($attributes, $roleName): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'] ?? str()->password(16)->toString(),
                'is_active' => true,
                'must_change_password' => true,
            ]);

            $role = Role::findByName($roleName, 'web');
            $user->assignRole($role);

            return $user;
        });
    }
}
