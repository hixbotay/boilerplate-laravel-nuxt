<?php

namespace App\Modules\Payment;

use Illuminate\Http\Request;

interface PaymentMethodInterface
{
    /**
     * Setup default config for sellonboard
     * Example: Setup webhook, rate,...
     */
    public function setup();

    /**
     * Checkout an order
     */
    public function checkout($request);

    /**
     * Get latest status of transaction
     */
    public function getTxStatus($request);

    //some payment gateway need defined order in params so it need specific method to get order id
    public function getOrderIdFromNotify(Request $request, $tx_id = null);
    public function getOrderIdFromResponse(Request $request, $tx_id = null);

    public function refund($data);

    /**
     * Get preview data for onboarding
     * @param $user_id
     */
    public function getPreviewOnboardingData(int $user_id);

    /**
     * Onboarding with sellonboard KYC data
     * @param $user_id
     * @param $data
     */
    public function onboarding($user_id, $data);

    /**
     * Post action onboarding if needed
     * @param $user_id
     * @param $data
     */
    public function postOnboarding($user_id, $data);

    /**
     * Create new settlement
     */
    public function createSettlement($amount, $params, $autoDisableMID);

    /**
     * Sync status of settlements
     */
    public function syncStatusSettlements($user_id, $from, $to);

    /**
     * Create new subscription
     */
    public function createSubscription($method);

    /**
     * Handle callback of subscription
     */
    public function getSubscriptionIdFromNotify(Request $request);
}
