<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeContentLength
{
    public function handle(Request $request, Closure $next): Response
    {
        $contentLength = $request->headers->get('content-length');

        if (
            is_string($contentLength)
            && str_contains($contentLength, ',')
        ) {
            $values = array_map(
                'trim',
                explode(',', $contentLength)
            );

            if (
                count(array_unique($values)) === 1
                && ctype_digit($values[0])
            ) {
                $normalized = $values[0];

                $request->headers->set(
                    'content-length',
                    $normalized
                );

                $request->server->set(
                    'CONTENT_LENGTH',
                    $normalized
                );
            }
        }

        return $next($request);
    }
}