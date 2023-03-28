<?php
$headers = getallheaders();
$notificationPath = '/doku/notify_php/notify.php';
$secretKey = 'SK-2zQLDKEZE8IzWWGT0BS8';
$digest = base64_encode(hash('sha256', file_get_contents('php://input'), true));

$componentSignature = "Client-Id:" . $headers['Client-Id'] . "\n"
	                    . "Request-Id:" . $headers['Request-Id'] . "\n"
                        . "Request-Timestamp:" . $headers['Request-Timestamp'] . "\n"
	                    . "Request-Target:" . $notificationPath ."\n"
	                    . "Digest:" . $digest;
$signature = base64_encode(hash_hmac('sha256', $componentSignature, $secretKey,true));

$signatureHMAC ="HMACSHA256=".$signature ;

//Something to write to txt log

if ($signatureHMAC == $headers['Signature']) {

    // http_response_code(200);
    $log  = "Signature Generate: ".$signatureHMAC.PHP_EOL.
        "Signature DOKU: ".$headers['Signature'].PHP_EOL.
        "Berhasil".PHP_EOL.
        "-------------------------".PHP_EOL;
} else {

    // http_response_code(401);
    $log  = "Signature Generate: ".$signatureHMAC.PHP_EOL.
        "Signature DOKU: ".$headers['Signature'].PHP_EOL.
        "Gagal".PHP_EOL.
        "-------------------------".PHP_EOL;
}

// //Save string to log, use FILE_APPEND to append.
file_put_contents('./log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);

?>