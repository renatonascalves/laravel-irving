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
			return Redirect::to( 'http://127.0.0.1:8000' . $request->getRequestUri() . '/' ); // Config::get('app.url')
		}
		return $next($request);
	}
}
