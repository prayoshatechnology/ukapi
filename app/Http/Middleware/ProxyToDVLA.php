<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProxyToDVLA
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
        $client = new \GuzzleHttp\Client();

        // Make a request to the DVLA API
        $response = $client->request($request->getMethod(), 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles', [
            'headers' => $request->headers->all(),
            'query' => $request->query->all(),
            'form_params' => $request->request->all(),
            'files' => $request->files->all(),
            'cookies' => $request->cookies->all(),
            'verify' => false, // This is optional, to ignore SSL verification
        ]);

        // Pass the response content along with status code and headers
        return response($response->getBody()->getContents(), $response->getStatusCode())->withHeaders($response->getHeaders());
    }
}
