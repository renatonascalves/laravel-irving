<?php

namespace Irving\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceTrailingSlashes
{
	/**
	* Handle an incoming request.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \Closure  $next
	* @return mixed
	*/
	public function handle(Request $request, Closure $next)
	{
		$appUrl = \config('app.url');

		if (empty($appUrl)) {
			return $next($request);
		}

		$params = $request->all();
		$uri    = $request->getRequestUri();

		// Redirect to ?path=/ (home)
		if (empty($params['path'])) {
			return \redirect()->to( $appUrl . $uri . '?path=/' );
		} else {

			// Force forward slash (/) to an url.
			if (!preg_match('/.+\/$/', $uri)) {
				return \redirect()->to( $appUrl . $uri . '/' );
			}
		}

		return $next($request);
	}
}
