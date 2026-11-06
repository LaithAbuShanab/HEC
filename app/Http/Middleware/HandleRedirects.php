<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    private const EXCLUDED_PREFIXES = ['/admin', '/livewire', '/filament', '/up', '/build', '/storage'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = Redirect::normalizePath($request->getPathInfo());

        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $next($request);
            }
        }

        $match = Redirect::map()[$path] ?? null;

        if ($match === null) {
            return $next($request);
        }

        Redirect::recordHit($match['id']);

        if ($match['status'] === 410) {
            abort(410);
        }

        $query = $request->getQueryString();
        $target = $match['to'].($query ? (str_contains($match['to'], '?') ? '&' : '?').$query : '');

        return redirect()->to($target, $match['status']);
    }
}
