<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class BrowserNotificationScriptTest extends TestCase
{
    public function test_initial_poll_establishes_a_baseline_and_new_requests_create_one_browser_alert(): void
    {
        $this->runBrowserScript(<<<'JS'
    const app = await setup();
    assert.equal(app.notices.length, 0);
    assert.equal(new URL(app.requests[0]).search, '');
    app.replies.push({ user_id: 7, cursor: 11, data: [{ id: 11, title: 'New Work Request', message: 'Repair', url: '/work-requests' }] });
    await app.listeners.poll();
    assert.equal(new URL(app.requests[1]).searchParams.get('after'), '10');
    assert.equal(app.notices.length, 1);
    assert.equal(app.notices[0].options.body, 'Repair');
    app.replies.push({ user_id: 7, cursor: 11, data: [] });
    await app.listeners.poll();
    assert.equal(new URL(app.requests[2]).searchParams.get('after'), '11');
    assert.equal(app.notices.length, 1);
JS);
    }

    public function test_permission_is_requested_only_when_the_admin_clicks_enable(): void
    {
        $this->runBrowserScript(<<<'JS'
    const app = await setup('default');
    assert.equal(app.Notification.permission, 'default');
    assert.equal(app.panel.hidden, false);
    app.replies.push({ user_id: 7, cursor: 10, data: [] });
    await app.listeners.click();
    assert.equal(app.Notification.permission, 'granted');
    assert.equal(app.panel.style.display, 'none');
JS);
    }

    public function test_blocked_permission_shows_instructions_and_does_not_display_alerts(): void
    {
        $this->runBrowserScript(<<<'JS'
    const app = await setup('denied');
    assert.equal(app.button.hidden, true);
    assert.equal(app.panel.hidden, false);
    assert.equal(app.notices.length, 0);
JS);
    }

    public function test_network_failure_keeps_the_cursor_so_the_next_poll_can_retry(): void
    {
        $this->runBrowserScript(<<<'JS'
    const app = await setup();
    app.replies.push(new Error('Offline'));
    await app.listeners.poll();
    app.replies.push({ user_id: 7, cursor: 10, data: [] });
    await app.listeners.poll();
    assert.equal(new URL(app.requests[2]).searchParams.get('after'), '10');
JS);
    }

    public function test_changing_the_signed_in_account_stops_notifications_in_the_old_tab(): void
    {
        $this->runBrowserScript(<<<'JS'
    const app = await setup();
    app.replies.push({ user_id: 8, cursor: 11, data: [{ id: 11, title: 'Private request' }] });
    await app.listeners.poll();
    await app.listeners.poll();
    assert.equal(app.notices.length, 0);
    assert.equal(app.requests.length, 2);
JS);
    }

    private function runBrowserScript(string $scenario): void
    {
        $harness = <<<'JS'
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync('public/js/browser-notifications.js', 'utf8');
const settle = () => new Promise((resolve) => setImmediate(resolve));

async function setup(permission = 'granted') {
    const listeners = {};
    const storage = new Map();
    const requests = [];
    const notices = [];
    const replies = [{ user_id: 7, cursor: 10, data: [] }];
    const button = { hidden: false, addEventListener: (name, callback) => { listeners[name] = callback; } };
    const panel = {
        dataset: { userId: '7', endpoint: 'https://example.test/notifications/browser', icon: '/logo.png' },
        style: {},
        querySelector: (selector) => selector === '[data-enable-notifications]' ? button : {},
    };
    class Notification {
        static permission = permission;
        static async requestPermission() { this.permission = 'granted'; }
        constructor(title, options) { notices.push({ title, options }); }
    }
    const context = {
        document: {
            getElementById: () => panel,
            addEventListener: (name, callback) => { listeners[name] = callback; },
        },
        window: {
            Notification,
            isSecureContext: true,
            location: { href: 'https://example.test/dashboard', origin: 'https://example.test' },
            setInterval: (callback) => { listeners.poll = callback; },
            addEventListener: () => {},
        },
        navigator: { onLine: true },
        localStorage: { getItem: (key) => storage.get(key) ?? null, setItem: (key, value) => storage.set(key, value) },
        Notification, URL, AbortSignal,
        fetch: async (url) => {
            requests.push(String(url));
            const payload = replies.shift();
            if (payload instanceof Error) throw payload;
            return { ok: true, status: 200, json: async () => payload };
        },
    };
    vm.runInNewContext(source, context);
    listeners.DOMContentLoaded();
    await settle();
    return { listeners, requests, notices, replies, panel, button, Notification, storage };
}


JS;
        $process = new Process(['node', '-e', $harness."\n(async () => {\n".$scenario."\n})().catch(error => { console.error(error); process.exitCode = 1; });"], base_path());
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
    }
}
