<?PHP // $Id: normalizegroups.php,v 1.2 2009/09/11 10:34:13 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');


    $mode = required_param('mode', PARAM_ALPHA);    // add, del
	$courseid = required_param('courseid', PARAM_INT);		// Curriculum id
//	$gid = required_param('gid', PARAM_INT);		// Academygroup ID
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

	if (!$mgroup = get_record('groups', 'id',  $mgid))	{
        error("Moodle group not found!");
	}
	if (!$academygroup = get_record('dean_academygroups', 'name', $mgroup->name)) {
        error("Academy group not found!");
 	}

	if (!$course =  get_record("course", "id", $courseid))	{
       error("Course not found!");
	}

    if ($mode == 'norma')		{

	    $academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id);
	    if ($academystudents)  {

		    foreach ($academystudents as $d)	{
		    	$agroup_arr[] =  $d->userid;
		    }

	    	$moodlestudents = get_records_sql("SELECT id, userid FROM {$CFG->prefix}groups_members WHERE groupid = $mgid");
	    	if ($moodlestudents)	{

				foreach ($moodlestudents as $m)	{
			    	$mgroup_arr[] =  $m->userid;
			    }

                $mgroup_diff = array_diff ($mgroup_arr, $agroup_arr);

                print_r($mgroup_diff);
                // exit();

	        	foreach ($mgroup_diff as $userid) {
 		       		if (!unenrol_student_dean($userid, $courseid)) {
   	 	    			error("Could not remove student with id $userid from this course $courseid!");
    	    		}
					// delete_records('groups_members', 'userid', $userid, "groupid", $mgid);
      		  	}
      		}
      	}
	}

    print_footer();

?>