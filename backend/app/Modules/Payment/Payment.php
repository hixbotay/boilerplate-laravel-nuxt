<?php

namespace App\Modules\Payment;

use App\Helpers\Helper;
use App\Helpers\Logger;
use App\Helpers\LogHelper;
use App\Http\Enums\EnumDomainStatus;
use App\Http\Enums\EnumSubscriptionStatus;
use App\Http\Enums\OrderType;
use App\Http\Enums\PayStatus;
use App\Models\MerchantOrder;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserStore;
use App\Models\MerchantOrderItem;
use App\Platforms\PlatformGeneral;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Throwable;

class Payment
{
    private $instance;
    private $method;
    public $service;
    private $gatewayTransaction;


    public function __construct(string $method)
    {
        $this->method = $method;
        $methodClass = 'App\Modules\Payment\Methods\\' . $method;
        $this->instance = new $methodClass();
        $this->instance->logger = new Logger([
            'payment/' . strtolower($this->method) . '.txt'
        ]);
    }

    public function set($key, $val)
    {
        $this->instance->$key = $val;
    }

    public function getMethod()
    {
        return $this->method;
    }
    public function getOrder()
    {
        return $this->instance->order;
    }
    public function getConfig()
    {
        return $this->instance->config;
    }

    public function setup() {
        return $this->instance->setup();
    }


    public function execute($func, $request)
    {
        $this->log("{$func}: " . json_encode($request->all()));
        try {
            return $this->instance->$func($request);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function setInstanceAttr($key, $val)
    {
        $this->instance->$key = $val;
    }


    public function checkout($request, $type = false, $method = false)
    {
        $this->setStoreInfo();
        if ($this->instance->requireOrderItems) {
            $this->instance->orderItems = MerchantOrderItem::where('order_id', $this->instance->order->id)->get()->all();
        }
        if ($this->instance->subscription->status == EnumSubscriptionStatus::EXPIRED['value']) {
            return view('payment.license_expired', ['mobile' => User::find($this->instance->order->user_id)->mobile]);
        }

        $this->instance->transaction = PaymentTransaction::create([
            'order_id' => $this->instance->order->id,
            'user_id' => $this->instance->order->user_id,
            'gateway' => $this->instance->config->service_id,
            'total' => $this->instance->order->total,
            'history' => ['checkout at ' . Carbon::now()->toString()],
            'status' => PayStatus::AWAITING_PAYMENT['value'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        if ($this->instance->order->type != OrderType::BILLING['value'] && $this->instance->subscription->user->domain && $this->instance->subscription->user->domain_status == EnumDomainStatus::SUCCESS['value']) {
            $domain = "https://{$this->instance->subscription->user->domain}";
        } else {
            $domain = config('app.url');
        }
        $this->instance->pingback_url =  $domain . "/payment/response/" . strtolower($this->method) . "/{$this->instance->transaction->id}";
        $this->instance->pingback_url_failed =  $domain . "/payment/response/" . strtolower($this->method) . "/{$this->instance->transaction->id}/?failed=1";
        $this->instance->notify_url = $domain . "/payment/notify/" . strtolower($this->method) . "/{$this->instance->transaction->id}";
        $this->instance->refund_url = $domain . "/payment/refund/" . strtolower($this->method) . "/{$this->instance->transaction->id}";
        $this->instance->cancel_url = $domain . "/payment/cancel/" . strtolower($this->method) . "/{$this->instance->transaction->id}";
        if ($this->instance->order->type == OrderType::BILLING['value']) {
            $this->instance->cancel_url = env('APP_MERCHANT_DASHBOARD_URL') . '/pricing';
        }
        $this->log("Checkout order {$this->instance->order->id} {$this->instance->order->total} {$this->instance->order->currency} {$this->instance->order->billing_person_name} {$this->instance->order->billing_person_mobile} {$this->instance->order->email}");
        try {
            if ($this->instance->order->type == OrderType::BILLING['value'] && boolval($this->instance->order->params['is_auto_renew'])) {
                $res = $this->instance->createSubscription($method);
            } else {
                $res = $this->instance->checkout($request, $type, $method);
            }

            return $res;
        } catch (\Exception $e) {
            if (env('LOG_LEVEL') == 'debug') {
                dd($e);
            }
            $this->log(json_encode($e));
            return view('payment.redirecting', ['message' => $e->getMessage(), 'url' =>  $this->instance->order->params['return'], 'second' => 60]);
        }
    }

    public function getPaymentInfoForm(array $data)
    {
        return $this->instance->getPaymentInfoForm($data);
    }

    public function response(Request $request, $tx_id)
    {
        try {
            $this->log("Response " .$tx_id.' '. json_encode($request->all()));
            $this->instance->transaction = PaymentTransaction::find($this->instance->getOrderIdFromResponse($request, $tx_id));
            if (!$this->instance->transaction->id) {
                return view('payment.inform', ['message' => 'Invalid transacion ID']);
            }            
			$this->instance->order = MerchantOrder::find($this->instance->transaction->order_id);
            if ($this->instance->transaction->status == PayStatus::PAID['value']) {
                if ($this->instance->order->type == OrderType::MERCHANT['value'] && !$this->instance->order->platform_status) {
                    return $this->messageUpdatePlatformFailed();
                }
                if ($this->instance->order->type == OrderType::BILLING['value']) {
                    $url = $this->getReturnUrl() . '?tx_id=' . $this->instance->transaction->tx_id;
                } else {
                    $url = $this->getReturnUrl();
                }
                Helper::redirect($url, false, 100);
            }
			
			
            if ($request->failed || (!$this->instance->notAllowCheckFailedInResponse && !$request->step && $this->isPaymentFailed($request))) {
                return view('payment.inform', [
                    'title' => 'Your payment failed',
                    'url' =>  $this->getReturnUrl(),
                    'message' => $this->gatewayTransaction['message'] ? $this->gatewayTransaction['message'] : "Payment failed"
                ]);
            }

            if ($this->instance->is_loop_check_response) {
                $step = $request->step;
                if (!$step) {
                    $this->instance->transaction->addHistory('Response at ' . Carbon::now()->toISOString() . ' ' . json_encode($request->all()));
                    $this->instance->transaction->save();
                }
                if ($step < 5) {
                    $root = config('app.url');
                    $query = $_REQUEST;
                    $query['step'] = $step + 1;
                    $url = $root . '/' . trim($request->getPathInfo(), '/') . '/?' . http_build_query($query);
                    $second = 30 - $step * 5;
                    return view('payment.checking', ['url' =>  $url, 'second' => $second]);
                }
            } else {
                $this->instance->transaction->addHistory('Response at ' + Carbon::now()->toISOString() + ' ' + json_encode($request->all()));
                $this->instance->transaction->save();
            }
            $this->setStoreInfo();
            $this->instance->logger->addPath('payment/' . $this->instance->order->user_id . "/payment.txt");
            $res = $this->updateOrderStatus($request, $tx_id);

            //redirect to success url
            if ($this->instance->order->type == OrderType::BILLING['value']) {
                $url = $this->getReturnUrl() . '?tx_id=' . $res['tx_id'];
            } else {
                $url = $this->getReturnUrl();
            }
            if (!$res['status']) {
                return view('payment.inform', [
                    'title' => 'Your payment is failed',
                    'url' =>  $url,
                    'message' => "Payment failed"
                ]);
            }
            if ($res['status'] && $res['platform'] && !$res['platform']['status']) {
                return $this->messageUpdatePlatformFailed();
            }
            Helper::redirect($url, false, 100);
        } catch (Exception $e) {
            if (env('LOG_LEVEL') == 'debug') {
                dd($e);
            }
            $this->log(json_encode($e));
            return view('payment.redirecting', ['message' => $e->getMessage(), 'url' =>  $url, 'second' => 60]);
        }
    }

    private function messageUpdatePlatformFailed()
    {
        $mobile = User::find($this->instance->order->user_id)->mobile;

        return view('payment.inform', [
            'title' => 'Your payment success but can not update order, please contact support',
            'url' =>  $this->getReturnUrl(),
            'message' => "Contact seller at <a id='{$mobile}' href='tel:{$mobile}'>{$mobile}</a> for more details"
        ]);
    }

    public function cancel($request, $tx_id)
    {
        $this->log("Response " . json_encode($request->all()));
        $this->instance->transaction = PaymentTransaction::find($tx_id);
        if (!$this->instance->transaction->id) {
            return view('payment.inform', ['message' => 'Invalid transacion ID']);
        }
        $this->instance->order = MerchantOrder::find($this->instance->transaction->order_id);
        Helper::redirect($this->getReturnUrl(), false, 0, "Your payment has been cancelled");
    }

    private function getReturnUrl()
    {
        if ($this->instance->order->type == OrderType::BILLING['value']) {
            $url = env('APP_MERCHANT_DASHBOARD_URL') . '/checkout/result';
        } else {
            $url = $this->instance->order->params['return'];
        }
        return $url;
    }

    private function isPaymentFailed($request)
    {
		if(method_exists($this->instance,'isResponseFailed')){
			return $this->instance->isResponseFailed($request);
		}else{
			$this->setStoreInfo();
			$this->gatewayTransaction = $this->instance->getTxStatus($request);
			return !$this->gatewayTransaction['status'];
		}
        
    }

    private function updateOrderStatus($request, $tx_id)
    {
        $this->log("Start update order {$this->instance->order->id} " . OrderType::getDisplay($this->instance->order->type) . " ");
        try {
            if (!$this->gatewayTransaction) {
                $this->gatewayTransaction = $this->instance->getTxStatus($request);
            }
            // update order
            $this->instance->order->payment_tx_id = $this->gatewayTransaction['tx_id'];
            $this->instance->order->payment_mode = $this->gatewayTransaction['pay_method'];
            $arr = $this->instance->order->params;
            $arr['vpa'] = $this->gatewayTransaction['vpa'];
            $arr['email'] = $this->gatewayTransaction['email'];
            $arr['contact'] = $this->gatewayTransaction['contact'];
            $this->instance->order->params = $arr;
            // transaction
            $this->instance->transaction->tx_id = $this->gatewayTransaction['tx_id'];
            $this->instance->transaction->data = array_merge($this->instance->transaction->data, $this->gatewayTransaction);
            $this->instance->transaction->fee = $this->gatewayTransaction['fee'];
            $this->instance->transaction->saving = 0;
            $this->instance->transaction->saving_lost = 0;

            $webhookUrls = [];

            if ($this->gatewayTransaction['status']) {
                //update webhook
                if ($this->config['webhook']) {
                    Http::timeout(1)->post($this->config['webhook'], $this->instance->order)->object();
                }
                $this->instance->order->payment_status = PayStatus::PAID['value'];
                $this->instance->transaction->status = PayStatus::PAID['value'];

                $store = UserStore::where('user_id', $this->instance->subscription->user_id)
                    ->where('platform_id', $this->instance->order->platform_id)->first();
                $platformConfig = [
                    'ecwid_store_id' => $this->instance->order->store_id,
                    'ecwid_access_token' => $store->params->access_token,
                    'user_id' => $this->instance->order->user_id,
                    'user_store_id' => $this->instance->order->user_store_id
                ];
                // dd($platformConfig,$store,$this->instance->order);
                $platform = new PlatformGeneral($this->instance->order->platform_id, $platformConfig);
                $this->gatewayTransaction['platform'] = $platform->updateOrderSuccess($this->instance->order);
                $this->log('Update order to platform ' . json_encode($this->gatewayTransaction['platform']));
                if ($this->gatewayTransaction['platform']['status']) {
                    $this->instance->order->platform_status = 1;
                } else {
                    $this->instance->order->platform_status = 0;
                }
            } else {
                $this->instance->order->payment_status = PayStatus::FAILED['value'];
            }

            // transaction begin
            DB::beginTransaction();
            // update order and transaction
            $this->instance->order->save();
            $this->instance->transaction->save();
            // commit transaction
            DB::commit();

            return $this->gatewayTransaction;
        } catch (Throwable $th) {
            $this->log("Checkout ===== " . $th->getMessage());
        }
        return false;
    }

    public function notify(Request $request, $tx_id = null)
    {
        $this->log("Notify ".$tx_id.' ' . json_encode($request->all()));
		$this->instance->isNotify = true;
        $transactionId = $this->instance->getOrderIdFromNotify($request, $tx_id);
        $this->instance->transaction = PaymentTransaction::find($transactionId);
        if (!$this->instance->transaction) {
            exit('TX NOT EXISTED');
        }
        if ((string) $this->instance->transaction->status == PayStatus::PAID['value']) {
            if (!config('app.payment_mock', false)) {
                exit('PAYMENT DONE ALREADY');
            }
        }
        $this->instance->transaction->addHistory("Notify " . json_encode($request->all()));
        $this->instance->order = MerchantOrder::find($this->instance->transaction->order_id);
        if ($this->updateOrderStatus($request, $tx_id)) {
            exit('OK');
        }
        exit('FAILED');
    }

    public function oAuth(Request $request)
    {
        $this->log("oauth " . json_encode($request->all()));
        return $this->instance->oAuth($request);
    }

    private function log($data)
    {
        // $this->instance->logger->info($data);
        LogHelper::write_log("payment/" . strtolower($this->method) . ".txt", $data);
        LogHelper::write_log("payment/payment.txt", $data);
        if (isset($this->instance->subscription)) {
            LogHelper::write_log($this->instance->subscription->user_id . "/payment.txt", "{$this->method} {$data}");
        }
    }

    private function handleError($msg)
    {
        return [
            'result' => 0,
            'message' => $msg
        ];
    }
}
