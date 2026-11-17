<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class RouteConfigurationTest extends TestCase
{
    public function test_every_application_route_points_to_an_existing_public_method(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (! str_starts_with($action, 'App\\') || ! str_contains($action, '@')) {
                continue;
            }
            [$controller, $method] = explode('@', $action, 2);
            $this->assertTrue(method_exists($controller, $method), $route->uri().' targets missing '.$action);
            $this->assertTrue((new ReflectionMethod($controller, $method))->isPublic(), $action);
        }
    }

    public function test_route_names_can_be_compiled_without_duplicates(): void
    {
        $this->assertNotEmpty(Route::getRoutes()->compile());
    }

    public function test_browser_dashboard_stats_keep_session_authentication(): void
    {
        $route = Route::getRoutes()->match(Request::create('/api/dashboard/stats'));
        $this->assertSame(DashboardController::class.'@getStats', $route->getActionName());
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertNotContains('auth:sanctum', $route->gatherMiddleware());
        $this->getJson('/api/dashboard/stats')->assertUnauthorized();
    }

    public function test_guest_cannot_schedule_an_inspection_or_read_notifications(): void
    {
        $this->put(route('work-requests.schedule', 1))->assertRedirect(route('login'));
        $this->getJson(route('notifications.index'))->assertUnauthorized();
        $this->getJson(route('notifications.browser'))->assertUnauthorized();
    }

    public function test_disabled_actions_still_have_explicit_rejection_endpoints(): void
    {
        foreach (['work-requests.edit', 'work-requests.update', 'maintenance.destroy', 'settings.profile'] as $name) {
            $this->assertTrue(Route::has($name), $name);
        }
        foreach (['maintenance.edit', 'maintenance.update', 'work-requests.update-status', 'notifications.destroy', 'users.show', 'users.toggle-status', 'users.reset-password', 'settings.notifications', 'settings.system'] as $name) {
            $this->assertFalse(Route::has($name), $name);
        }
    }
}
