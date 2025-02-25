<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware to check for user authentication and set HTTP response headers
 * to prevent caching of sensitive data.
 */
class CheckCookie
{
    /**
     * Handle an incoming HTTP request.
     *
     * This middleware ensures that the user is authenticated before allowing
     * the request to proceed. If the user is not authenticated, they are 
     * redirected to the login route with an error message. It also modifies
     * the HTTP response to disable caching for security purposes.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request instance.
     * @param  \Closure  $next  The next middleware in the request pipeline.
     * @return mixed The HTTP response, either redirecting to login or 
     *               allowing the request to proceed with caching disabled.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            // Redirect to the login route with an error message if not authenticated
            return redirect()->route('login')->with('error', 'AUTH_008-Please log in first.');
        }
    
        // Proceed with the next middleware in the pipeline
        $response = $next($request);
    
        // Set HTTP headers to disable caching
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    
        // Return the modified response
        return $response;
    }
}
