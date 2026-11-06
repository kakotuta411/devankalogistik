<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class ReadOnlyForViewer
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'Management_View' && !$request->isMethod('GET')) {
            abort(403, 'Read-only access');
        }
        return $next($request);
    }
}
