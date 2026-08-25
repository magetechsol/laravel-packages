<?php

declare(strict_types=1);

namespace MageTech\DevTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DevToolsAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mts-devtools.enabled', false)) {
            abort(404);
        }

        $ip = $request->ip();
        $allowedIps = config('mts-devtools.allowed_ips', ['127.0.0.1', '::1']);

        if (! in_array('*', $allowedIps) && ! in_array($ip, $allowedIps)) {
            abort(403, 'DevTools access denied. Your IP is not authorized.');
        }

        $password = config('mts-devtools.password');

        if ($password !== null && $password !== '') {
            $authenticated = Session::get('devtools_authenticated', false);

            if (! $authenticated) {
                if ($request->isMethod('post') && $request->input('devtools_password') !== null) {
                    if (hash_equals($password, $request->input('devtools_password'))) {
                        Session::put('devtools_authenticated', true);

                        return Redirect::refresh();
                    }

                    return Redirect::back()->withErrors(['devtools_password' => 'Invalid password.']);
                }

                return response()->view('devtools::auth', [
                    'error' => Session::get('devtools_auth_error'),
                ]);
            }
        }

        return $next($request);
    }
}
