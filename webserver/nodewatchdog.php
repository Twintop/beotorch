<?php
$_SERVER['DOCUMENT_ROOT'] = '/home/beotorch/beotorch.com';

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$computerAPIKey = "8c317370-dba6-11e5-aef8-080027e16b71";
$ipaddress = "127.0.0.1";

if ($stmt = $mysqli->prepare("SELECT TIMESTAMPDIFF(SECOND,ConnectionLogTime,NOW()) as LastConnection FROM ConnectionLog ORDER BY ConnectionLogId DESC LIMIT 1")) {
	//$stmt->bind_param('ss', $computerAPIKey, $ipaddress);  // Bind ComputerId to parameter.
	$stmt->execute();	// Execute the prepared query.
	$stmt->store_result();

	clearStoredResults($mysqli);

	if ($stmt->num_rows == 1)
	{	
		$lastConnection = fetchMysqlRows($stmt);
	}	
}

if ($lastConnection[0]["LastConnection"] > (15*60))
{
	$toAddress1 = "youremail@address.com";
	$subject = "ALL BEOTORCH NODES DOWN";

	$headers   = array();
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/plain; charset=iso-8859-1";
	$headers[] = "From: Beotorch <noreply@beotorch.com>";
	$headers[] = "Reply-To: Beotorch <noreply@beotorch.com>";
	$headers[] = "Subject: {$subject}";
	$headers[] = "X-Mailer: PHP/".phpversion();

	$emailBody = "Last node contact was " . gmdate("H:i:s", $lastConnection[0]["LastConnection"]) . " ago.";

	echo $emailBody;

	$mailSent = mail($toAddress1, $subject, $emailBody, implode("\r\n", $headers));
	$mailSent = mail($toAddress2, $subject, $emailBody, implode("\r\n", $headers));
}

?>
