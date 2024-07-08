<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Enums\PaymentStatus;
use App\Http\Enums\ProductItemStatus;
use App\Models\Country;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\User;
use App\Modules\Payment\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->filter($request);

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

    public function store(Request $request)
    {
        
        $data = $request->validate([
            'user_id' => 'required',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'product_id' => 'required|numeric',
            'count_product' => 'required|numeric'
        ]);
        return $this->confirmOrder($data);
    }

    public function clientBuy(Request $request){
        $data = $request->validate([
            'product_id' => 'required|numeric',
            'count_product' => 'required|numeric'
        ]);
        $user = $request->user();
        $data['user_id'] = $user->id;
        $data['email'] = $user->email;
        $data['phone'] = $user->phone ? $user->phone : '+84';
        return $this->confirmOrder($data);
    }

    private function confirmOrder($data)
    {
        $data['product'] = Product::find($data['product_id']);
        $data['total'] = Product::getTotalPrice($data['product'], $data['count_product']);
        $data['discount'] = 0;
        $data['payment_status'] = PaymentStatus::APPROVED['value'];
        try {
            $user = User::find($data['user_id']);
            $data['user'] = $user;
            if ($user->amount < $data['total'])  {
                return response()->json(array('message' => 'Tài khoản chưa đủ tiền để thực hiện giao dịch!'), 400);
            }
           
            DB::beginTransaction();
            // Add order
            $data['order'] = Order::create(array(
                'user_id' => $data['user_id'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'total' => $data['total'],
                'discount' => $data['discount'],
                'payment_status' => $data['payment_status']
            ));

            /**
             * Add oder_product_item and order_product
             */
            
            // return collections list product_item
            $data['list_product_item'] = OrderProductItem::createOrderProductItem($data['product_id'], $data['count_product'], $data['order']->id);
            $numberItem = $data['list_product_item']->count();
            if ($numberItem < $data['count_product'])  {
                return response()->json(array('message' => 'Số lượng tài khoản khổng đủ. Số lượng còn lại '.$numberItem), 400);
            }
            // Tránh trường hợp không đủ account để bán
            OrderProduct::create(array(
                'order_id' => $data['order']->id,
                'product_id' => $data['product_id'],
                'quantity' => $numberItem
            ));
            if ($data['count_product'] != $data['list_product_item']->count()) {
                $data['count_product'] = $data['list_product_item']->count();
                $data['order']->total = Product::getTotalPrice($data['product'], $data['count_product']);
                $data['order']->update();
                $data['total'] = Product::getTotalPrice($data['product'], $data['count_product']);
            }
            Product::updateQuantity($data['product_id']);
            
            // Add transaction
            $resultTransction = PaymentTransaction::create(array(
                "total" => $data['total'],
                "tx_id" => PaymentTransaction::getTxId(),
                "status" => PaymentStatus::APPROVED['value'],
                "user_id" => $data['user_id'],
                "order_id" => $data['order']->id,
                'data' => 'Mua '.$data['count_product'].' Tài khoản '.$data['product']->name
            ));
            // Update Change Money user
            $user->updateAmount(-$data['total'])->updateTotalPaid( $data['total'])->update();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('message' => $e->getMessage()), 400);
        }
        // Send Email
        EmailTemplate::sendMailAddOrder($data);
        return response()->json(Order::with('orderProductItem')->find($data['order']->id));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'total' => 'required',
            'payment_status' => 'required|in:' . implode(',', PaymentStatus::getAllValue()),
        ]);
        try {
            $order->update($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        return response()->json($order);
    }

    public function show(Request $request, $order_id)
    {
        return response()->json(Order::with('orderProductItem', 'user')->find($order_id));
    }

    public function destroy(Request $request, Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Delete success'], 202);
    }

    public function getClientOrders(Request $request) {
        $user = Auth::user();
        $query = Order::with('paymentTransaction')->filter($request);

        if ($request->has('keyword')) {
            $query->getByTxId($request->keyword, 'OR');
        } 
        $query->getByUser($user->id);
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
    public function getClientOrderDetail(Request $request, $order_id) {
        $user = Auth::user();
        $order = Order::with('orderProductItem', 'paymentTransaction', 'product')->find($order_id);
        if ($order->user_id != $user->id) {
            return response()->json(['message' => 'User does not have this order '.$order_id]);
        }
        return response()->json($order);

    }

    public function listOrderProductItem(Request $request) {
        $query = OrderProductItem::filter($request);

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

}