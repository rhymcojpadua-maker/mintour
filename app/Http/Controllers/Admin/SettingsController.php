<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => [
                'site_name' => Setting::get('site_name', config('app.name', 'MinTour')),
                'contact_email' => Setting::get('contact_email', ''),
                'default_lang' => Setting::get('default_lang', 'en'),
                'groq_model' => Setting::get('groq_model', config('groq.model')),
                'rate_limit' => Setting::get('rate_limit', config('groq.rate_limit', 20)),
                'max_kb' => Setting::get('max_kb', config('groq.max_kb', 5)),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings.site_name' => ['required', 'string', 'max:120'],
            'settings.contact_email' => ['nullable', 'email', 'max:190'],
            'settings.default_lang' => ['required', 'in:en,fil'],
            'settings.groq_model' => ['required', 'string', 'max:120'],
            'settings.rate_limit' => ['required', 'integer', 'min:1', 'max:120'],
            'settings.max_kb' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            $group = in_array($key, ['groq_model', 'rate_limit', 'max_kb'], true) ? 'ai' : 'general';
            Setting::put($key, (string) $value, $group);
        }

        return back()->with('status', 'Settings saved.');
    }
}
