<?php

///////////////////////////////////////////////////////////////////////////
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once '../../../config.php';
require_once $CFG->dirroot.'/lib/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';

$courseid = required_param('id', PARAM_INT);
$groupid  = optional_param('group', null, PARAM_INT);

// Making this work with profile reports
$userid   = optional_param('userid', null, PARAM_INT);

$default_type = $userid ? 'user' : 'select';

$itemid   = optional_param('itemid', $userid, PARAM_INT);
$itemtype = optional_param('item', $default_type, PARAM_TEXT);

$course_params = array('id' => $courseid);

$PAGE->set_url(new moodle_url('/grade/report/easyview/index.php', $course_params));
$PAGE->set_pagelayout('standard');

if (!$course = $DB->get_record('course', $course_params)) {
    print_error('nocourseid');
}

// if (!in_array($itemtype, grade_report_easyview::valid_screens())) {
//     print_error('notvalid', 'gradereport_easyview', '', $itemtype);
// }

require_login($course);

$context = get_context_instance(CONTEXT_COURSE, $course->id);

// This is the normal requirements
// - for this, do we want access.php in db
 require_capability('gradereport/easyview:view', $context);
//require_capability('moodle/grade:viewall', $context);
// require_capability('moodle/grade:edit', $context);
// End permission

$gpr = new grade_plugin_return(array(
    'type' => 'report',
    'plugin' => 'easyview',
    'courseid' => $courseid
));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
// $USER->grade_last_report[$course->id] = 'easyview';

// grade_regrade_final_grades($courseid);

// $report = new grade_report_easyview( $courseid, $gpr, $context, $itemtype, $itemid, $groupid);

$reportname = 'easygrade';

$pluginname = get_string('pluginname', 'gradereport_easyview');

$report_url = new moodle_url('/grade/report/grader/index.php', $course_params);
$edit_url = new moodle_url('/grade/report/easyview/index.php', $course_params);
$view_url = new moodle_url('/grade/report/easyview/easyview/index.php', $course_params);


    redirect($view_url);

$PAGE->navbar->ignore_active(true);

$PAGE->navbar->add(get_string('courses'));
$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', $course_params));

$PAGE->navbar->add(get_string('gradeadministration', 'grades'));
$PAGE->navbar->add(get_string('pluginname', 'gradereport_grader'), $report_url);

//    redirect($view_url);
// include $CFG->dirroot.'/local/easyview/index.php';
return;

if ($reportname != $pluginname) {
    trigger_error('QE: $reportname != $pluginname');
    $PAGE->navbar->add($pluginname, $edit_url);
    $PAGE->navbar->add($reportname);
} else {
    trigger_error('QE: ' . $reportname . ' == ' . $pluginname);
    $PAGE->navbar->add($pluginname);
}

//    redirect($view_url);
if ($data = data_submitted()) {
    trigger_error('QE: $data = data_submitted()');
    $warnings = $report->process_data($data);

    if (empty($warnings)) {
// TFW UCSB 14/02 - return to quick edit after update, not grader report...
        redirect($edit_url);
//        redirect($report_url);
    }
}

print_grade_page_head($course->id, 'report', 'easyview', $reportname);
// require_once $CFG->dirroot.'/local/easyview/index.php';

if ($report->screen->supports_paging()) {
    trigger_error('QE: $report->screen->supports_paging()');
    echo $report->screen->pager();
}

if (!empty($warnings)) {
    foreach ($warnings as $warning) {
        echo $OUTPUT->notification($warning);
    }
}

echo $report->output();

echo $OUTPUT->footer();

