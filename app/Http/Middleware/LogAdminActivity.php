<?php
namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    /** Log mutating admin actions (POST/PUT/PATCH/DELETE) for the audit trail. */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $response->getStatusCode() < 400) {
            ActivityLog::create([
                'user_id'    => auth()->id(),
                'action'     => strtolower($request->method()) . ':' . $request->path(),
                'meta'       => ['payload' => $request->except(['password', '_token', 'password_confirmation'])],
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
