<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthKeyController extends Controller
{

    public function refreshAuth($fresh=false)
    {
        $url = 'https://csgapi.appspot.com/v1/auth.json';
        $data = [
            "api_key" => "b6857a54ee06426068773cfad96cb5d154e0ad80445e616835b7d3bd502f83d6",
            "portal_name" => "csg_individual"
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);
        
        $data = json_decode($response);

        $key = $data->token;
        $type = $data->client_type;
        $expires_at = $data->expires_date;
        $last_used_at = $data->last_activity;

        $data = [
            "key" => $key,
            "type" => $type,
            "expires_at" => $expires_at,
            "last_used_at" => $last_used_at,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $data = array_merge($data, ['user_id' => 1]);

        try{
            DB::table('auth_keys')->insert($data);
            $getToken = DB::table('auth_keys')->first();
            if($fresh){
                return $getToken;
            }
            return response()->json(['status' => 'success', 'token' => $getToken]);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    //make a API call function globally
    public function makeApiCall($url)
    {
        $baseUrl = 'https://csgapi.appspot.com/v1/';
        $targetUrl = $baseUrl . $url;

        $token = DB::table('auth_keys')->first();
        if(!$token){
            return response()->json(['error' => 'Token not found, Please create the token']);
        }

        $expires_at = Carbon::parse($token->expires_at);
        $current_time = Carbon::now();
        //difference in minutes
        $diff = $current_time->diffInHours($expires_at);

        if($diff < 1){
           $token = $this->refreshAuth(true);
        }

        $token = $token->key;
        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-api-token: $token"
        ]);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }



    public function getQuotes(Request $request)
    {
        $url = 'final_expense_life/quotes.json';
        $data = $request->all();
        $targetUrl = $url.'?'.http_build_query($data);
        $response = $this->makeApiCall($targetUrl);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }
        return response()->json(json_decode($response));
    }


    public function openCompanies(Request $request)
    {
        $url = 'final_expense_life/open/companies.json';
        $response = $this->makeApiCall($url);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }
        return response()->json(json_decode($response));
    }

    // Medicare Advantage Routes
    public function getQuotesMA(Request $request)
    {
        $url = 'medicare_advantage/quotes.json';
        $data = $request->all();
        $targetUrl = $url.'?'.http_build_query($data);
        $response = $this->makeApiCall($targetUrl);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }
        return response()->json(json_decode($response));
    }

    //get a single quote
    public function getSingleQuoteMA(Request $request)
    {
        $url = 'medicare_advantage/qoutes';
        $qoute_id = $request->qoute_id??null;
        if(empty($qoute_id)){
            return response()->json(['error' => 'Please provide the quote_id']);
        }
        $targetUrl = $url.'/'.$qoute_id.'.json';
        $response = $this->makeApiCall($targetUrl);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }
        return response()->json(json_decode($response));

    }

    //Market Penetration
    public function marketPenetrationMA(Request $request)
    {
        $url = 'medicare_advantage/penetration.json';
        $data = $request->all();
        $targetUrl = $url.'?'.http_build_query($data);
        $response = $this->makeApiCall($targetUrl);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }
        return response()->json(json_decode($response));
    }

    //Market Contract Enrollment
    public function marketContractEnrollmentMA(Request $request)
    {
        $url = 'medicare_advantage/contract_enrollment.json';
        $data = $request->all();
        $targetUrl = $url.'?'.http_build_query($data);
        $response = $this->makeApiCall($targetUrl);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }

        return response()->json(json_decode($response));
    }

    //Companies Collection
    public function medicareAdvantageCompanies(Request $request)
    {
        $url = 'medicare_advantage/open/companies.json';
        $response = $this->makeApiCall($url);
        if(stripos($response, '<html>') !== false){
            return response($response);
        }
        return $response;
        return response()->json(json_decode($response));
    }

}
