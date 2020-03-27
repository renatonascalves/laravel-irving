<?php

namespace Irving\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForceTrailingSlashes
{
	/**
	* Handle an incoming request.
	*
	* @param  \Illuminate\Http\Request $request
	* @param  \Closure                 $next
	* @return mixed
	*/
	public function handle(Request $request, Closure $next)
	{
		$appUrl = env('APP_URL', null);

		if (empty($appUrl)) {
			return $next($request);
		}

		$params = $request->all();
		$path   = $params['path'] ?? '';
		$uri    = $request->getRequestUri();

		// Return if there is already a trailing slash.
		if ($path === '/' || Str::endsWith($path, '/')){
			return $next($request);
		}

		// Redirect to ?path=/ (home)
		if (empty($path)) {
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
