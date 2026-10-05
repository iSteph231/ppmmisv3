<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    public function test_emailed_reset_token_is_hashed_and_can_reset_password_only_once(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/0001_01_01_000000_create_users_table.php',
            '--no-interaction' => true,
        ])->assertExitCode(0);

        $user = User::create(User::factory()->make(['email' => 'test@psu.edu.ph'])->only(['name', 'email', 'password']));
        $token = null;
        Mail::shouldReceive('send')->once()->withArgs(function ($view, $data, $callback) use (&$token): bool {
            $token = basename(parse_url($data['resetLink'], PHP_URL_PATH));

            return $view === 'emails.password-reset';
        });

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        $storedToken = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');
        $this->assertNotSame($token, $storedToken);
        $this->assertTrue(Hash::check($token, $storedToken));

        $credentials = [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ];
        $this->post('/reset-password', $credentials)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($credentials['password'], $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->post('/reset-password', $credentials)->assertSessionHasErrors('email');
    }
}
