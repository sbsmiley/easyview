-- requirements for stored procedure, run this as mysql root
-- change ootb253 to your database 
-- change ootb253 to your moodle user
-- change localhost to your moodle db host 
-- change mdl_  to your moodle prefix  
use ootb253;
drop procedure gb12narrow;
delimiter //
CREATE DEFINER=`root`@`localhost` PROCEDURE `gb12narrow`( IN gidinput int )
BEGIN
set @gid = gidinput;
set @max = (select mdl_grade_items.grademax from mdl_grade_items where mdl_grade_items.id = @gid);
set @cid = (select mdl_grade_items.courseid from mdl_grade_items where mdl_grade_items.id = @gid);

set @scoremax =  (
SELECT
    max(mdl_grade_grades.finalgrade) 
 FROM
    mdl_grade_grades
    where mdl_grade_grades.itemid = @gid   
    AND  mdl_grade_grades.userid in 
( 

SELECT
    mdl_user.id
FROM
    mdl_role_assignments
INNER JOIN
    mdl_user
ON
    (
        mdl_role_assignments.userid = mdl_user.id)
INNER JOIN
    mdl_context
ON
    (
        mdl_role_assignments.contextid = mdl_context.id)
INNER JOIN
    mdl_course
ON
    (
        mdl_context.instanceid = mdl_course.id)
WHERE
    mdl_course.id = @cid
AND mdl_context.contextlevel = 50
AND mdl_role_assignments.roleid = 5

) LIMIT 1);

set @scoremin =  (
SELECT
    min(mdl_grade_grades.finalgrade) 
 FROM
    mdl_grade_grades
    where mdl_grade_grades.itemid = @gid   
    AND  mdl_grade_grades.userid in 
( 
SELECT
    mdl_user.id
FROM
    mdl_role_assignments
INNER JOIN
    mdl_user
ON
    (
        mdl_role_assignments.userid = mdl_user.id)
INNER JOIN
    mdl_context
ON
    (
        mdl_role_assignments.contextid = mdl_context.id)
INNER JOIN
    mdl_course
ON
    (
        mdl_context.instanceid = mdl_course.id)
WHERE
    mdl_course.id = @cid
AND mdl_context.contextlevel = 50
AND mdl_role_assignments.roleid = 5


) LIMIT 1);

-- use the max grade item, rather than the max student score
set @scoremax = @max;
set @scorebucket = (@scoremax - @scoremin) / 10;

set @score100 = cast(@scoremin+(10*@scorebucket) as decimal(10,2)); 
set @score90 = cast(@scoremin+(9*@scorebucket) as decimal(10,2)); 
set @score80 = cast(@scoremin+(8*@scorebucket) as decimal(10,2)); 
set @score70 = cast(@scoremin+(7*@scorebucket) as decimal(10,2)); 
set @score60 = cast(@scoremin+(6*@scorebucket) as decimal(10,2)); 
set @score50 = cast(@scoremin+(5*@scorebucket) as decimal(10,2)); 
set @score40 = cast(@scoremin+(4*@scorebucket) as decimal(10,2)); 
set @score30 = cast(@scoremin+(3*@scorebucket) as decimal(10,2)); 
set @score20 = cast(@scoremin+(2*@scorebucket) as decimal(10,2)); 
set @score10 = cast(@scoremin+(1*@scorebucket) as decimal(10,2)); 
set @score0 = floor(@scoremin);

set @label100 =  concat(' > ',@score100);


set @label90 = concat(@score90,' to ' );
set @label90 = concat(@label90,@score100 );

set @label80 = concat(@score80,' to ' );
set @label80 = concat(@label80,@score90 );

set @label70 = concat(@score70,' to ' );
set @label70 = concat(@label70,@score80 );

set @label60 = concat(@score60,' to ' );
set @label60 = concat(@label60,@score70 );

set @label50 = concat(@score50,' to ' );
set @label50 = concat(@label50,@score60 );

set @label40 = concat(@score40,' to ' );
set @label40 = concat(@label40,@score50 );

set @label30 = concat(@score30,' to ' );
set @label30 = concat(@label30,@score40 );

set @label20 = concat(@score20,' to ' );
set @label20 = concat(@label20,@score30 );

set @label10 = concat(@score10,' to ' );
set @label10 = concat(@label10,@score20 );

set @label0 = concat(@score0,' to ' );
set @label0 = concat(@label0,@score10 );



SELECT
    case when mdl_grade_grades.finalgrade > (@scoremax)  then ">100% "
         when  mdl_grade_grades.finalgrade  > @score90 and mdl_grade_grades.finalgrade <= @score100 then "90 to 100% "
         when  mdl_grade_grades.finalgrade  > @score80 and mdl_grade_grades.finalgrade <= @score90 then "80 to 90% "
         when  mdl_grade_grades.finalgrade  > @score70 and mdl_grade_grades.finalgrade <= @score80 then "70 to 80% "
         when  mdl_grade_grades.finalgrade  > @score60 and mdl_grade_grades.finalgrade <= @score70 then "60 to 70% "
         when  mdl_grade_grades.finalgrade  > @score50 and mdl_grade_grades.finalgrade <= @score60 then "50 to 60% "
         when  mdl_grade_grades.finalgrade  > @score40 and mdl_grade_grades.finalgrade <= @score50 then "40 to 50% "
         when  mdl_grade_grades.finalgrade  > @score30 and mdl_grade_grades.finalgrade <= @score40 then "30 to 40% "
         when  mdl_grade_grades.finalgrade  > @score20 and mdl_grade_grades.finalgrade <= @score30 then "20 to 30% "
         when  mdl_grade_grades.finalgrade  > @score10 and mdl_grade_grades.finalgrade <= @score20 then "10 to 20% "
         when  mdl_grade_grades.finalgrade  >= @score0 and mdl_grade_grades.finalgrade <= @score10 then "0 to 10% "
    else " unknown value" end hitrange ,
    case when mdl_grade_grades.finalgrade > (@scoremax)  then @label100
         when  mdl_grade_grades.finalgrade  > @score90 and mdl_grade_grades.finalgrade <= @score100 then @label90
         when  mdl_grade_grades.finalgrade  > @score80 and mdl_grade_grades.finalgrade <= @score90 then @label80
         when  mdl_grade_grades.finalgrade  > @score70 and mdl_grade_grades.finalgrade <= @score80 then @label70
         when  mdl_grade_grades.finalgrade  > @score60 and mdl_grade_grades.finalgrade <= @score70 then @label60
         when  mdl_grade_grades.finalgrade  > @score50 and mdl_grade_grades.finalgrade <= @score60 then @label50
         when  mdl_grade_grades.finalgrade  > @score40 and mdl_grade_grades.finalgrade <= @score50 then @label40
         when  mdl_grade_grades.finalgrade  > @score30 and mdl_grade_grades.finalgrade <= @score40 then @label30
         when  mdl_grade_grades.finalgrade  > @score20 and mdl_grade_grades.finalgrade <= @score30 then @label20
         when  mdl_grade_grades.finalgrade  > @score10 and mdl_grade_grades.finalgrade <= @score20 then @label10
         when  mdl_grade_grades.finalgrade  >= @score0 and mdl_grade_grades.finalgrade <= @score10 then @label0
    else " unknown value" end hitrangevalue ,
    count(*)  as BucketSize,
    mdl_grade_grades.itemid
 FROM
    mdl_grade_grades
    where mdl_grade_grades.itemid = @gid    AND mdl_grade_grades.finalgrade >= 0
    AND  mdl_grade_grades.userid in 
(
SELECT
    mdl_user.id
FROM
    mdl_role_assignments
INNER JOIN
    mdl_user
ON
    (
        mdl_role_assignments.userid = mdl_user.id)
INNER JOIN
    mdl_context
ON
    (
        mdl_role_assignments.contextid = mdl_context.id)
INNER JOIN
    mdl_course
ON
    (
        mdl_context.instanceid = mdl_course.id)
WHERE
    mdl_course.id = @cid
AND mdl_context.contextlevel = 50
AND mdl_role_assignments.roleid = 5
)
    group by  hitrange
    order by hitrange asc;
END
//
delimiter ;
grant execute on procedure  ootb253.gb12narrow to 'ootb253'@'localhost';
flush privileges;
