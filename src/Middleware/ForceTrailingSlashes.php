<?php

namespace Irving\Middleware;

use Closure;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Config;

class ForceTrailingSlashes
{
	/**
	 * Handle an incoming request.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \Closure  $next
	* @return mixed
	*/
	public function handle($request, Closure $next)
	{
		if (!preg_match('/.+\/$/', $request->getRequestUri())) {
			return Redirect::to( Config::get('app.url') . $request->getRequestUri() . '/' );
		}
		return $next($request);
	}
}
