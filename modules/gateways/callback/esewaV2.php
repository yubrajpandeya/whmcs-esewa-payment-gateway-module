<?php

/**
 * eSewa Payment Gateway WHMCS Module Callback Handler
 * 
 * @author : @yubrajpandeya
 */

use WHMCS\Database\Capsule;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

# Require libraries
require_once __DIR__ . '/../esewaV2/init.php';

$gatewayModuleName = "esewaV2";
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams["type"]) {
    die("Module Not Activated");
}

// Accept GET
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
    $receivedSignature = $decoded['signature'] ?? '';
    $signedFields = isset($decoded['signed_field_names']) ? explode(',', $decoded['signed_field_names']) : [];
    $productCode = $decoded['product_code'] ?? '';

    // Validate invoice ID
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

    // Verify transaction is unique
    checkCbTransID($transactionUuid);

    // Validate invoice amount
    $invoice = WHMCS\Billing\Invoice::find($invoiceId);
    if (!$invoice || $invoice->total != $paymentAmount) {
        logTransaction("eSewa V2", $decoded, "Amount Mismatch");
        header("Location: " . $gatewayParams['systemurl'] . "/viewinvoice.php?id=" . $invoiceId . "&paymentfailed=true");
        exit;
    }

    // Rebuild signature data
    $signatureData = '';
    foreach ($signedFields as $field) {
        if (isset($decoded[$field])) {
            $signatureData .= $field . '=' . $decoded[$field] . ',';
        }
    }
    $signatureData = rtrim($signatureData, ',');

    $secretKey = ($gatewayParams['test_mode'] == 'on')
        ? $gatewayParams['test_secret_key']
        : $gatewayParams['secret_key'];

    $generatedSignature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));

    // Verify with eSewa API (extra layer of validation)
    $url = $gatewayParams['test_mode'] == 'on' 
        ? 'https://rc.esewa.com.np/api/epay/transaction/status/' 
        : 'https://epay.esewa.com.np/api/epay/transaction/status/';

    $verifyUrl = $url . '?' . http_build_query($decoded);

    $ch = curl_init($verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $verifyResponse = json_decode($result, true);
    $verifiedStatus = $verifyResponse['status'] ?? '';

    if (hash_equals($generatedSignature, $receivedSignature) && $status === "COMPLETE" && $verifiedStatus === "COMPLETE") {
        addInvoicePayment($invoiceId, $transactionUuid, $paymentAmount, 0, $gatewayModuleName);
        logTransaction("eSewa V2", $decoded, "Successful");

        header("Location: " . $gatewayParams['systemurl'] . "/viewinvoice.php?id=" . $invoiceId . "&paymentsuccess=true");
    } else {
        logTransaction("eSewa V2", $decoded, "Invalid Signature/Status or Verification Failed");
        header("Location: " . $gatewayParams['systemurl'] . "/viewinvoice.php?id=" . $invoiceId . "&paymentfailed=true");
    }
    exit;

} else {
    logTransaction("eSewa V2", $_REQUEST, "No `data` parameter provided");
    die("Invalid request.");
}
