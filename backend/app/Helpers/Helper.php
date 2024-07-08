<?php
namespace App\Helpers;

use App\Http\Enums\EnumDomainStatus;
use App\Http\Enums\OrderType;
use App\Models\User;
use App\Modules\Payment\PaymentFactory;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class Helper{
	

    static function get_data_from_url($method,$url,$postFields=array(),$header=array(),$params=array()){
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($method=='POST'){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		}
		if($method =='PUT'){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		if(!empty($params)){
			foreach($params as $key=>$val){
				curl_setopt($ch, $key, $val);
			}
		}
		
	// 	debug($url);
		if(!empty($header)){
	// 		debug($header);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
			
		$response = curl_exec($ch);
		curl_close($ch);	
	
		LogHelper::write_log('payment/curl.log',"method: {$method}\n
		url: {$url}\n
		postField: ".($postFields)."\n
		params: ".json_encode($params)."\n
		header: ".json_encode($header)."\n
		response: {$response}");
		
		return $response;
	}

	function get_client_ip_address(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
	
	static function submitForm($data, $actionUrl, $formEndCode='')
	{
		$html = '<form action="' . $actionUrl . '" method="POST" name="jb_payment_form" id="jb_payment_form" '.($formEndCode ? 'enctype="'.$formEndCode.'"' : '').'>';
		foreach ($data as $key => $val) {
			$html .= '<input name="' . $key . '" value="' . $val . '" type="hidden" />';
		}
		$html .= '</form>';
		$html .= '<script>document.jb_payment_form.submit();</script>';
		echo view('payment.redirecting', ['message' => $html, 'url' =>  'javascript:document.jb_payment_form.submit()', 'second' => 10]);
	}

	static function redirect($url, $permanent = false, $delay = 0, $msg = ''){
		if($delay){
			$second = $delay/1000;
			echo view('payment.redirecting', ['message' => $msg, 'url' =>  $url, 'second' => $second]);
		}else{
			if($permanent && headers_sent() === false){
				header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);			
			}else{
				echo view('payment.redirecting', ['message' => $msg, 'url' =>  $url, 'second' => 0]);
			}
		}
		
		exit();
	}

	static function dd($arr){
		header('Content-type: application/json');
		echo json_encode($arr);
		exit();
	}

	static function redirectPaymentUrl($order, $type = 0, $method = 0){
		if(!$order->user){
			$user = User::find($order->user_id);
		}else{
			$user = $order->user;
		}
		if($order->type != OrderType::BILLING['value'] && $user->domain && $user->domain_status == EnumDomainStatus::SUCCESS['value']){
			$payment = PaymentFactory::getInstance($order, $type, $method);
			$config = $payment->getConfig();
			if($config->config['domain_routing']){
				$redirectUrl = "https://{$user->domain}/payment/checkout/?order_id={$order->id}&type={$type}&method={$method}";
				// die($redirectUrl);
				// Redirect::away($redirectUrl);
				self::redirect($redirectUrl,true);
			}
		}
	}

	static public function generateUpiLink ($apiKey, $apiSecret, $order) {
		$headers = [
            "Authorization" =>  'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
            'Content-Type' =>  'application/json'
        ];

		$payload = [
            'type' => 'upi_qr',
            'usage' => 'single_use',
            'fixed_amount' => true,
            'payment_amount' => (int) ($order->total * 100),
            'notes' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]
        ];

        LogHelper::info('payment_qr.txt', 'Payment QR request: ==== ' . json_encode($payload));

        $response = Http::withHeaders($headers)->post('https://api.razorpay.com/v1/payments/qr_codes', $payload)->object();

        LogHelper::info('payment_qr.txt', 'Payment QR response: ==== ' . json_encode($response));

        // success
        if ($response->id) {
            $upiUrl = '';
            // decode qr image to get upi url
            $decodedResponse = Http::get('https://api.qrserver.com/v1/read-qr-code/?fileurl=' . $response->image_url)->object();
			LogHelper::info('payment_qr.txt', 'Payment QR decode response: ==== ' . json_encode($decodedResponse));

			if (count($decodedResponse) && isset($decodedResponse[0]->symbol) && count($decodedResponse[0]->symbol)) {
                $upiUrl = $decodedResponse[0]->symbol[0]->data;
			}

			return [
				'qr_image_url' => $response->image_url,
				'upi_url' => $upiUrl
			];
		}

		return [
			'qr_image_url' => '',
			'upi_url' => ''
		];
	}

}