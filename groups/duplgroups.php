<?PHP // $Id: duplgroups.php,v 1.2 2009/10/19 12:23:03 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $namegroup = optional_param('namegroup', '');		// Group name (number)

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('duplgroups', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$site->shortname: $strsearchgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$table->head  = array (get_string('lastname'), get_string('username'),
						   'Num of groups members',  'Num of user students');
	$table->align = array ("left", "left", "center", "center");


    if ($delusers = get_records('user', 'deleted', 1, 'id'))	{
    	foreach ($delusers as $du)	{
            if ($dmemgr = get_records('groups_members', 'userid', $du->id, 'id'))	{
            	$num_grmem = count($dmemgr);
		        delete_records('groups_members', 'userid', $du->id);
            } else {
            	$num_grmem = 0;
            }

            if ($dstud = get_records('user_students', 'userid', $du->id, 'id'))	{
            	$num_stud = count($dstud);
				delete_records('user_students', 'userid', $du->id);
            } else {
            	$num_stud = 0;
            }
            if ($num_grmem > 0 || $num_stud>0)	{
	   			$table->data[] = array (fullname($du), $du->username, $num_grmem, $num_stud);
	   		}
    	}
    }
	print_table($table);
    unset($table);

//	exit();

	$strsql = "SELECT id, courseid, name, COUNT(*) AS dups
			   FROM {$CFG->prefix}groups
			   GROUP BY courseid, name
			   HAVING COUNT(*)>1
			   ORDER BY courseid";

    if ($dupgroups = get_records_sql($strsql))	{
    	// print_r($dupgroups);
    	foreach ($dupgroups as $dupgroup)  {
    	    // print_r($dupgroup); echo '<br>';
	        if ($zgroups = get_records_sql("SELECT id, courseid, name FROM {$CFG->prefix}groups
	        							WHERE courseid = {$dupgroup->courseid} AND name = '{$dupgroup->name}'"))	{
		        foreach ($zgroups as $zgroup)	{
		        	// print_r($zgroup); echo '<br>';
					if ($memgr = get_records('groups_members', 'groupid', $zgroup->id))	{
						$numstudents = count($memgr);
					} else {
						$numstudents = 0;
					}
					// echo 'N = ' . $numstudents;
					// echo '<br>';
					if ($numstudents == 0)	{
		                $ret = delete_records('groups', 'id', $zgroup->id);
		                // print_r($ret); echo '<br>';
					}
		        }
		    }
			// echo '<hr>';
    	}
    }

    // exit();

	$strsql = "SELECT id, courseid, name, COUNT(*) AS dups
			   FROM {$CFG->prefix}groups
			   GROUP BY courseid, name
			   HAVING COUNT(*)>1
			   ORDER BY courseid";

    if ($duplgroups = get_records_sql($strsql))	 {
    	// print_r($dupgroups);

		$table->head  = array (get_string('discipline','block_dean'), get_string('group'),
								get_string('numofgoups', 'block_dean'),  get_string("action","block_dean"));
		$table->align = array ("left", "left", "center", "center");

        $numout = 0;
    	foreach ($duplgroups as $duplgroup)  {

    		$strlinkupdate = '-';

			$acourse = get_record("course", "id", $duplgroup->courseid);
	   	    $linkcss = $acourse->visible ? "" : ' class="dimmed" ';


			$table->data[] = array ("<strong><a $linkcss href=\"{$CFG->wwwroot}/course/view.php?id={$acourse->id}\">$acourse->fullname</a></strong>",
									"<strong>$duplgroup->name</strong>",
									$duplgroup->dups,
									$strlinkupdate);

           	if ($academygroup = get_record('dean_academygroups', 'name', $duplgroup->name))   {

				if ($academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id)) 	{

						$strsql = "SELECT id, courseid, name  FROM {$CFG->prefix}groups
								 WHERE courseid = {$duplgroup->courseid} AND name = '$duplgroup->name'";

					    if ($mgroups = get_records_sql($strsql))	 {
			                foreach ($mgroups as $mgroup)	{
								delete_records('groups', 'id', $mgroup->id);
			 	                delete_records("groups_members", "groupid", $mgroup->id);
			                }
						}

			   	        $newgroup->name = $academygroup->name;
			       	    $newgroup->courseid = $duplgroup->courseid;
						$newgroup->description = '';
						$newgroup->password = '';
						$newgroup->theme = '';
			           	$newgroup->lang = current_language();
			            $newgroup->timecreated = time();
			            if (!$newgrpid=insert_record("groups", $newgroup)) {
			      	           notify("Could not insert the new group '$newgroup->name'");
			          	}


				   	  foreach ($academystudents as $astud)	  {
	                	 if (!$newmemberwas = get_record('groups_members', 'groupid', $newgrpid, 'userid', $astud->userid))	 {
						     $newmember->groupid = $newgrpid;
		    	             $newmember->userid = $astud->userid;
		        	         $newmember->timeadded = time();
		            	     if (!insert_record('groups_members', $newmember)) {
		                	    notify("Error occurred while adding user $astud->userid to group $academygroup->name");
			                 }
			             }
		              }
				}
			} else {
		        notify("Academygroup {$duplgroup->name} not found!");
		    }
            // print_r($academystudents);

	    }
		print_table($table);
    }

   print_footer();

?>