<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetRequirementsTest extends TestCase
{
    public function test_short_or_unconfirmed_passwords_are_rejected(): void
    {
        foreach ([['short', 'short'], ['abcdefgh', 'different']] as [$password, $confirmation]) {
            $this->post('/reset-password', [
                'token' => 'test-token',
                'email' => 'test@psu.edu.ph',
                'password' => $password,
                'password_confirmation' => $confirmation,
            ])->assertSessionHasErrors('password');
        }
    }

    public function test_eight_character_password_is_accepted_without_optional_strength_characters(): void
    {
        Password::shouldReceive('reset')->once()->andReturn(Password::INVALID_TOKEN);

        $this->post('/reset-password', [
            'token' => 'test-token',
            'email' => 'test@psu.edu.ph',
            'password' => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ])->assertSessionDoesntHaveErrors('password')->assertSessionHasErrors('email');
    }
}
