<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class RequestUpdatesScriptTest extends TestCase
{
    public function test_unchanged_content_and_animation_do_not_replace_the_list(): void
    {
        $script = <<<'JS'
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
let refresh;
let ready;
let replacements = 0;
let focused = false;
let serverContent = 'pending';

function region(content, animation = '') {
    return {
        content, animation,
        dataset: { requestUpdates: 'facility-list' },
        get innerHTML() { return this.content + this.animation; },
        get childNodes() { return [{ content: this.content }]; },
        cloneNode() { return region(this.content, this.animation); },
        contains() { return focused; },
        querySelectorAll(selector) {
            if (selector !== '[data-new-request-expires]') return [];
            const copy = this;
            return [{
                removeAttribute() {},
                querySelectorAll() {
                    return [{ removeAttribute() { copy.animation = ''; } }];
                },
            }];
        },
        replaceChildren(node) {
            replacements++;
            this.content = node.content;
            this.animation = '';
        },
    };
}
const live = region('pending', 'initial animation');
const context = {
    document: {
        hidden: false,
        querySelector: () => live,
        querySelectorAll: () => [live],
        addEventListener(name, callback) { if (name === 'DOMContentLoaded') ready = callback; },
    },
    navigator: { onLine: true },
    window: { location: { href: 'https://example.test/requests' }, addEventListener() {}, setInterval(callback) { refresh = callback; } },
    DOMParser: class { parseFromString() { return { querySelectorAll: () => [region(serverContent)] }; } },
    AbortSignal,
    fetch: async () => ({ ok: true, status: 200, headers: { get: () => 'text/html' }, text: async () => '' }),
};
vm.runInNewContext(fs.readFileSync('public/js/request-updates.js', 'utf8'), context);
ready();
(async () => {
    live.animation = 'changed animation';
    await refresh();
    await refresh();
    assert.equal(replacements, 0, 'animation must not refresh unchanged records');
    serverContent = 'approved';
    await refresh();
    assert.equal(replacements, 1, 'actual status changes must update the records');
    live.animation = 'another animation frame';
    await refresh();
    assert.equal(replacements, 1, 'unchanged server content must preserve the DOM');
    focused = true;
    serverContent = 'finished';
    await refresh();
    assert.equal(replacements, 1, 'focused controls must not be replaced');
    focused = false;
    await refresh();
    assert.equal(replacements, 2, 'deferred updates must apply after editing');
})().catch(error => { console.error(error); process.exitCode = 1; });
JS;
        $process = new Process(['node', '-e', $script], base_path());
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
    }
}
