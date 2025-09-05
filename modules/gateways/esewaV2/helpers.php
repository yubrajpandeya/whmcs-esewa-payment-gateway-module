<?php

/**
 * WHMCS eSewa Payment Gateway Helper Functions
 * For more information, please refer to the online documentation.
 * @see https://github.com/yubrajpandeya/whmcs-esewa-payment-gateway-module
 * @copyright Copyright (c) Yubraj Pandeya
 * @author : @yubrajpandeya
 */

/**
 * Redirect page
 * 
 * @param string url
 * @return redirect
 */ 
function redirect($path) 
{
    header('location: '.$path);
    exit();
}

/**
 * Encode invoice to pass a unique Invoice number
 * 
 * @param int length
 * @return string
 */
function encodeInvoice($invoiceId)
{
    $encode = randomString(7).'-'.$invoiceId;

    return $encode;
}

/**
 * Decode invoice number
 * 
 * @param int length
 * @return string
 */
function decodeInvoice($string)
{
    return substr($string, 8);
}

/**
 * Create random string
 * 
 * @param int leng
 * @return string
 */
function randomString($leng=100) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str_len = strlen($chars);
    $random = '';

    for($i=0; $i<$leng; $i++) {
        $random .= $chars[rand(0, $str_len-1)];
    }

    return $random;
}

function generateSignature(string $secretKey, array $data): string
{
    $signedFields = "total_amount={$data['amount']}," .
        "transaction_uuid={$data['transaction_uuid']}," .
        "product_code={$data['product_code']}";

    return base64_encode(hash_hmac('sha256', $signedFields, $secretKey ?? '', true));
}

/**
 * Decrypt the signature for the payment.
 *
 * @param string $data
 * @return array
*/
function decodeSignature(string $data): array
{
    return json_decode(base64_decode($data), true);
}
