<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserOnline
{
    public function handle($request, Closure $next)
    {
        Log::info("User Online");
        if (Auth::check()) {
            Log::info("User Online Auth");

            $expiresAt = now()->addMinutes(5);

            Cache::put('user-is-online-' . Auth::user()->id, true, $expiresAt);
        }
//        Log::info(Cache::has('user-is-online-' . Auth::user()->id));
        return $next($request);
    }
}