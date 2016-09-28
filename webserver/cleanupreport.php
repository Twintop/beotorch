<?php
$_SERVER['DOCUMENT_ROOT'] = '/home/beotorch/beotorch.com';

include_once $_SERVER['DOCUMENT_ROOT'] . '/beotorch-config.php';

$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);

$mysqli->set_charset('utf8');
$mysqli->query("SET NAMES utf8");
$mysqli->query("SET @@session.time_zone='+00:00'");
$mysqli->query("SET @IPAddress = '" . $mysqli->real_escape_string($_SERVER['REMOTE_ADDR']) . "'");

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$computerAPIKey = "8c317370-dba6-11e5-aef8-080027e16b71";
$ipaddress = "127.0.0.1";

if ($stmt = $mysqli->prepare("CALL ReportsToArchiveList(null, ?, ?)")) {
	$stmt->bind_param('ss', $computerAPIKey, $ipaddress);  // Bind ComputerId to parameter.
	$stmt->execute();	// Execute the prepared query.
	$stmt->store_result();

	clearStoredResults($mysqli);

	if ($stmt->num_rows > 0)
	{	
		$reportsList = fetchMysqlRows($stmt);
	}	
}

if ($reportsList) {
	foreach ($reportsList as $report) {
		unlink(WAREHOUSE_FOLDER . $report["SimulationGUID"] . ".html");
	
		if ($stmt = $mysqli->prepare("CALL ArchiveReport(null, ?, ?, ?)")) {
			$stmt->bind_param('sis', $computerAPIKey, $report["SimulationId"], $ipaddress);
			$stmt->execute();
			$stmt->store_result();

			clearStoredResults($mysqli);

			if ($stmt->num_rows == 1)
			{	
				$resultRow = fetchMysqlRows($stmt);
				echo $report["SimulationId"] . ", " . $report["UserId"] . ", " . $report["SimulationGUID"] . ", " . $report["DaysBeforeCleanup"] . ", " . $report["DateDiff"] . "\n";
			}
		}
	}
}

?>


