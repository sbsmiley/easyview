<?php
/*
 ** queries database for changes in the gradebook since the given timestamp, which is stored in $_SESSION['GRADEBOOK_DATALOAD']
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
SELECT
    mdl_grade_grades.id,
    mdl_user.firstname,
    mdl_user.lastname,
    mdl_grade_grades.finalgrade,
    mdl_grade_items.itemname
FROM
    mdl_grade_grades
INNER JOIN
    mdl_user
ON
    (
        mdl_grade_grades.userid = mdl_user.id)
INNER JOIN
    mdl_grade_items
ON
    (
        mdl_grade_grades.itemid = mdl_grade_items.id)
WHERE
    mdl_grade_items.courseid = ".$_SESSION['COURSE_ID']."
AND mdl_grade_grades.timemodified >= ".$_SESSION['GRADEBOOK_DATALOAD'];

//error_log("time on query: ".$_SESSION['GRADEBOOK_DATALOAD']);
$others = $DB->get_records_sql($sql);
//error_log($sql);
$others_array = array();
foreach($others as $other){
	$row['firstname'] = $other->firstname;
	$row['lastname'] = $other->lastname;
	$row['finalgrade'] = $other->finalgrade;
	$row['itemname'] = $other->itemname;
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
?>{"status": <?php echo $status; ?>, "dataload": [<?php echo $final; ?>]}
