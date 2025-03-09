<?php

namespace Webkul\PayTR\Listeners;

use Webkul\Admin\Listeners\Base;
use Webkul\Admin\Mail\Order\RefundedNotification;

class Refund extends Base
{
    /**
     * After order is created
     */
    public function afterCreated(\Webkul\Sales\Contracts\Refund $refund): void
    {
        $this->refundOrder($refund);

        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.new_refund')) {
                return;
            }

            $this->prepareMail($refund, new RefundedNotification($refund));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * After Refund is created
     */
    public function refundOrder(\Webkul\Sales\Contracts\Refund $refund): void
    {
        $order = $refund->order;

        $merchant_id = env('PAYTR_MERCHANT_ID', 'null');
        $merchant_key = env('PAYTR_MERCHANT_KEY', 'null');
        $merchant_salt = env('PAYTR_MERCHANT_SALT', 'null');

        $merchant_oid   = $order->payment->additional['order_id'];

        $return_amount  = $refund->grand_total;

        $paytr_token=base64_encode(hash_hmac('sha256',$merchant_id.$merchant_oid.$return_amount.$merchant_salt,$merchant_key,true));

        $post_vals=array('merchant_id'=>$merchant_id,
            'merchant_oid'=>$merchant_oid,
            'return_amount'=>$return_amount,
            'paytr_token'=>$paytr_token);

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/iade");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);

        $result = @curl_exec($ch);

        if(curl_errno($ch))
        {
            echo curl_error($ch);
            curl_close($ch);
            exit;
        }

        curl_close($ch);

        $result=json_decode($result,1);

        if($result['status']=='success')
        {
            echo 'ok';
        }
        else
        {
            echo $result['err_no']." - ".$result['err_msg'];
        }
    }
}
