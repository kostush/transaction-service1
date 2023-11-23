<?php

namespace App\Http\Middleware;

use Closure;
use Ramsey\Uuid\Uuid;

class GenerateSessionId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request The http request
     * @param  \Closure                 $next    Closure
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $sessionId = $request->route('sessionId');

        if (empty($sessionId)) {
            $sessionId = (string) Uuid::uuid4();
        }

        $request->attributes->set('sessionId', $sessionId);

        $response = $next($request);

        return $response;
    }
}
