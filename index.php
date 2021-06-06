<?php

require 'vendor/autoload.php';

use Tracy\Debugger as Debugger;

Debugger::enable(Debugger::DEVELOPMENT);

define("PLAIN_STRING", "fadilxcoder");
define("ENCRYPTION_KEY",  getToken(64));
define('IV', openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')));

function getToken($length) {
	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet.= "0123456789";
	for($i=0;$i<$length;$i++) {
		$token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
	}

	return $token;
}

function encrypt($key, $payload) {
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	$encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);

	return base64_encode($encrypted . '::' . $iv);
}

function decrypt($key, $garble) {
    list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);

    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

function crypto_rand_secure($min, $max) {
	$range = $max - $min;
	if ($range < 0) return $min; // not so random...
	
	$log = log($range, 2);
	$bytes = (int) ($log / 8) + 1; // length in bytes
	$bits = (int) $log + 1; // length in bits
	$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	do {
		$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		$rnd = $rnd & $filter; // discard irrelevant bits
	} 
	while ($rnd >= $range);
	return $min + $rnd;
}

$encrypted = openssl_encrypt(PLAIN_STRING, "AES-256-CFB", ENCRYPTION_KEY, 0, IV);
$decrypted = openssl_decrypt($encrypted, "AES-256-CFB", ENCRYPTION_KEY, 0, IV);

$encpt_string = encrypt(ENCRYPTION_KEY, PLAIN_STRING);
$decpt_string = decrypt(ENCRYPTION_KEY, $encpt_string);

echo "<pre><br><hr><br>";

echo '<b>ENCRYPTION KEY : </b>' . ENCRYPTION_KEY;
echo "<br><br>";

echo '<b>ENCRYPTED STRING : </b>' . $encrypted;
echo "<br><br>";

echo '<b>DECRYPTED STRING : </b>' . $decrypted;
echo "<br><br>";

echo '<b>PRIVATE KEY : </b>' . ENCRYPTION_KEY;
echo "<br><br>";

echo '<b>PUBLIC KEY : </b>' . $encrypted;
echo "<br><br>";

echo '<b>LICENSE KEY GENERATOR : </b>' . getToken(128);
echo "<br><br>";

echo '<b>ENCRYPT FUNC : </b>' . $encpt_string;
echo "<br><br>";

echo '<b>DECRYPT FUNC : </b>' . $decpt_string;
echo "<br><br>";

echo "<br><hr><br>";

dump(IV);
?>