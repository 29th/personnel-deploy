<?php
// This is working! Just need to move the below to env vars
$config = array(
    'personnel-app' => array(
        'production_dir' => '/var/www/html/personnel-app/',
        'staging_dir' => '/var/www/html/staging-personnel-app/',
        'backup_dir' => '/var/www/html/backup-personnel-app/',
        'update_script' => './update.sh'
    ),
    'personnel-api' => array(
        'production_dir' => '/var/www/html/personnel-api/',
        'staging_dir' => '/var/www/html/staging-personnel-api/',
        'backup_dir' => '/var/www/html/backup-personnel-api/',
        'update_script' => './update.sh'
    )
);
$paths = sizeof($argv) > 1 ? $config[$argv[1]] : $config['personnel-app'];

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
echo shell_exec('set -x ; rm -rf ' . $paths['staging_dir']);
echo shell_exec('set -x ; cp -R ' . $paths['production_dir'] . ' ' . $paths['staging_dir']);

// Execute update script inside staging directory
chdir($paths['staging_dir']);
echo shell_exec('bash ' . $paths['update_script']);

// Backup previous build
echo shell_exec('set -x ; rm -rf ' . $paths['backup_dir']);
echo shell_exec('set -x ; mv ' . $paths['production_dir'] . ' ' . $paths['backup_dir']);

// Move staging to production
echo shell_exec('set -x ; mv ' . $paths['staging_dir'] . ' ' . $paths['production_dir']);