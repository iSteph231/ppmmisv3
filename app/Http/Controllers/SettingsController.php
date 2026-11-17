<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $systemInfo = $this->getSystemInfo();

        return view('settings.index', compact('user', 'systemInfo'));
    }

    public function updateProfile(Request $request): never
    {
        abort(403, 'Profile details cannot be changed from settings.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            return redirect()->back()->withErrors($validator);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Password changed successfully!');
    }

    public function updateNotifications(Request $request)
    {
        $preferences = $request->validate([
            'email_notifications' => 'boolean',
            'browser_notifications' => 'boolean',
            'request_updates' => 'boolean',
        ]);

        session()->put('notification_preferences', $preferences);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences saved!',
        ]);
    }

    public function systemInfo()
    {
        return response()->json($this->getSystemInfo());
    }

    private function getSystemInfo()
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'timezone' => config('app.timezone'),
            'database' => config('database.default'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_usage' => $this->formatBytes(memory_get_usage()),
            'disk_free' => $this->formatBytes(disk_free_space('/')),
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
