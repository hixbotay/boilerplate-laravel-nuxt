<?php

namespace App\Http\Controllers;

use App\Helpers\Qrcode;
use App\Http\Enums\PaymentStatus;
use App\Models\Config;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentTransaction::filter($request)->with('user')->with('order');

        $res = [
            'page' => $query->getPageNumber(),
            'per_page' =>  $query->getPerPage(),
        ];
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }

		return response()->json($res);
    }


    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $body = $request->validate(
            [
                "total" => "required",
                "tx_id" => "required|string",
                "status" => "required|in:".implode(',',PaymentStatus::getAllValue()),
                "user_id" => "required",
                "order_id" => 'nullable',
                "data" => "required|string"
            ]
        );

        $payment = PaymentTransaction::create($body);
        if($payment->order_id){
            $payment->order = Order::find($payment->order_id);
        }

        return response()->json($payment);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $template_id
     * @return \Illuminate\Http\Response
     */
    public function show($payment)
    {
        return response()->json(PaymentTransaction::with('order')->with('user')->find($payment));
    }

    public function update(Request $request, PaymentTransaction $payment)
    {
        $body = (array) json_decode($request->getContent());

        $body = $request->validate(
            [
                "total" => "required",
                "tx_id" => "required|string",
                "status" => "required|in:".implode(',',PaymentStatus::getAllValue()),
                "user_id" => "required",
                "order_id" => 'nullable',
                "data" => "required|string"
            ]
        );

        $payment->update($body);

        return response()->json($payment);
    }

    public function destroy(Request $request, PaymentTransaction $payment)
    {
        if ($payment->delete()) {
            return response(['message' => 'Record is deleted']);
        }
        return response()->json(['message' => 'Delete error'], 405);
    }

    public function getQrCode(Request $request)
    {
        // https://img.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-<TEMPLATE>.png?amount=<AMOUNT>&addInfo=<DESCRIPTION>&accountName=<ACCOUNT_NAME>
        $request->validate([
            'amount' => 'required|numeric',
        ]);
        $default = array(
            "bank_number" => "",
            "bank_name" => "",
            "bank_address" => "",
            "bank_user_name" => ""
        );
        $admin_bank = Config::getOptions('admin_bank', $default);
        $amount = $request->amount;
        // $tx_id
        $tx_td = PaymentTransaction::getTxId();
        $qrcode = Qrcode::createQrCode($admin_bank, $amount, $tx_td);
        $admin_bank['amount'] = $amount;
        $data = array(
            'qr_code' => $qrcode,
            "bank_number" => $admin_bank['bank_number'],
            "bank_name" => $admin_bank['bank_name'],
            "bank_address" => $admin_bank['bank_address'],
            "bank_user_name" => $admin_bank['bank_user_name'],
            "amount" => $amount,
            'tx_id' => $tx_td
        );
      
        return response()->json($data);
    }

    public function createTransaction(Request $request) {
        // Xử lý cho việc nạp tiền vào tài khoản
        $request->validate([
            'amount' => 'required|numeric',
            'tx_id' => 'required'
        ]);
        $order_id = 0;

        $data = array(
            "total" => $request->amount,
            "tx_id" => $request->tx_id,
            "status" => PaymentStatus::WAITING['value'],
            "user_id" => $request->user()->id,
            "order_id" => $order_id,
            "data" => 'Nạp tiền vào tài khoản'
        );
        $status = PaymentTransaction::create($data);
        return response()->json($status);
    }

    public function purchaseApprove(Request $request, PaymentTransaction $paymentTransaction) {
        if ($paymentTransaction->status == PaymentStatus::WAITING['value']) {
            $paymentTransaction->status = PaymentStatus::APPROVED['value'];
            $paymentTransaction->update();
            
            $user = User::find($paymentTransaction->user_id);
            // update total_purchased
            $user->updateAmount($paymentTransaction->total)
            ->updateTotalPurchased($paymentTransaction->total);
            $user->update();
            
            return response()->json(array(
                'message' => 'success',
                'payment_transaction' => $paymentTransaction,
            ));
        }
        return response()->json(array(
            'message' => 'Giao dịch này cần phải có trạng thái là chờ giao dịch',
        ),400);
    }

    public function purchaseCancel(Request $request, PaymentTransaction $paymentTransaction) {
        if ($paymentTransaction->status == PaymentStatus::WAITING['value']) {
            $paymentTransaction->status = PaymentStatus::CANCEL['value'];
            $paymentTransaction->update();
            return response()->json(array(
                'message' => 'success',
                'payment_transaction' => $paymentTransaction
            ));
        }
        return response()->json(array(
            'message' => 'Cannot change status',
        ),400);
    }

    public function getClientTransactions(Request $request) {
        $user = Auth::user();
        
        $query = PaymentTransaction::getByUser($user->id)->filter($request);

        $res = [
            'records' => [],
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
            'total_records' => 0
        ];
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }
        return response()->json($res);
    }

    public function getClientTransactionDetail(Request $request, $transactionId) {
        
        $user = Auth::user();
        $transaction = PaymentTransaction::find($transactionId);
        if ($transaction->user_id == $user->id) {
            return response()->json($transaction);
        }
        return response()->json(['message' => 'Not found']);
    }

    public function getTransactionHistory(Request $request) {
        $query = PaymentTransaction::filter(['per_page' => 20, 'page' => 1])
        ->select(['id','data','created_at']);
        if (!empty($request->is_purchase)) {
            $query->isPurchase();
        }
        if ($request->is_buy) {
            $query->isBuy();
        }
        return response()->json([
            'is_purchase' => $request->is_purchase,
            'is_buy' => $request->is_buy,
            'records' => $query->get(),
        ]);
    }
    
    public function summaryTotal(Request $request) {
        $totalPaid = PaymentTransaction::select('total')->isStatusApproved()->isBuy()->get()->sum('total');
        $totalPurchased = PaymentTransaction::select('total')->isStatusApproved()->isPurchase()->get()->sum('total');
        $totalUser = User::isNotAdmin()->isStatus()->count();
        return response()->json([
            'amount_deposited' => $totalPurchased,
            'amount_spent' => $totalPaid,
            'amount_user' => $totalUser
        ]);
    }

    public function summaryToday(Request $request) {
        $today = getdate(time())['mday'];
        $totalPaid = PaymentTransaction::select('total')->isStatusApproved()->isBuy()->getByDay($today)->get()->sum('total');
        $totalPurchased = PaymentTransaction::select('total')->isStatusApproved()->isPurchase()->getByDay($today)->get()->sum('total');
        $totalUser = User::isNotAdmin()->isStatus()->getByDay($today)->count();
        
        return response()->json([
            'amount_deposited' => $totalPurchased,
            'amount_spent' => $totalPaid,
            'amount_user' => $totalUser,
        ]);
    }

    
}
