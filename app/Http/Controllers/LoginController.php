<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Infrastructure\ServiceResponse;
use Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginController extends BaseController
{
    /**
     * Get login page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!empty(Auth::check())){
            return redirect()->route('dashboard');
        }
        $title = "Login";
    	return view('auth/login',['title' => $title]);
    }

    /**
     * Manage Login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postManageLogin(Request $request)
    {
        $response = new ServiceResponse;
        $reqData = $request->all();
        if($reqData){
            $remember = ($request->input('remember')) ? true : false;
            $userData = Auth::attempt([ 'phone_number' => $reqData['email'], 'password' => $reqData['password'],'user_type_id' => 1],$remember);
            $id = Auth::user();
            if (!empty($userData)){
                $response->redirectURL = redirect()->intended('user/dashboard')->getTargetUrl();
                $response->IsSuccess = true;
                $response->Message = "Login has been successfully.";

            }else{
                $response->Message = "You have entered incorrect phone_number or password";
            }
        }else{
            $response->Message =  trans('messages.ERR100');
        }
        return $this->GetJsonResponse($response);
    }

    public function vpn(Request $request)
    {
        $response = \Illuminate\Support\Facades\Http::withOptions([
            'proxy' => '141.147.88.9:8888', // VPN IP address and port
        ])->post('https://uat.driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles'
        );

        return $response->json();
    }

    public function getVehicleDetails(Request $request)
    {
        $apiKey = 'CS8NuPQICs60CPb1Xb4cS6MG9KGIVuQ47ONeJXu6'; // Replace 'YOUR_API_KEY' with your actual API key

        // $name = $request->input('name');
        // $salary = $request->input('salary');
        // $age = $request->input('age');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->get('https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles', [
            // 'name' => $name,
            // 'salary' => $salary,
            // 'age' => $age,
        ]);

        $user = $response->json();

        return response()->json($user);
    }
    public function proxy(Request $request)
    {
        $allowedUrls = [
            'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles',
        ];
    
        $requestedUrl = $request->query('url');
    
        if (!in_array($requestedUrl, $allowedUrls)) {
            return response()->json(['error' => 'Requested URL is not allowed'], 403);
        }
    
        $client = new Client();
    
        // Convert request cookies to Guzzle CookieJar
        $cookies = new \GuzzleHttp\Cookie\CookieJar();
        foreach ($request->cookies->all() as $name => $value) {
            $cookies->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                'Name' => $name,
                'Value' => $value,
            ]));
        }
    
        try {
            $response = $client->request($request->method(), $requestedUrl, [
                'headers' => $request->headers->all(),
                'query' => $request->query->all(),
                'json' => $request->json()->all(),
                'form_params' => $request->request->all(),
                'cookies' => $cookies,
                'verify' => false, // if you need to ignore SSL verification
            ]);
    
            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->withHeaders($response->getHeaders());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
