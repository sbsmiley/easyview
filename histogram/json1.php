<?php
/**
 ** create waiting list queue data feed in json format for extia
 ** requires the data to be in session variable $_SESSION['JSON_QUEUE'] from index.php
 ** the variable is 
 **
* @package easyview report
* @copyright 2014 UC Regents
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
 **/
require 'config_path.php';

if ( !array_key_exists('JSON_CHART1', $_SESSION) //&& !array_key_exists('JSON_CHART2', $_SESSION) &&
//	!array_key_exists('JSON_CHART3', $_SESSION) && !array_key_exists('JSON_CHART4', $_SESSION) &&
//	!array_key_exists('JSON_CHART5', $_SESSION) 
	) {
    exit;
}

################################
### BEGIN OUTPUT TO BROWSER
###
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Fri, 01 Jul 2011 00:00:00 GMT"); // Date in the past
header("Content-type: application/JSON; charset=utf-8");

$data1 = $_SESSION['JSON_CHART1'];
$_SESSION['JSON_CHART1'] = null;
unset($_SESSION['JSON_CHART1']);
$status1 = empty($data1) ? 'false' : 'true';
?>{"status": <?php echo $status1; ?>, "chart1": [<?php echo $data1; ?>]}



