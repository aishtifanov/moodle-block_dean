<?PHP // $Id: zerogroups_.php,v 1.2 2009/09/11 10:34:14 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');


    $mode = required_param('mode', PARAM_ALPHA);    // add, del
	$courseid = required_param('courseid', PARAM_INT);		// Curriculum id
	$gid = required_param('gid', PARAM_INT);		// Academygroup ID
	$mgid = required_param('mgid', PARAM_INT);		// Moodlegroup ID


	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('zerogroup', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$site->shortname: $strsearchgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
    if (!$admin_is && !$creator_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Group not found!");
 	}

	$course =  get_record("course", "id", $courseid);

    if ($mode == 'add')		{

        $numgroups = count_records('groups', 'name', $academygroup->name, 'courseid', $courseid);
        if ($numgroups >= 2)	{
            error("В курсе '{$course->fullname}' уже организованы $numgroups группы {$academygroup->name}!!!");
        }

	    $academystudents = get_records('dean_academygroups_members', 'academygroupid', $gid);

	    if ($academystudents)  {
	   	   foreach ($academystudents as $astud)	  {
	              /// Enrol student
		     if ($usr = get_record('user', 'id', $astud->userid))	{
			    // print '-'.$usr->id.':'.$usr->lastname.' '. $usr->firstname. '<br>';
			    if ($usr->deleted != 1)	 {
						if (!enrol_student_dean($astud->userid, $courseid))  {
	 		                   error("Could not add student with id $astud->userid to the course $addcourse!");
	   		            }
	   		    } else {
	   		       	delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
	   		    }
		     }
           	 if (!$newmemberwas = get_record('groups_members', 'groupid', $mgid, 'userid', $astud->userid))	 {
			     $newmember->groupid = $mgid;
  	             $newmember->userid = $astud->userid;
      	         $newmember->timeadded = time();
          	     if (!insert_record('groups_members', $newmember)) {
              	    notify("Error occurred while adding user $astud->userid to group $academygroup->name");
                 }
             }
	       }
           notify("Группа {$academygroup->name} подписана на дисциплину {$course->fullname}.", 'green', 'left');
		}
	} else if ($mode == 'del')	{
		    // if ($delgrp = get_record("groups", "name", $academygroup->name, "courseid", $courseid))	{
			if ($delgrp = get_record("groups", "id", $mgid))	{
                delete_records("groups", "id", $delgrp->id);
                delete_records("groups_members", "groupid", $delgrp->id);
           		notify("Группа {$academygroup->name} удалена из дисциплины {$course->fullname}.", 'green', 'left');
            }
	}


    print_footer();

?>