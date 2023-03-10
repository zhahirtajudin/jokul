<?php

$input = json_decode(file_get_contents("php://input"), true);

$requestBody = array (
    'order' => array (
        'invoice_number' => '111000' // Change to your business logic
    ),
    'payment' => array (
        'original_request_id' => '111000'
    ),
    'refund' => array (
        'amount' => 20000,
    ),
);

$requestId = rand(1, 100000); // Change to UUID or anything that can generate unique value
$dateTime = gmdate("Y-m-d H:i:s");
$isoDateTime = date(DATE_ISO8601, strtotime($dateTime));
$dateTimeFinal = substr($isoDateTime, 0, 19) . "Z";
$clientId = 'BRN-0252-1648456322620'; // Change with your Client ID
$secretKey = 'SK-2zQLDKEZE8IzWWGT0BS8'; // Change with your Secret Key

$getUrl = 'https://api-sandbox.doku.com';

$targetPath = '/cancellation/credit-card/refund';
$url = $getUrl . $targetPath;

// Generate digest
$digestValue = base64_encode(hash('sha256', json_encode($requestBody), true));

// Prepare signature component
$componentSignature = "Client-Id:".$clientId ."\n".
                    "Request-Id:".$requestId . "\n".
                    "Request-Timestamp:".$dateTimeFinal ."\n".
                    "Request-Target:".$targetPath ."\n".
                    "Digest:".$digestValue;

// Generate signature
$signature = base64_encode(hash_hmac('sha256', $componentSignature, $secretKey, true));

$finalSignature = "HMACSHA256=" . $signature;

//print body signature
echo "==== Request Body ==="."\n";
echo json_encode($requestBody)."\n";
echo "=== Component Signature ===" ."\n";
echo $componentSignature ."\n";
echo "=== Signature ==="."\n";
echo $finalSignature ."\n";
echo "==== API Response ===" ."\n";

// Execute request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Client-Id:' . $clientId,
    'Request-Id:' . $requestId,
    'Request-Timestamp:' . $dateTimeFinal,
    'Signature:' . "HMACSHA256=" . $signature,
));

// Set response json
$responseJson = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Echo the response
if (is_string($responseJson) && $httpCode == 200) {
    echo $responseJson;
    return json_decode($responseJson, true);
} else {
    echo $responseJson;
    return null;
}
