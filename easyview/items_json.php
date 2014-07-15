<?php
/**
 ** create waiting list queue data feed in json format for extia
 ** requires the data to be in session variable $_SESSION['JSON_QUEUE'] from index.php
 ** the variable is 
 **
* @package easyview report
* @copyright 2014 UC Regents
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
*/
require 'config_path.php';
require 'functions.php';
require_once $CFG->dirroot.'/lib/gradelib.php';
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
$COURSEIDPASSEDIN = $_SESSION['COURSE_ID'];

require_login();
$context = context_course::instance($COURSEIDPASSEDIN);
if (! has_capability('gradereport/grader:view', $context, $USER->id)) {
        print_error('Insufficient privilege');
}


/////////////////////////////////////////////////////////////////////////
////code to create all the entries for each student in the gradebook/////
////////////////////////////////////////////////////////////////////////

//get all students and grade items for course
$students_and_all_groups        = get_students($COURSEIDPASSEDIN,$DB);//included in functions.php
$students       =       $students_and_all_groups[0];
$all_groups     =       $students_and_all_groups[1];//will be parsed below to create groups dropdown
$grade_items_and_categories     = get_grade_items($COURSEIDPASSEDIN,$DB);//included in functions.php
$grade_items = $grade_items_and_categories[0];
$all_categories = $grade_items_and_categories[1];//will be parsed below to create categories dropdown
//array_final will be filled, flattened, and set to json
$array_final    = array();

// first build table of grade scores...$uids=array();
foreach ($students as $i => $any){ $uids[]=$students[$i]['id']; }
$ggtable=array();
for($j=0; $j<count($grade_items); $j++){
    $gitem=$grade_items[$j]['id'];
    $gi=grade_item::fetch(array('id'=>$gitem));
    $ggtable[$gitem]= grade_grade::fetch_users_grades($gi,$uids,true);
}


//creates final json, loops through all students and each grade item to fill in matrix
for ($i = 0; $i < count($students); $i++){
        $row['first']           = addslashes($students[$i]['first']);
        $row['last']            = addslashes($students[$i]['last']);
        $row['name']            = $students[$i]['last'].", ".$students[$i]['first'];//this is what is actually displayed to the user
        $row['perm']            = $students[$i]['perm'];
        $row['userid']          = $students[$i]['id'];
        $row['email']           = addslashes($students[$i]['email']);
        $row['courseid']        = $COURSEIDPASSEDIN;
        if($students[$i]['group']==NULL){
                $row['group']='no group';
        }else{
                $row['group']=$students[$i]['group'];
        }
        for($j=0; $j<count($grade_items); $j++){
                //// grade item score  and grade item feedback stored according to id
                $gitem=$grade_items[$j]['id'];
                $userid = $students[$i]['id'];
                $score = number_format((float)($ggtable[$gitem][$userid]->finalgrade),2,'.','');
                $row[$grade_items[$j]['gid']] = $score;
                $row[$gitem.'feedback'] = $ggtable[$gitem][$userid]->feedback;

        }
        array_push($array_final, json_encode($row));
        ///////////////looks like this///////////////////
        /////////////////////////////////////////////////
        //Name  // 61  // 61feedback // 62 // 62feedback//
        //Alex  // 100 // "nice!"    // 80 // "good job"//
        //Steve // 90  //  ""        // 90 // "wooh!"   //
        /////////////////////////////////////////////////
        //61 and 62 are the grade item ids, note how the feedback works
}

$final = implode(',',$array_final);
$_SESSION['GRADEBOOK_DATALOAD']=time();
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////


################################
### BEGIN OUTPUT TO BROWSER
###
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Fri, 01 Jul 2011 00:00:00 GMT"); // Date in the past
header("Content-type: application/JSON; charset=utf-8");

// we might not be using total 7.15.2014 SRM
$total = count($array_final);
$status2 = empty($final) ? 'false' : 'true';
?>{"status": <?php echo $status2; ?>, "total":<?php echo $total ?>,"items": [<?php echo $final; ?>]}



