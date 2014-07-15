<?php
/**
* Histogram primary file to load
*
* @package easyview report
* @copyright 2014 UC Regents
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v2
*/


function summaryquery($id){
global $COURSEID;

        $sql = 'select 
                        mdl_grade_items.grademax,
                        std(mdl_grade_grades.finalgrade) as std,
                        count(mdl_grade_grades.finalgrade) as total,
                        max(mdl_grade_grades.finalgrade) as max,
                        min(mdl_grade_grades.finalgrade) as min,
                        avg(mdl_grade_grades.finalgrade) as avg
                from
                        mdl_grade_grades, 
                                                mdl_grade_items
                where
                        mdl_grade_items.id      = '.$id.' and 
                        mdl_grade_grades.itemid = '.$id.' AND  
                        mdl_grade_grades.finalgrade >= 0 AND
                        mdl_grade_grades.userid in (
                                SELECT mdl_user.id 
                                FROM mdl_role_assignments 
                                INNER JOIN mdl_user ON ( mdl_role_assignments.userid = mdl_user.id)
				INNER JOIN mdl_context ON ( mdl_role_assignments.contextid = mdl_context.id)
				INNER JOIN mdl_course ON ( mdl_context.instanceid = mdl_course.id) 
                                WHERE mdl_course.id = ' .  $COURSEID  . ' 
                                AND mdl_context.contextlevel = 50 
                                AND mdl_role_assignments.roleid = 5);';
        return $sql;
}

function namequery($id){
        $sql = 'select itemname, itemtype, iteminstance from mdl_grade_items where id=' . $id . ';';
        return $sql;
}

function medianquery($id,$count){
global $COURSEID;
        $sql = 'SELECT finalgrade 
                FROM mdl_grade_grades 
                WHERE itemid ='.$id.' 
                AND userid IN (
                        SELECT mdl_user.id 
                        FROM mdl_role_assignments 
                        INNER JOIN mdl_user ON ( mdl_role_assignments.userid = mdl_user.id) 
                        INNER JOIN mdl_context ON ( mdl_role_assignments.contextid = mdl_context.id) 
                        INNER JOIN mdl_course ON ( mdl_context.instanceid = mdl_course.id) 
                        WHERE mdl_course.id = ' .  $COURSEID  . ' 
                        AND mdl_context.contextlevel = 50 
                        AND mdl_role_assignments.roleid = 5) 
                AND finalgrade >= 0
                order by finalgrade asc limit '.floor($count/2).',1;';
        return $sql;
}


function categoryquery($id){
        $sql = '
SELECT
    mdl_grade_categories.fullname
FROM
    mdl_grade_items
INNER JOIN
    mdl_grade_categories
ON
    (mdl_grade_items.iteminstance = mdl_grade_categories.id)
WHERE
    mdl_grade_items.itemtype IN ("course", "category")
    and mdl_grade_items.id = '.$id.';';
        return $sql;
}

function parse($resources, $min, $max, $classsize){
global $COURSE,$DB;
        $array_final=array();
        //label_array and label_index are used to keep all buckets filled, query only returns those with values
        $label_array=array('unknown value','0 to 10%','10 to 20%','20 to 30%', '30 to 40%', '40 to 50%', '50 to 60%', '60 to 70%', '70 to 80%', '80 to 90%','90 to 100%', '> 100%');
        $label_index = 1;
        foreach ($resources as $obj){
                //this conditional removes the unknown value debug
                if($label_index>0){
                        $row['value']= (int)$obj->bucketsize;
                        $row['percentvalue'] = ((int)$obj->bucketsize)/(int)$classsize;
                        $row['flag']=0;
                        $row['label'] = $obj->hitrangevalue;
                        //adds empty histo values if needed, between or before student grades
                        //preg_replace fixes weird error with hidden non alpha-num characters
                        while(strcmp(preg_replace('/[^0-9A-Za-z]/i','',$obj->hitrange),preg_replace('/[^0-9A-Za-z]/i','',$label_array[$label_index]))!=0){
                                $new_row['value']=0;
                                $new_row['flag']=0;
                                $new_row['label']=(string)number_format(($min+ ((($max-$min)/10)*($label_index-1))),2,'.','')." to ".(string)number_format(($min+((($max-$min)/10)*$label_index)),2,'.','');//$label_array[$label_index];
                                array_push($array_final, json_encode($new_row));
                                $label_index++;
                        }
                        array_push($array_final, json_encode($row));
                }
                $label_index++;
        }
        //for missing buckets after largest student grade
        while($label_index<12){
                        $new_row['value']=0;
                        $new_row['flag']=0;
                        if ($label_index==11)
                                $new_row['label'] = "> ".(string)$max;
                        else
                                $new_row['label']=(string)($min+ ((($max-$min)/10)*($label_index-1)))." to ".(string)($min+((($max-$min)/10)*$label_index));
                        array_push($array_final, json_encode($new_row));
                        $label_index++;
        }

        $final = implode(',',$array_final);
        return $final;
}


?>
