<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAndPermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_admin_user_with_correct_credentials_and_roles(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::query()->where('email', 'smprodigo@up.edu.ph')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('smprodigo@up.edu.ph', $admin->password));
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->must_change_password);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_rerunning_seeder_updates_existing_admin_and_maintains_roles(): void
    {
        $admin = User::factory()->create([
            'email' => 'smprodigo@up.edu.ph',
            'password' => 'temporary-password',
            'is_active' => false,
            'must_change_password' => true,
        ]);

        $this->seed(RoleAndPermissionSeeder::class);

        $admin->refresh();

        $this->assertTrue(Hash::check('smprodigo@up.edu.ph', $admin->password));
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->must_change_password);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole('super_admin'));
    }
}
