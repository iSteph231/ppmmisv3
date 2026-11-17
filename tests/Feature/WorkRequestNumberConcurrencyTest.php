<?php

namespace Tests\Feature;

use App\Models\WorkRequest;
use App\Services\WorkRequestNumberAllocator;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class WorkRequestNumberConcurrencyTest extends TestCase
{
    private ?PDO $server = null;

    private ?string $databaseName = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('MYSQL_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Opt in with MYSQL_CONCURRENCY=1 against a disposable local MySQL server.');
        }
        $this->server = new PDO('mysql:host=127.0.0.1;port='.(getenv('MYSQL_TEST_PORT') ?: '33079'), getenv('MYSQL_TEST_USERNAME') ?: 'root', getenv('MYSQL_TEST_PASSWORD') ?: '');
        $this->databaseName = 'ppmmis_concurrency_'.bin2hex(random_bytes(6));
        $this->server->exec('CREATE DATABASE `'.$this->databaseName.'`');
        config(['database.default' => 'mysql', 'database.connections.mysql' => [
            'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => getenv('MYSQL_TEST_PORT') ?: '33079',
            'database' => $this->databaseName, 'username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
            'password' => getenv('MYSQL_TEST_PASSWORD') ?: '', 'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true, 'engine' => 'InnoDB',
        ]]);
        DB::purge('mysql');
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_062233_create_work_requests_table.php',
            '2026_11_17_155548_create_work_request_counters_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        try {
            DB::disconnect('mysql');
            if ($this->databaseName !== null) {
                $this->server->exec('DROP DATABASE `'.$this->databaseName.'`');
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_ten_concurrent_creations_bootstrap_existing_numbers_and_start_a_new_month(): void
    {
        $this->assertStringStartsWith('8.', DB::selectOne('select version() as version')->version);
        foreach ([73, 8, 19] as $number) {
            WorkRequest::create(['request_number' => 'WR-202611-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT), 'title' => 'Existing request', 'description' => 'Existing request']);
        }
        WorkRequest::where('request_number', 'WR-202611-0073')->firstOrFail()->delete();
        $this->runConcurrentCreations('202611', 74);
        $this->runConcurrentCreations('202611', 84);
        $this->runConcurrentCreations('202612', 1);
        $this->assertSame(33, DB::table('work_requests')->count());
        $this->assertSame(93, DB::table('work_request_counters')->where('month', '202611')->value('last_number'));
        $this->assertSame(10, DB::table('work_request_counters')->where('month', '202612')->value('last_number'));
    }

    public function test_rollback_releases_the_number_and_exhaustion_preserves_four_digits(): void
    {
        try {
            DB::transaction(function (): void {
                app(WorkRequestNumberAllocator::class)->allocate('202611');
                throw new \RuntimeException('Rollback');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Rollback', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('work_request_counters')->count());
        $this->assertSame('WR-202611-0001', DB::transaction(fn (): string => app(WorkRequestNumberAllocator::class)->allocate('202611')));
        DB::table('work_request_counters')->where('month', '202611')->update(['last_number' => 9999]);
        $this->expectExceptionMessage('The monthly work request number range is exhausted.');
        DB::transaction(fn (): string => app(WorkRequestNumberAllocator::class)->allocate('202611'));
    }

    private function runConcurrentCreations(string $month, int $firstNumber): void
    {
        $gate = tempnam(sys_get_temp_dir(), 'wr-gate-');
        unlink($gate);
        $processes = [];
        $worker = <<<'PHP'
        require 'vendor/autoload.php';
        $payload = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
        $app = require 'bootstrap/app.php';
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        config(['database.default' => 'mysql', 'database.connections.mysql' => $payload['connection']]);
        Illuminate\Support\Facades\DB::purge('mysql');
        Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "ready\n";
        flush();
        $deadline = microtime(true) + 30;
        while (! is_file($payload['gate'])) {
            if (microtime(true) > $deadline) { throw new RuntimeException('Barrier timed out'); }
            usleep(10000);
            clearstatcache();
        }
        $number = Illuminate\Support\Facades\DB::transaction(function () use ($payload): string {
            $number = app(App\Services\WorkRequestNumberAllocator::class)->allocate($payload['month']);
            usleep(20000);
            App\Models\WorkRequest::create(['request_number' => $number, 'title' => 'Concurrent request', 'description' => 'Concurrent request']);
            return $number;
        }, 5);
        echo $number;
        PHP;
        try {
            for ($index = 0; $index < 10; $index++) {
                $process = new Process([PHP_BINARY, '-r', $worker], base_path(), ['APP_ENV' => 'testing']);
                $process->setInput(json_encode(['connection' => config('database.connections.mysql'), 'month' => $month, 'gate' => $gate], JSON_THROW_ON_ERROR));
                $process->setTimeout(60);
                $process->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 30;
            do {
                $ready = 0;
                foreach ($processes as $process) {
                    $this->assertTrue($process->isRunning(), $process->getErrorOutput());
                    $ready += str_contains($process->getOutput(), 'ready') ? 1 : 0;
                }
                if (microtime(true) > $deadline) {
                    $this->fail('Workers did not reach the concurrency barrier.');
                }
                usleep(10000);
            } while ($ready < 10);
            touch($gate);
            $numbers = [];
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput());
                $numbers[] = trim(explode("\n", $process->getOutput())[1]);
            }
            sort($numbers);
            $this->assertSame(array_map(fn (int $number): string => 'WR-'.$month.'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT), range($firstNumber, $firstNumber + 9)), $numbers);
            $this->assertSame(10, WorkRequest::whereIn('request_number', $numbers)->count());
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            if (is_file($gate)) {
                unlink($gate);
            }
        }
    }
}
