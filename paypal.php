<?php


/**
 * PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *
 * Payment Gateway paypal.com
 *
 * created by @ibnux <me@ibnux.com>
 *
 **/


function paypal_validate_config()
{
    global $config;
    if (empty($config['paypal_client_id']) || empty($config['paypal_secret_key'])) {
        sendTelegram("PayPal payment gateway not configured");
        r2(U . 'order/package', 'w', Lang::T("Admin has not yet setup Paypal payment gateway, please tell admin"));
    }
}

function paypal_show_config()
{
    global $ui;
    $ui->assign('_title', 'Paypal - Payment Gateway');
    $ui->assign('currency', json_decode(file_get_contents('system/paymentgateway/paypal_currency.json'), true));
    $ui->display('paypal.tpl');
}


function paypal_save_config()
{
    global $admin, $_L;
    $paypal_client_id = _post('paypal_client_id');
    $paypal_secret_key = _post('paypal_secret_key');
    $paypal_currency = _post('paypal_currency');
    $d = ORM::for_table('tbl_appconfig')->where('setting', 'paypal_secret_key')->find_one();
    if ($d) {
        $d->value = $paypal_secret_key;
        $d->save();
    } else {
        $d = ORM::for_table('tbl_appconfig')->create();
        $d->setting = 'paypal_secret_key';
        $d->value = $paypal_secret_key;
        $d->save();
    }
    $d = ORM::for_table('tbl_appconfig')->where('setting', 'paypal_client_id')->find_one();
    if ($d) {
        $d->value = $paypal_client_id;
        $d->save();
    } else {
        $d = ORM::for_table('tbl_appconfig')->create();
        $d->setting = 'paypal_client_id';
        $d->value = $paypal_client_id;
        $d->save();
    }
    $d = ORM::for_table('tbl_appconfig')->where('setting', 'paypal_currency')->find_one();
    if ($d) {
        $d->value = $paypal_currency;
        $d->save();
    } else {
        $d = ORM::for_table('tbl_appconfig')->create();
        $d->setting = 'paypal_currency';
        $d->value = $paypal_currency;
        $d->save();
    }
    _log('[' . $admin['username'] . ']: Paypal ' . $_L['Settings_Saved_Successfully'], 'Admin', $admin['id']);

    r2(U . 'paymentgateway/paypal', 's', $_L['Settings_Saved_Successfully']);
}

function paypalGetAccessToken()
{
    global $config;
    $result = Http::postData(paypal_get_server() . 'oauth2/token', [
        "grant_type" => "client_credentials"
    ], [], $config['paypal_client_id'] . ":" . $config['paypal_secret_key']);
    $json = json_decode($result, true);
    //if()
}


function paypal_get_server()
{
    global $_app_stage;
    if ($_app_stage == 'Live') {
        return 'https://api-m.paypal.com/v1/';
    } else {
        return 'https://api-m.sandbox.paypal.com/v1/';
    }
}
