<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ?User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->make();
    }

    public function test_user_access_profile(): void
    {
        $response = $this->get('/profile');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        $this->actingAs($this->user);
        $response = $this->get('/profile');
        $response->assertStatus(200);
    }

    public function test_update_profile(): void
    {
        $oldData = (object) [
            'name' => $this->user->name,
            'email' => $this->user->email,
        ];

        $this->actingAs($this->user);

        $response = $this->put(route('user-profile-information.update'), [
            'name' => $newName = $this->faker->name(),
            'email' => $newEmail = $this->faker->email(),
        ]);
        $response->assertStatus(302);

        $this->user->refresh();

        $this->assertEquals($newName, $this->user->name);
        $this->assertEquals($newEmail, $this->user->email);
        $this->assertNotEquals($oldData->name, $this->user->name);
        $this->assertNotEquals($oldData->email, $this->user->email);

        $response = $this->get(route('profile.edit'));
        $response->assertViewHas('user.name', $this->user->name);
        $response->assertViewHas('user.email', $this->user->email);
    }

    public function test_update_profile_validation(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('user-profile-information.update'), [
            'name' => '',
            'email' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email'], null, 'updateProfileInformation');
    }

    public function test_update_password(): void
    {
        $oldData = (object) [
            'current_password' => 'password',
        ];
        $newPassword = $this->faker->password();

        $this->actingAs($this->user);

        $response = $this->put(route('user-password.update'), [
            'current_password' => $oldData->current_password,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);
        $response->assertStatus(302);

        $this->user = $this->user->fresh();

        $this->assertTrue(Hash::check($newPassword, $this->user->password));
    }

    public function test_update_password_fails_validation(): void
    {
        $oldData = (object) [
            'current_password' => 'password',
        ];
        $newPassword = '';

        $this->actingAs($this->user);

        $response = $this->put(route('user-password.update'), [
            'current_password' => $oldData->current_password,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);
        $response->assertStatus(302);

        $response->assertSessionHasErrors(['password'], null, 'updatePassword');
    }

    public function test_delete_account(): void
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);
        $response->assertStatus(302);

        $this->user->refresh();

        $this->assertGuest();
        $this->assertModelMissing($this->user);
        $this->assertDatabaseEmpty('users');
    }

    public function test_enable_two_factor_auth(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('two-factor.enable'));
        $response->assertStatus(302);

        $this->user->refresh();

        $this->assertNotNull($this->user->two_factor_secret);
        $this->assertNotNull($this->user->two_factor_recovery_codes);
        $this->assertNull($this->user->two_factor_confirmed_at);
        $this->assertIsArray(json_decode(decrypt($this->user->two_factor_recovery_codes), true));
        $this->assertNotNull($this->user->twoFactorQrCodeSvg());
    }

    public function test_disable_two_factor_auth(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('two-factor.enable'));
        $response->assertStatus(302);

        $this->user->refresh();

        $response = $this->delete(route('two-factor.disable'));
        $response->assertStatus(302);

        $this->user->refresh();

        $this->assertNull($this->user->two_factor_secret);
        $this->assertNull($this->user->two_factor_recovery_codes);
        $this->assertNull($this->user->two_factor_confirmed_at);
        $this->assertNull($this->user->two_factor_recovery_codes);
    }
}
