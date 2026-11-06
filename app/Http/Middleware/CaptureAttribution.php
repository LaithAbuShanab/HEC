<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptureAttribution
{
    public const SESSION_KEY = 'hec.attribution';

    private const UTM = ['utm_source', 'utm_medium', 'utm_campaign'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->header('X-Inertia') && $request->hasSession() && ! $request->session()->has(self::SESSION_KEY) && ! $request->is('admin*', 'livewire*')) {
            $request->session()->put(self::SESSION_KEY, $this->capture($request));
        }

        return $next($request);
    }

    private function capture(Request $request): array
    {
        $referrer = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        $source = [
            'landing_path' => Str::limit('/'.ltrim($request->path(), '/'), 200, ''),
            'referrer' => is_string($referrer) && $referrer !== $request->getHost() ? Str::limit($referrer, 120, '') : null,
        ];

        foreach (self::UTM as $key) {
            $value = $request->query($key);
            $source[$key] = is_string($value) && $value !== '' ? Str::limit(preg_replace('/[^\pL\pN._\- ]/u', '', $value) ?? '', 60, '') ?: null : null;
        }

        return array_filter($source);
    }
}
