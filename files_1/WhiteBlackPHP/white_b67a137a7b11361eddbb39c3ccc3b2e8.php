<?php

class StripeService
{
    static function init(&$doorGets)
    {
        $mail = null;
        if (!is_object($doorGets) || !is_array($doorGets->configWeb)) {
            return 'PHPMailerService: doorGets object not found';
        }
        $config = $doorGets->configWeb;
        if ($config['stripe_active']) {
            $stripe = array("secret_key" => $config['stripe_secret_key'], "publishable_key" => $config['stripe_publishable_key']);
            \Stripe\Stripe::setApiKey($stripe['secret_key']);
            return true;
        }
        return false;
    }
}
