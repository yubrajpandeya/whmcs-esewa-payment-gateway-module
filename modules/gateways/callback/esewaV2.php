<?php

/**
 * eSewa Payment Gateway WHMCS Module Callback Handler
 * 
 * @see https://yubrajpandeya.com.np
 * 
 * @author : @yubrajpandeya
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = "esewaV2";
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams["type"]) {
    die("Module Not Activated");
}

// Accept GET or POST
$dataEncoded = $_GET['data'] ?? null;

if ($dataEncoded) {
    $decoded = json_decode(base64_decode($dataEncoded), true);

    if (!$decoded || !isset($decoded['transaction_uuid']) || !isset($decoded['status'])) {
        logTransaction("eSewa V2", $_GET, "Invalid or Malformed Data");
        die("Invalid data.");
    }

    $transactionUuid = $decoded['transaction_uuid'] ?? $decoded['transaction_code'];
    $invoiceId = explode("-", $transactionUuid)[0];
    $paymentAmount = $decoded['total_amount'];
    $status = $decoded['status'];
    $receivedSignature = $decoded['signature'];
    $signedFields = explode(',', $decoded['signed_field_names']);
    $productCode = $decoded['product_code'];

    // Rebuild signature data
    $signatureData = '';
    foreach ($signedFields as $field) {
        if (isset($decoded[$field])) {
            $signatureData .= $field . '=' . $decoded[$field] . ',';
        }
    }
    $signatureData = rtrim($signatureData, ',');

    $secretKey = $gatewayParams['testmode'] == 'on' ? '8gBm/:&EnhH.1/q' : $gatewayParams['secret_key'];
    $generatedSignature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));

    if (hash_equals($generatedSignature, $receivedSignature) && $status === "COMPLETE") {
        addInvoicePayment($invoiceId, $transactionUuid, $paymentAmount, 0, $gatewayModuleName);
        logTransaction("eSewa V2", $decoded, "Successful");
    } else {
        logTransaction("eSewa V2", $decoded, "Invalid Signature or Status");
    }

    header("Location: " . $gatewayParams['systemurl'] . "/viewinvoice.php?id=" . $invoiceId);
    exit;

} else {
    logTransaction("eSewa V2", $_REQUEST, "No `data` parameter provided");
    die("Invalid request.");
}
