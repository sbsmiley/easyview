<?php

/**
* Easyview primary file to load
*
* @package easyview report
* @copyright 2014 UC Regents
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
*/

require '/var/www/ssl/moodle/config.php'; // lots of library loaded, give $CFG and more useful variables, can uase all of moodle API from moodlelib.php dmllib.php, etc.""

// Enable the link to the quickedit module
$quickedit_param = 1;

// Enable the link for the provided histogram functionality - please read about the stored procedures 
$histogram_param = 1;

// enable averages on the top 
$averages_param = 1;

// display the full grade item name when clicking on pull down
$text_param = 0;

// Don't run background tasks to display who has been in grader tools or check if the grades have been updated
// this is a default setting for easyview, an instructor can easily enable it
$hide_gradebook_access = 1;


// Interval in seconds to check to see if data has changed in gradebook
// query done by grade.grade timemodify change since last check or start up 
$check_data = 15;

// How often to check mdl_log for gradebook access by peers
$check_access = 5;
?>

