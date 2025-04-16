<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function ghl_oauth_call($code = '', $method = '')
    {
        $url = 'https://api.msgsndr.com/oauth/token';
        $curl = curl_init();
        $data = [];
        $data['client_id'] = env('GHL_CLIENT_ID');
        $data['client_secret'] = env('GHL_CLIENT_SECRET');
        $md = empty($method) ? 'code' : 'refresh_token';
        $data[$md] = $code;
        $data['grant_type'] = empty($method) ? 'authorization_code' : 'refresh_token';
        $postv = '';
        $x = 0;
        foreach ($data as $key => $value) {
            if ($x > 0) {
                $postv .= '&';
            }
            $postv .= $key . '=' . $value;
            $x++;
        }
        $curlfields = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postv,
        );
        curl_setopt_array($curl, $curlfields);
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        return $response;
    }

    public function ghl_token($request, $type = '')
    {
        // dd($request);
        $code = $request->code;
        $code = ghl_oauth_call($code, $type);
        $route = '/';
        $id = 1;
        if ($code) {
            if (property_exists($code, 'access_token')) {
                save_auth($code, $type);
                return redirect()->route('dashboard')->with('success', 'Connected successfully');
            } else {
                if (property_exists($code, 'error_description')) {
                    if (empty($type)) {
                        return redirect()->route('dashboard')->with('error', $code->error_description);
                    }
                }
                return null;
            }
        } else {
            return redirect()->route('dashboard')->with('error', 'Server error');
        }
    }

     //goHighLevel oAuth 2.0 callback
     public function goHighLevelCallback(Request $request)
     {
         return ghl_token($request);
     }
}
