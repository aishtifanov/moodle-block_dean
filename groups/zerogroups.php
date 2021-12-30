<?PHP // $Id: zerogroups.php,v 1.3 2009/10/19 12:23:04 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $namegroup = optional_param('namegroup', '');		// Group name (number)

    $fid = optional_param('fid', 0, PARAM_INT);		// Number of facultet

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
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


 // ÏÎÈÑÊ È ÈÇÌÅÍÅÍÈÅ ÑÂÅĞÁÎËÜØÈÕ ÃĞÓÏÏ
  $facultymenu = array();
  $facultymenu[0] = get_string('selectafaculty', 'block_dean').'...';

  for ($i = 1; $i<=20; $i++)   {
     if ($i <= 9)	{
		$facultymenu[$i] = '0'.$i;
 	 } else {
		$facultymenu[$i] = $i;
 	 }
  }

  echo '<div align=center>'.get_string('ffaculty', 'block_dean').': ';
  popup_form('zerogroups.php?fid=', $facultymenu, 'switchfac', $fid, '', '', '', false);
  echo '</div>';

  if ($fid > 0)		{

    if ($fid <= 9) {
	  	$fid_shablon = '0'. $fid .'____';
	} else {
	  	$fid_shablon = $fid .'____';
	}

    $groups = get_records_sql("SELECT id, courseid, name FROM {$CFG->prefix}groups
							    WHERE name LIKE '$fid_shablon'
    							ORDER BY name");

	$table->head  = array (get_string('group'), get_string('discipline','block_dean'),
							get_string('numofstudents', 'block_dean'),  get_string("action","block_dean"));
	$table->align = array ("left", "left", "center", "center");

    if ($groups) {

        $stopsignal = 1;
        $bigroups = array();
        foreach ($groups as $group) {
        	/*
            $countusers = 0;
            $listmembers[$group->id] = array();
            if ($groupusers = get_group_users($group->id)) {
                foreach ($groupusers as $groupuser) {
                    $listmembers[$group->id][$groupuser->id] = $nonmembers[$groupuser->id];
                    unset($nonmembers[$groupuser->id]);
                    $countusers++;
                }
                // natcasesort($listmembers[$group->id]);
            }
            */
            $countusers = -1;
            // if ($groupusers = get_group_users($group->id)) {
            if ($groupusers = get_records_sql("SELECT id, userid FROM {$CFG->prefix}groups_members WHERE groupid = {$group->id}")) {
            	$countusers = count($groupusers);
            }
            // $listgroups[$group->id] = $group->name." ($countusers)";
            if ($countusers > 60)	{

				$title = get_string('updategroup','block_dean');
			    $strlinkupdate = "<a title=\"$title\" TARGET=\"_blank\" href=\"normalizegroups.php?mode=norma&amp;courseid={$group->courseid}&amp;mgid={$group->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";


	            $table->data[] = array ($group->name,
	             "<strong><a href=\"{$CFG->wwwroot}/course/view.php?id={$group->courseid}\">{$group->courseid}</a></strong>",
	             $countusers, $strlinkupdate);

				$bigroups[] = $group;
                if ($stopsignal++ > 5) break;
	        }

        }

 	   print_heading('Êîëè÷åñòâî ãğóïï '.count($groups), 'center', 3);
 	   print_heading('Êîëè÷åñòâî áîëüøèõ ãğóïï '.count($bigroups), 'center', 3);
	   print_table($table);

       foreach ($bigroups as $mgroup)  {

			if (!$academygroup = get_record('dean_academygroups', 'name', $mgroup->name)) {
  		      notify("Academy group {$mgroup->name} not found!");
  		      continue;
		 	}

			$mgid = $mgroup->id;
			$courseid = $mgroup->courseid;

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

	                // print_r($mgroup_diff);
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
    }


  }
  print_footer();


// ÏÎÈÑÊ È ÓÍÈ×ÒÎÆÅÍÈÅ ÍÓËÅÂÛÕ ÃĞÓÏÏ
/*
    exit();

    $agroups = get_records_sql("SELECT * FROM {$CFG->prefix}dean_academygroups
							    WHERE name LIKE '______'
    							ORDER BY name");
    if ($agroups)	{

		$table->head  = array (get_string('group'), get_string('discipline','block_dean'),
								get_string('numofstudents', 'block_dean'),  get_string("action","block_dean"));
		$table->align = array ("left", "left", "center", "center");

        $numout = 0;
        foreach ($agroups as $agroup)		{
	        if ($agroupmembers = get_records('dean_academygroups_members',  'academygroupid', $agroup->id))		{
		        $countagroupmembers = count($agroupmembers);
	            // echo "$agroup->name ($countagroupmembers): ";

		        if ($mgroups = get_records('groups', 'name',  $agroup->name))	{
		            $countmgroups = count($mgroups);
	                // echo "[$countmgroups]: ";
		        	foreach ($mgroups as $mgroup)	{

						if ($memgr = get_records('groups_members', 'groupid', $mgroup->id, 'userid'))	{
							$numstudents = count($memgr);
						} else {
							$numstudents = 0;
						}
 						// echo ' = ' . $numstudents;
						if ($numstudents == 0)	{
                            $numout++;
							if 	($admin_is)	 {
								$title = get_string('enrolgroup','block_dean');
								$strlinkupdate = "<a title=\"$title\" TARGET=\"_blank\"  href=\"zerogroups_.php?mode=add&amp;courseid={$mgroup->courseid}&amp;gid={$agroup->id}&amp;mgid={$mgroup->id}\">";
								$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/clear.gif\" alt=\"$title\" /></a>&nbsp;";
								$title = get_string('deletegroup','block_dean');
							    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" TARGET=\"_blank\" href=\"zerogroups_.php?mode=del&amp;courseid={$mgroup->courseid}&amp;gid={$agroup->id}&amp;mgid={$mgroup->id}\">";
								$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
							}
							else	{
								$strlinkupdate = '-';
							}

							$acourse = get_record("course", "id", $mgroup->courseid);
				    	    $linkcss = $acourse->visible ? "" : ' class="dimmed" ';

							$table->data[] = array ("<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>",
													"<strong><a $linkcss href=\"{$CFG->wwwroot}/course/view.php?id={$acourse->id}\">$acourse->fullname</a></strong>",
													"$numstudents ($countagroupmembers)",
													$strlinkupdate);

			                delete_records("groups", "id", $mgroup->id);

                			delete_records("groups_members", "groupid", $mgroup->id);
						}
		        	}
		        }
		        if ($numout > 50) break;

		        // echo '<hr>';
		    } else {
			    $countagroupmembers = 0;
		    }
	    }
		print_table($table);
    }

   $strsql = "SELECT {$CFG->prefix}groups.id, {$CFG->prefix}groups.courseid, {$CFG->prefix}groups.name
   			  FROM {$CFG->prefix}groups LEFT JOIN {$CFG->prefix}groups_members ON {$CFG->prefix}groups.id = {$CFG->prefix}groups_members.groupid
			  WHERE ((({$CFG->prefix}groups_members.groupid) Is Null))";
   if ($mgroups = get_records_sql($strsql))	{
   		foreach ($mgroups as $mgroup)	{
			if (strlen($mgroup->name) == 6 && is_numeric($mgroup->name))	{
	            delete_records("groups", "id", $mgroup->id);
	        }
		}
   }

   print_footer();
 */
?>