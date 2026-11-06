<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Localization\Locale;
use App\Http\Middleware\SetPanelLocale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchPanelLocaleController
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $target = Locale::tryFrom($locale);

        abort_if($target === null, 404);

        $request->session()->put(SetPanelLocale::SESSION_KEY, $target->value);

        $user = $request->user();

        if ($user instanceof User) {
            $user->forceFill(['locale' => $target->value])->saveQuietly();
        }

        $previous = url()->previous();

        return redirect()->to(str_starts_with($previous, url('/admin')) ? $previous : url('/admin'));
    }
}
