<?php

namespace App\Http\Middleware;

use App\Domain\Localization\Locale;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPanelLocale
{
    public const SESSION_KEY = 'admin_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $preferred = $user instanceof User ? $user->locale : $request->session()->get(self::SESSION_KEY);

        app()->setLocale((Locale::tryFrom((string) $preferred) ?? Locale::Arabic)->value);

        return $next($request);
    }
}
