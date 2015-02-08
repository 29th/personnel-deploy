<?php
$config = array(
    'personnel-app' => array(
        'production_dir' => getenv('PERSONNEL_APP_PRODUCTION_DIR'),
        'staging_dir' => getenv('PERSONNEL_APP_STAGING_DIR'),
        'backup_dir' => getenv('PERSONNEL_APP_BACKUP_DIR'),
        'update_script' => './update.sh'
    ),
    'personnel-api' => array(
        'production_dir' => getenv('PERSONNEL_API_PRODUCTION_DIR'),
        'staging_dir' => getenv('PERSONNEL_API_STAGING_DIR'),
        'backup_dir' => getenv('PERSONNEL_API_BACKUP_DIR'),
        'update_script' => './update.sh'
    ),
    'forums' => array(
        'production_dir' => getenv('FORUMS_PRODUCTION_DIR'),
        'staging_dir' => getenv('FORUMS_PRODUCTION_DIR'),
        'backup_dir' => getenv('FORUMS_PRODUCTION_DIR'),
        'update_script' => './update.sh'
    )
);
//$app = sizeof($argv) > 1 ? $argv[1] : null;
$app = isset($_GET['q']) ? $_GET['q'] : null;
if( ! $app || ! isset($config[$app])) {
    http_response_code(404);
    die("Configuration unknown for application '{$app}'\n");
}
$paths = $config[$app];

// Verify request is authentic (from GitHub)
$headers = getallheaders();
$hubSig = $headers['X-Hub-Signature'];
list($algo, $hash) = explode('=', $hubSig, 2);

$payload = file_get_contents('php://input');

$payloadHash = hash_hmac($algo, $payload, getenv('SECRET'));
if($hash !== $payloadHash) {
	http_response_code(403);
	die('Invalid signature');
}

// Create staging directory
echo shell_exec('set -x ; rm -rf ' . $paths['staging_dir']);
echo shell_exec('set -x ; mkdir --parents $(dirname ' . $paths['staging_dir'] . ')');
echo shell_exec('set -x ; cp -R ' . $paths['production_dir'] . ' ' . $paths['staging_dir']);

// Execute update script inside staging directory
chdir($paths['staging_dir']);
echo shell_exec('bash ' . $paths['update_script']);
echo "Staging directory built\n";

// Backup previous build
echo shell_exec('set -x ; rm -rf ' . $paths['backup_dir']);
echo shell_exec('set -x ; mkdir --parents $(dirname ' . $paths['backup_dir'] . ')');
echo shell_exec('set -x ; mv ' . $paths['production_dir'] . ' ' . $paths['backup_dir']);
echo "Previous build backed up\n";

// Move staging to production
echo shell_exec('set -x ; mkdir --parents $(dirname ' . $paths['production_dir'] . ')');
echo shell_exec('set -x ; mv ' . $paths['staging_dir'] . ' ' . $paths['production_dir']);
echo "Staging build moved to production\n";