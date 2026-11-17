<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateRequestSubmission
{
    public function handle(Request $request, Closure $next, string $redirectRoute): Response
    {
        $request->validate(['submission_token' => ['sometimes', 'uuid']]);
        $token = $request->input('submission_token');
        $payload = $request->except(['_token', 'submission_token']);
        foreach ($request->allFiles() as $field => $file) {
            if ($file instanceof UploadedFile) {
                $payload[$field] = $file->isValid() ? hash_file('sha256', $file->getRealPath()) : 'invalid-upload';
            }
        }
        ksort($payload);
        $fingerprint = $token ?? hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        $key = 'request-submission:'.hash('sha256', $request->user()->id.'|'.$redirectRoute.'|'.$fingerprint);
        $lock = Cache::lock($key.':lock', 300);
        if (! $lock->get()) {
            throw ValidationException::withMessages(['submission' => 'Your request is already being submitted. Please wait.']);
        }

        try {
            if (Cache::has($key)) {
                return redirect()->route($redirectRoute)->with('success', 'Your request has already been submitted.');
            }
            $response = $next($request);
            if ($response instanceof RedirectResponse && $response->getTargetUrl() === route($redirectRoute)) {
                Cache::put($key, true, $token ? now()->addDay() : now()->addMinute());
            }

            return $response;
        } finally {
            $lock->release();
        }
    }
}
