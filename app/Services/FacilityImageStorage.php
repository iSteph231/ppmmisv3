<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacilityImageStorage
{
    private ?string $accessToken = null;

    public function store(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $folder = config('services.google_drive.folder_id');
        if (! $folder) {
            throw new RuntimeException('GOOGLE_DRIVE_FOLDER_ID is required for image uploads.');
        }

        $boundary = 'drive_'.Str::random(32);
        $metadata = json_encode(['name' => $filename ?? $file->hashName(), 'parents' => [$folder]], JSON_THROW_ON_ERROR);
        $body = "--{$boundary}\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n{$metadata}\r\n"
            ."--{$boundary}\r\nContent-Type: {$file->getMimeType()}\r\n\r\n".$file->get()."\r\n--{$boundary}--\r\n";
        $id = $this->client()->withBody($body, 'multipart/related; boundary='.$boundary)
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id&supportsAllDrives=true')
            ->throw()->json('id');
        if (! is_string($id) || $id === '') {
            throw new RuntimeException('Google Drive did not return an uploaded file ID.');
        }

        return 'gdrive:'.$id;
    }

    public function exists(string $path): bool
    {
        if (! str_starts_with($path, 'gdrive:')) {
            return Storage::disk('local')->exists($path);
        }
        $response = $this->client()->get($this->url($path), ['fields' => 'id,trashed', 'supportsAllDrives' => 'true']);
        if ($response->status() === 404) {
            return false;
        }

        return ! $response->throw()->json('trashed');
    }

    public function get(string $path): string
    {
        return str_starts_with($path, 'gdrive:')
            ? $this->client()->get($this->url($path), ['alt' => 'media', 'supportsAllDrives' => 'true'])->throw()->body()
            : Storage::disk('local')->get($path);
    }

    public function mimeType(string $path): string
    {
        return str_starts_with($path, 'gdrive:')
            ? $this->client()->get($this->url($path), ['fields' => 'mimeType', 'supportsAllDrives' => 'true'])->throw()->json('mimeType')
            : Storage::disk('local')->mimeType($path);
    }

    public function response(string $path): StreamedResponse
    {
        $contents = $this->get($path);

        return response()->stream(static function () use ($contents): void {
            echo $contents;
        }, 200, ['Content-Type' => $this->mimeType($path), 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function delete(string|array $paths): void
    {
        foreach ((array) $paths as $path) {
            if (! str_starts_with($path, 'gdrive:')) {
                Storage::disk('local')->delete($path);

                continue;
            }
            $response = $this->client()->delete($this->url($path).'?supportsAllDrives=true');
            if ($response->status() !== 404) {
                $response->throw();
            }
        }
    }

    private function url(string $path): string
    {
        return 'https://www.googleapis.com/drive/v3/files/'.rawurlencode(substr($path, 7));
    }

    private function client(): PendingRequest
    {
        if ($this->accessToken === null) {
            $settings = config('services.google_drive');
            if (! empty($settings['service_account_path'])) {
                $parameters = [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $this->serviceAccountAssertion($settings['service_account_path']),
                ];
            } else {
                foreach (['client_id', 'client_secret', 'refresh_token'] as $key) {
                    if (empty($settings[$key])) {
                        throw new RuntimeException('Google Drive OAuth client ID, client secret, and refresh token must be configured.');
                    }
                }
                $parameters = [
                    'client_id' => $settings['client_id'],
                    'client_secret' => $settings['client_secret'],
                    'refresh_token' => $settings['refresh_token'],
                    'grant_type' => 'refresh_token',
                ];
            }
            $token = $this->httpClient()->timeout(30)->asForm()->post('https://oauth2.googleapis.com/token', $parameters)->throw()->json('access_token');
            if (! is_string($token) || $token === '') {
                throw new RuntimeException('Google did not return an access token.');
            }
            $this->accessToken = $token;
        }

        return $this->httpClient()->withToken($this->accessToken);
    }

    private function httpClient(): PendingRequest
    {
        $bundle = config('services.google_drive.ca_bundle');
        if ($bundle && ! is_readable($bundle)) {
            throw new RuntimeException('The configured Google Drive CA bundle is unreadable.');
        }

        return Http::connectTimeout(10)->timeout(60)->withOptions(['verify' => $bundle ?: true]);
    }

    private function serviceAccountAssertion(string $path): string
    {
        if (! is_readable($path)) {
            throw new RuntimeException('Google Drive service account JSON file is missing or unreadable.');
        }
        $credentials = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (($credentials['type'] ?? null) !== 'service_account' || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException('Google Drive requires a valid service account JSON file.');
        }
        $encode = static fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $issuedAt = time();
        $header = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $encode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsigned = $header.'.'.$claims;
        if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the Google Drive service account assertion.');
        }

        return $unsigned.'.'.$encode($signature);
    }
}
