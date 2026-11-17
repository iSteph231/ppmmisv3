<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsPasswordFeedbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    public function test_successful_password_change_displays_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'Original123']);
        $this->actingAs($user)->from(route('settings.index'))
            ->put(route('settings.password'), [
                'current_password' => 'Original123',
                'password' => 'Updated123',
                'password_confirmation' => 'Updated123',
            ])->assertRedirect(route('settings.index'))->assertSessionHas('success', 'Password changed successfully!');

        $this->get(route('settings.index'))->assertOk()
            ->assertSee('role="status"', false)->assertSee('Password changed successfully!');
        $this->assertTrue(Hash::check('Updated123', $user->fresh()->password));
    }

    public function test_profile_is_disabled_and_profile_updates_are_rejected(): void
    {
        $user = User::factory()->create();
        $original = $user->only(['name', 'email', 'department', 'phone_number']);
        $html = $this->actingAs($user)->get(route('settings.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<input[^>]*id="profile-name"[^>]*disabled>/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*id="profile-email"[^>]*disabled>/', $html);
        $this->assertStringNotContainsString('Update Profile', $html);

        $this->put(route('settings.profile'), [
            'name' => 'Changed Name', 'email' => 'changed@example.com',
            'department' => 'Changed Department', 'phone_number' => '09123456789',
        ])->assertForbidden();
        $this->assertSame($original, $user->fresh()->only(array_keys($original)));
    }

    public function test_incorrect_current_password_displays_failure_without_changing_password(): void
    {
        $user = User::factory()->create(['password' => 'Original123']);
        $this->actingAs($user)->from(route('settings.index'))
            ->put(route('settings.password'), [
                'current_password' => 'Incorrect123',
                'password' => 'Updated123',
                'password_confirmation' => 'Updated123',
            ])->assertRedirect(route('settings.index'))->assertSessionHasErrors('current_password');

        $this->view('settings.index', ['errors' => session('errors')])
            ->assertSee('role="alert"', false)->assertSee('Password was not changed.')
            ->assertSee('Current password is incorrect.')->assertDontSee('Password changed successfully!');
        $this->assertTrue(Hash::check('Original123', $user->fresh()->password));
    }

    public function test_invalid_new_password_displays_validation_feedback(): void
    {
        $user = User::factory()->create(['password' => 'Original123']);
        $this->actingAs($user);

        foreach ([['short', 'short'], ['Updated123', 'Different123'], ['', '']] as [$password, $confirmation]) {
            $this->from(route('settings.index'))->put(route('settings.password'), [
                'current_password' => 'Original123',
                'password' => $password,
                'password_confirmation' => $confirmation,
            ])->assertRedirect(route('settings.index'))->assertSessionHasErrors('password');

            $this->view('settings.index', ['errors' => session('errors')])
                ->assertSee('Password was not changed.')->assertDontSee('Password changed successfully!');
            $this->assertTrue(Hash::check('Original123', $user->fresh()->password));
        }
    }
}
