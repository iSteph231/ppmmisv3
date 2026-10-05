<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LoginSessionTest extends TestCase
{
    public function test_guests_can_view_login_and_receive_a_session_cookie(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertCookie(config('session.cookie'));
    }

    public function test_logged_out_users_are_redirected_to_login_when_returning_to_protected_pages(): void
    {
        $user = new User;
        $user->id = 1;
        $user->role = 'admin';

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();

        foreach (['/dashboard', '/inventory', '/work-requests'] as $page) {
            $this->get($page)->assertRedirect(route('login'));
        }

        $this->get('/login')->assertOk();
    }

    public function test_signed_in_users_return_to_dashboard_when_visiting_login(): void
    {
        foreach (['admin', 'personnel', 'user'] as $role) {
            $user = new User;
            $user->id = 1;
            $user->role = $role;

            $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);
        }
    }
}
