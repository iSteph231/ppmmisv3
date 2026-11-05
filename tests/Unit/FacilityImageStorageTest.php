<?php

namespace Tests\Unit;

use App\Services\FacilityImageStorage;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityImageStorageTest extends TestCase
{
    public function test_service_account_signs_and_exchanges_a_verified_jwt(): void
    {
        Storage::fake('local');
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);
        $publicKey = openssl_pkey_get_details($key)['key'];
        Storage::disk('local')->put('account.json', json_encode([
            'type' => 'service_account', 'client_email' => 'test@example.iam.gserviceaccount.com', 'private_key' => $privateKey,
        ], JSON_THROW_ON_ERROR));
        config(['services.google_drive' => ['service_account_path' => Storage::disk('local')->path('account.json')]]);
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'service-token']),
            'www.googleapis.com/drive/v3/files/*' => Http::response('image'),
        ]);
        $this->assertSame('image', (new FacilityImageStorage)->get('gdrive:file'));
        Http::assertSent(function ($request) use ($publicKey): bool {
            if ($request->url() !== 'https://oauth2.googleapis.com/token') {
                return false;
            }
            $this->assertSame('urn:ietf:params:oauth:grant-type:jwt-bearer', $request['grant_type']);
            [$header, $payload, $signature] = explode('.', $request['assertion']);
            $decode = static fn (string $value): string => base64_decode(strtr($value, '-_', '+/'));
            $claims = json_decode($decode($payload), true);
            $this->assertSame('test@example.iam.gserviceaccount.com', $claims['iss']);
            $this->assertSame('https://www.googleapis.com/auth/drive', $claims['scope']);
            $this->assertSame(3600, $claims['exp'] - $claims['iat']);
            $this->assertSame(1, openssl_verify($header.'.'.$payload, $decode($signature), $publicKey, OPENSSL_ALGO_SHA256));

            return true;
        });
    }

    public function test_missing_service_account_file_fails_before_any_network_request(): void
    {
        config(['services.google_drive' => ['service_account_path' => storage_path('missing-service-account.json')]]);
        Http::preventStrayRequests();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or unreadable');
        (new FacilityImageStorage)->get('gdrive:file');
    }

    public function test_upload_read_and_delete_use_private_drive_files(): void
    {
        config(['services.google_drive' => ['folder_id' => 'folder', 'client_id' => 'client', 'client_secret' => 'secret', 'refresh_token' => 'refresh']]);
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
            'www.googleapis.com/upload/drive/*' => Http::response(['id' => 'file123']),
            'www.googleapis.com/drive/v3/files/*' => Http::response('image-bytes', 200),
        ]);
        $storage = new FacilityImageStorage;
        $path = $storage->store(UploadedFile::fake()->create('photo.png', 1, 'image/png'), 'photos', 'before.png');
        $this->assertSame('gdrive:file123', $path);
        $this->assertSame('image-bytes', $storage->get($path));
        $storage->delete($path);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_contains($request->url(), '/upload/') && str_contains($request->body(), '"parents":["folder"]') && str_contains($request->body(), 'before.png'));
        Http::assertSent(fn ($request) => $request->method() === 'DELETE' && str_contains($request->url(), 'file123'));
    }

    public function test_old_local_images_remain_readable(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('old.png', 'old-image');
        $storage = new FacilityImageStorage;
        $this->assertTrue($storage->exists('old.png'));
        $this->assertSame('old-image', $storage->get('old.png'));
    }

    public function test_missing_credentials_stop_uploads(): void
    {
        config(['services.google_drive' => ['folder_id' => 'folder']]);
        Http::preventStrayRequests();
        $this->expectException(\RuntimeException::class);
        (new FacilityImageStorage)->store(UploadedFile::fake()->create('photo.png'), 'photos');
    }

    public function test_missing_drive_file_returns_false_and_api_errors_are_not_hidden(): void
    {
        config(['services.google_drive' => ['folder_id' => 'folder', 'client_id' => 'client', 'client_secret' => 'secret', 'refresh_token' => 'refresh']]);
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
            'www.googleapis.com/drive/v3/files/missing*' => Http::response([], 404),
            'www.googleapis.com/drive/v3/files/denied*' => Http::response([], 403),
        ]);
        $storage = new FacilityImageStorage;
        $this->assertFalse($storage->exists('gdrive:missing'));
        $this->expectException(RequestException::class);
        $storage->exists('gdrive:denied');
    }
}
