<?PHP // $Id: 
	require_once('../../config.php');
    require_once('lib.php');
    

    $yid = 17;
    //$startyear = 2015;
	$strtitle = '__ Регистрация всех групп 2016/2017 учебного года в Пегасе с регистрацией в курсах';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> " . $strtitle;
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}
    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
    
    $currtime = time();
    $countacademygroup = 0;
        
    $strsql = "SELECT id, curriculumid FROM mdl_dean_discipline_temp group by curriculumid";
    if ($curriculums = get_records_sql ($strsql))		{
        print_heading( 'Количество рабочих учебных планов ' . count($curriculums), 'center', 4);
        
	   foreach ($curriculums as $curriculum)	{
	       
            $strsql = "SELECT ag.id, ag.name FROM mdl_dean_academygroups ag inner join mdl_bsu_ref_groups rg using(name)
                        where rg.yearid=$yid and ag.curriculumid=$curriculum->curriculumid";
            // print $strsql . '<br>'; 
            if ($academygroups = get_records_sql($strsql))		{
				print_heading( "Количество академических групп для РУП #$curriculum->curriculumid = " . count($academygroups), 'center', 5);
                $countacademygroup += count($academygroups);
                
                $strsql = "SELECT id, courseid FROM mdl_dean_discipline_temp 
                           where curriculumid=$curriculum->curriculumid";
                if ($courses  = get_records_sql($strsql))		{
                    print_heading( "Количество дисциплин для РУП #$curriculum->curriculumid = " . count($courses), 'center', 5);
                    
                    foreach ($academygroups as $academygroup)	{
                        foreach ($courses as $course)  {
                            
                            $courseid = $course->courseid;
                            
    		                $newgroup = new stdClass();
                            if ($mgroupa = get_record('groups', 'courseid', $courseid, 'name', $academygroup->name))	{
                            	$newgroup->id = $mgroupa->id;
                            } else {
    	                        $newgroup->name = $academygroup->name;
    			                $newgroup->courseid = $courseid;
    			                $newgroup->description = $academygroup->name;
    			                $newgroup->lang = 'ru_utf8';
    			                $newgroup->timecreated = $currtime;
    			                if ($newgroup->id = insert_record("groups", $newgroup)) {
    			                    notify("Группа '$newgroup->name' успешно добавлена в курс $courseid", 'green');
    			                }  else {
    			                    error("Could not insert the new group '$newgroup->name'");
    			                }
                                
    			            }
        
			                $strsql = "SELECT id, userid FROM {$CFG->prefix}dean_academygroups_members
			                		  WHERE  academygroupid = {$academygroup->id}";

							if ($academygroup_members = get_records_sql($strsql))	{

	                           foreach ($academygroup_members as $amem)	{

					           	 if (!$newmemberwas = get_record('groups_members', 'groupid', $newgroup->id, 'userid', $amem->userid))	 {
					           	   
			                       if (enrol_student_dean($amem->userid, $courseid)) {
			                              $record = new stdClass();
						  		          $record->groupid = $newgroup->id;
				 	      	              $record->userid = $amem->userid;
					   	    	          $record->timeadded = $currtime;
				 	      	    	      if (!insert_record('groups_members', $record)) {
			            	    	  		  error('!!> '. get_string('erroraddingusertogroup', 'block_dean', $academygroup->name));
					               	      }
			 	                   } else {
									     if (!$usr = get_record('user', 'id', $amem->userid))	{
							   		 	      	delete_records('dean_academygroups_members', 'userid', $amem->userid);
	   									 } else {
	   									 	if ($usr->deleted == 1)	 {
							   		 	      	delete_records('dean_academygroups_members', 'userid', $amem->userid);
							   		 	    } else {
				   	                      	    error('!> '.get_string('enrolledincoursenot'). ' ' . $amem->userid );
				   	                      	}
			   	                      	 }
			   	                   }
                                   
			   	                 }
						       }
						    }
        				}    
                    }
                }    
            } 
            // break;
         }
    }              
	
    notify ("$countacademygroup");
    notify ('Complete!!');
    print_footer();
    
?>