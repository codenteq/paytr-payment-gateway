<?php

namespace Webkul\PayTR\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository   $orderRepository,
        protected InvoiceRepository $invoiceRepository
    )
    {
        //
    }

    /**
     * Redirects to the PayTR server.
     *
     * \Illuminate\Contracts\View\View
     * \Illuminate\Foundation\Application
     * \Illuminate\Contracts\View\Factory
     * \Illuminate\Contracts\Foundation\Application
     */
    public function redirect(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $cart = Cart::getCart();
        $address = $cart->billing_address;
        $user = Customer::find($cart->customer_id);

        $merchant_id = env('PAYTR_MERCHANT_ID', 'null');
        $merchant_key = env('PAYTR_MERCHANT_KEY', 'null');
        $merchant_salt = env('PAYTR_MERCHANT_SALT', 'null');

        $email = $user['email'];

        $payment_amount = $cart['grand_total'] * 100;

        $merchant_oid = rand();

        $user_name = $cart['customer_first_name'] . ' ' . $cart['customer_last_name'];

        $user_address = $address['address'];

        $user_phone = $user['phone'];

        $merchant_ok_url = route('paytr.callback');

        $merchant_fail_url = route('paytr.cancel');

        $user_baskets = [];
        foreach ($cart['items'] as $product) {
            $user_baskets[] = [
                $product['name'],
                number_format($product['total'], 2, '.', ''),
                $product['quantity']
            ];
        }


        $user_basket = base64_encode(json_encode($user_baskets));

        if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        $user_ip = $ip;

        $timeout_limit = "30";

        $debug_on = 0;

        $test_mode = 0;

        $no_installment = 0;

        $max_installment = 0;

        $currency = $cart['cart_currency_code'];

        $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));
        $post_vals = array(
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $email,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => $debug_on,
            'no_installment' => $no_installment,
            'max_installment' => $max_installment,
            'user_name' => $user_name,
            'user_address' => $user_address,
            'user_phone' => $user_phone,
            'merchant_ok_url' => $merchant_ok_url,
            'merchant_fail_url' => $merchant_fail_url,
            'timeout_limit' => $timeout_limit,
            'currency' => $currency,
            'test_mode' => $test_mode
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = @curl_exec($ch);

        if (curl_errno($ch))
            die("PAYTR IFRAME connection error. err:" . curl_error($ch));

        curl_close($ch);

        $result = json_decode($result, 1);

        if ($result['status'] == 'success')
            $token = $result['token'];
        else
            die("PAYTR IFRAME failed. reason:" . $result['reason']);

        return view('paytr::iframe', compact('token'));
    }

    /**
     * Redirects to the PayTR server.
     */
    public function callback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $post = $request->all();

        $merchant_key = env('PAYTR_MERCHANT_KEY', 'null');
        $merchant_salt = env('PAYTR_MERCHANT_SALT', 'null');

        $hash = base64_encode( hash_hmac('sha256', $post['merchant_oid'].$merchant_salt.$post['status'].$post['total_amount'], $merchant_key, true) );

        if( $hash != $post['hash'] )
            die('PAYTR notification failed: bad hash');

        if( $post['status'] == 'success' ) {
            return redirect()->route('paytr.success');
        } else {
            return redirect()->route('paytr.cancel');
        }
    }

    /**
     * Place an order and redirect to the success page.
     *
     * @throws \Exception
     */
    public function success(): \Illuminate\Http\RedirectResponse
    {
        $cart = Cart::getCart();

        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        if ($order->canInvoice()) {
            $this->invoiceRepository->create($this->prepareInvoiceData($order));
        }

        Cart::deActivateCart();

        session()->flash('order_id', $order->id);

        return redirect()->route('shop.checkout.onepage.success');
    }

    /**
    /**
     * Redirect to the cart page with error message.
     */
    public function failure(): \Illuminate\Http\RedirectResponse
    {
        session()->flash('error', 'PayTR payment was either cancelled or the transaction failed.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Prepares order's invoice data for creation.
     */
    protected function prepareInvoiceData($order): array
    {
        $invoiceData = [
            'order_id' => $order->id,
            'invoice'  => ['items' => []],
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
