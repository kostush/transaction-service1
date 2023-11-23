<?php

namespace App\Http\Middleware;

use App\Logger;
use Closure;
use ProBillerNG\Logger\Log;

class NGLogger
{
    use Logger;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request The http request
     * @param  \Closure                 $next    Closure
     * @return mixed
     * @throws \ProBillerNG\Logger\Exception
     * @throws \App\Exceptions\InvalidSessionIdException
     */
    public function handle($request, Closure $next)
    {
        $this->initLogger('APP_LOG_FILE', $request);

        Log::logRequest(
            $request,
            [
                'payment.information.routingNumber',
                'payment.information.accountNumber',
                'payment.information.socialSecurityLast4',
                'payment.information.number',
                'payment.information.cvv'
            ]
        );

        $response = $next($request);

        Log::logResponse($response);

        return $response;
    }
}
