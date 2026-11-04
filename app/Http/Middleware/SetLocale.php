<?php
namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->user()?->preferred_lang
            ?? session('lang')
            ?? Setting::get('default_lang', config('app.locale', 'en'));

        if (! in_array($lang, ['en', 'fil'], true)) {
            $lang = 'en';
        }

        app()->setLocale($lang);

        return $next($request);
    }
}
