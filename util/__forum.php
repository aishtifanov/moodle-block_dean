<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');

    $courseid = optional_param('id', 3500, PARAM_INT); // course id

    $str = 'update_groupid_in_discussion';
    
    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> $str";
    print_header("$SITE->shortname: $str", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

    $courseid2 = $courseid + 1000;
    
    $strsql = "SELECT distinct course FROM mdl_forum_discussions
               where groupid=-1 and course>$courseid and course<$courseid2";
               
    if ($courses = get_records_sql($strsql))    {
        foreach ($courses as $course)   {
            update_groupid_in_discussion($course->course);
            notify("Complete $course->course", 'green');
        }
    }             	
 
    echo 'Complete all.';
    
	print_footer();
	
	
	
function update_groupid_in_discussion($courseid) 
{
	global $db;
    
     
    $strsql = "SELECT id, userid, groupid FROM mdl_forum_discussions
                where course=$courseid and groupid=-1";
    
    if ($discussions = get_records_sql($strsql))    {


        $strsql = "SELECT userid, groupid FROM mdl_groups_members
                    where groupid in (SELECT id FROM mdl_groups where courseid=$courseid)"; 

/*
        $strsql = "SELECT userid, groupid FROM mdl_groups_members
                    where userid in (SELECT distinct userid FROM mdl_forum_discussions where  course=$courseid and groupid=-1)";
*/        
        $amembers = array();
        if ($members = get_records_sql($strsql))    {
            foreach ($members as $member)   {
                $amembers[$member->userid] = $member->groupid; 
            }
        }            

        foreach ($discussions as $discussion)   {
            if (isset($amembers[$discussion->userid]))  {
                set_field('forum_discussions', 'groupid', $amembers[$discussion->userid], 'id', $discussion->id);
            } else {
                notify ('Not found group member ' . $discussion->userid);
            }      
        }
    } 
    
	return true;
}	 

?>


