<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ToolController extends Controller
{
    public function checkLiveUidFacebook(Request $request) {
        $data = $request->validate([
            'uid' => 'array',
        ]);
        $arrCheck = array();
        foreach ($data['uid'] as $uid) {
            $url = 'https://www.facebook.com/'.$uid;
            $response = Http::get($url);
            if (empty($response->headers()['accept-ch-lifetime'])) {
                $arrCheck[] = [
                    'status' => 'not_exist',
                    'uid' => $uid
                ];
            } else {
                $arrCheck[] = [
                    'status' => 'exist',
                    'uid' => $uid
                ];
            }
        }
      
        return response()->json($arrCheck);
    }
}
