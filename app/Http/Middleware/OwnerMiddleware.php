<?php

namespace App\Http\Middleware;

use App\Http\Responses\ResponseResult;
use Closure;
use Illuminate\Http\Request;

class OwnerMiddleware
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
        if(!auth()->guest() && auth()->user()->owner){
            return $next($request);
        }
        $response = new ResponseResult();
        $response->setResult(false);
        $response->setMessage('Your is not owner');

        return response()->json($response->makeResponse());
    }
}
