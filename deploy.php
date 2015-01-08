<?php
$working_dir = getenv('WORKING_DIR') ?: '../personnel-app/';
$staging_dir = '../staging-personnel-app/';
$update_script = './update.sh';

// Verify request is authentic (from GitHub)
/*$headers = getallheaders();
$hubSig = $headers['X-Hub-Signature'];
list($algo, $hash) = explode('=', $hubSig, 2);

$payload = file_get_contents('php://input');

$payloadHash = hash_hmac($algo, $payload, getenv('SECRET'));
if($hash !== $payloadHash) {
	http_response_code(403);
	die('Invalid signature');
}*/

// Create staging directory
echo shell_exec('set -x ; rm -rf ' . $staging_dir);
echo shell_exec('set -x ; cp -R ' . $working_dir . ' ' . $staging_dir);
chdir($staging_dir);

// Execute update script
echo shell_exec('bash ' . $update_script);