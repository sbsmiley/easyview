<?php
/*
 ** queries DB for list of other users who have used gradebook modules since the given timestamp
* @package easyview report
* @copyright 2014 UC Regents
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
*/
require 'config_path.php';
require_login();
$context = context_course::instance($_SESSION['COURSE_ID']);
if (! has_capability('gradereport/grader:view', $context, $USER->id)) {
        print_error('Insufficient privilege');
}

$sql = "
SELECT DISTINCT
    mdl_log.module,
    mdl_log.action,
    mdl_user.firstname,
    mdl_user.lastname
FROM
    mdl_log
INNER JOIN
    mdl_user
ON
    (
        mdl_log.userid = mdl_user.id) 
WHERE
    mdl_log.course = ".$_SESSION['COURSE_ID']."
AND mdl_log.module IN ('easyview', 'csvimport', 'quickedit', 'grader', 'grade') 
AND mdl_log.time >= unix_timestamp(now()) - 300 
AND mdl_log.userid != ".$USER->id;

$others = $DB->get_records_sql($sql);
$others_array = array();
foreach($others as $other){
	$row['module'] = $other->module;
	$row['action'] = $other->action;
	$row['firstname'] = $other->firstname;
	$row['lastname'] = $other->lastname;
	array_push($others_array,json_encode($row));
}
$final = implode(',',$others_array);

################################
### BEGIN OUTPUT TO BROWSER
###
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Fri, 01 Jul 2011 00:00:00 GMT"); // Date in the past
header("Content-type: application/JSON; charset=utf-8");

$status = empty($final) ? 'false' : 'true';
?>{"status": <?php echo $status; ?>, "others": [<?php echo $final; ?>]}
