<?php
	/**
	 * GIT DEPLOYMENT SCRIPT
	 *
	 * Used for automatically deploying websites via github or bitbucket, more deets here:
	 *
	 *		https://gist.github.com/1809044
	 */
	
	// Verify request is authentic (from GitHub)
	$headers = getallheaders();
	$hubSig = $headers['X-Hub-Signature'];

	list($algo, $hash) = explode('=', $hubSig, 2);

	$payload = file_get_contents('php://input');
	$data = json_decode($payload);

	$payloadHash = hash_hmac($algo, $payload, getenv('SECRET'));
	if($hash !== $payloadHash) {
		http_response_code(403);
		die('Invalid signature');
	}
 
	// The commands
	$commands = array(
		'echo $PWD',
		'whoami',
		'rm -rf *',
		'git clone https://github.com/29th/personnel.git ./',
		'npm install',
		'bower install',
		'gulp',
	);
 
	// Set working directory
	if(getenv('WORKING_DIR')) chdir(getenv('WORKING_DIR'));

	// Run the commands for output
	$output = '';
	foreach($commands AS $command){
		// Run it
		$tmp = shell_exec($command);
		// Output
		//$output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
		//$output .= htmlentities(trim($tmp)) . "\n";
		$output .= "$ {$command}\n";
		$output .= trim($tmp) . "\n";
	}
 
	// Make it pretty for manual user access (and why not?)
/*
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>GIT DEPLOYMENT SCRIPT</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
 .  ____  .    ____________________________
 |/      \|   |                            |
[| <span style="color: #FF0000;">&hearts;    &hearts;</span> |]  | Git Deployment Script v0.1 |
 |___==___|  /              &copy; oodavid 2012 |
              |____________________________|
 
<?php echo $output; ?>
</pre>
</body>
</html>
*/

    echo 'GIT DEPLOYMENT SCRIPT';
    echo $output;