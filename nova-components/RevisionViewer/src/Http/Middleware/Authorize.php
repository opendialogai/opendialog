<?php

namespace App\RevisionViewer\Http\Middleware;

use App\RevisionViewer\RevisionViewer;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return resolve(RevisionViewer::class)->authorize($request) ? $next($request) : abort(403);
    }
}
