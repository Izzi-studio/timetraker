<?php

namespace App\Http\Middleware;

use App\Http\Responses\ResponseResult;
use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->guest() && !auth()->user()->owner && !auth()->user()->is_admin){
            return $next($request);
        }
        $response = new ResponseResult();
        $response->setResult(false);
        $response->setMessage('Your is not customer');

        return response()->json($response->makeResponse());
    }
}
