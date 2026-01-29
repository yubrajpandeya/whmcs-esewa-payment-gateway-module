<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

# Require libraries
require_once __DIR__ . '/esewaV2/init.php';

function esewaV2_MetaData()
{
    return array(
        'DisplayName' => 'eSewa Payment Gateway',
        'APIVersion' => '2.0',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function esewaV2_config()
{
    return [
        "FriendlyName" => ["Type" => "System", "Value" => "eSewa V2"],
        "product_code" => [
            "FriendlyName" => "Product Code (Merchant Code)",
            "Type" => "text",
            "Size" => "20",
        ],
        "secret_key" => [
            "FriendlyName" => "Secret Key",
            "Type" => "password",
            "Size" => "50",
        ],
        "testmode" => [
            "FriendlyName" => "Sandbox Mode",
            "Type" => "yesno",
            "Description" => "Tick to enable test (UAT) mode",
        ],
    ];
}

function esewaV2_link($params)
{
    // Parameters
    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $productCode = $params['testmode'] == 'on' ? 'EPAYTEST' : $params['product_code'];
    $secretKey = $params['testmode'] == 'on' ? '8gBm/:&EnhH.1/q' : $params['secret_key'];

    $tax = 0;
    $serviceCharge = 0;
    $deliveryCharge = 0;
    $totalAmount = $amount + $tax + $serviceCharge + $deliveryCharge;

    $uuid = $invoiceId . '-' . time();

    $fields = [
        'amount' => $amount,
        'tax_amount' => $tax,
        'product_service_charge' => $serviceCharge,
        'product_delivery_charge' => $deliveryCharge,
        'total_amount' => $totalAmount,
        'transaction_uuid' => $uuid,
        'product_code' => $productCode,
    ];

    $signedFieldNames = "total_amount,transaction_uuid,product_code";
    $signData = "total_amount=$totalAmount,transaction_uuid=$uuid,product_code=$productCode";
    $signature = base64_encode(hash_hmac('sha256', $signData, $secretKey, true));

    $url = $params['testmode'] == 'on'
        ? "https://rc-epay.esewa.com.np/api/epay/main/v2/form"
        : "https://epay.esewa.com.np/api/epay/main/v2/form";


   $successUrl = $params['systemurl'] . "/modules/gateways/callback/esewaV2.php";
    $failureUrl = $params['systemurl'] . "/payment-failed.html";

    // Form HTML
    $form = '<form method="POST" action="' . $url . '">';
    foreach ($fields as $name => $value) {
        $form .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '">';
    }
    $form .= '<input type="hidden" name="success_url" value="' . htmlspecialchars($successUrl) . '">';
    $form .= '<input type="hidden" name="failure_url" value="' . htmlspecialchars($failureUrl) . '">';
    $form .= '<input type="hidden" name="signed_field_names" value="' . $signedFieldNames . '">';
    $form .= '<input type="hidden" name="signature" value="' . $signature . '">';
    $form .= '<input type="submit" value="' . $params['langpaynow'] . '">';
    $form .= '</form>';

    return $form;
}
