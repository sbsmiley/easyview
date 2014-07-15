<?php
/**
 ** create waiting list queue data feed in json format for extia
 ** requires the data to be in session variable $_SESSION['JSON_USERS'] from index.php
 ** the variable is
 **
* @package easyview report
* @copyright 2014 UC Regents
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
*/
require 'config_path.php';

if ( ! array_key_exists('JSON_GROUPS', $_SESSION) ) {
        exit;
}

if ( ! array_key_exists('COURSE_ID', $_SESSION) ) {
        exit;
}
require_login();
$context = context_course::instance($_SESSION['COURSE_ID']);
if (! has_capability('gradereport/grader:view', $context, $USER->id)) {
        print_error('Insufficient privilege');
}


################################
### BEGIN OUTPUT TO BROWSER
###
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Fri, 01 Jul 2011 00:00:00 GMT"); // Date in the past
header("Content-type: application/JSON; charset=utf-8");

$data = $_SESSION['JSON_GROUPS'];
// clenaup session
$_SESSION['JSON_GROUPS'] = null;
unset($_SESSION['JSON_GROUPS']);
$status = empty($data) ? 'false' : 'true';
?>{"status": <?php echo $status; ?>, "groups": [<?php echo $data; ?>]}
