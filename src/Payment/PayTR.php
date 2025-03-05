<?php

namespace Webkul\PayTR\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class PayTR extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'paytr';

    public function getRedirectUrl(): string
    {
        return route('paytr.redirect');
    }

    /**
     * Returns payment method image.
     */
    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/money-transfer.png', 'shop');
    }
}
