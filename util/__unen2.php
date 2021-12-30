<?php // $Id: __unenroll.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    require_once('lib.php');

    $year = optional_param('year', '04'); // course id
    
    $strtitle = 'Move graduated group 03,04,05';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();


    $strsql = "SELECT id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
               FROM mdl_dean_academygroups where name like '__{$year}__'";
    if ($agroups = get_records_sql($strsql))   {
        
        $lgrnames = array();
        $strsql = "SELECT DISTINCT group1 FROM mdl_dean_ldap where group1 like '__{$year}__'";
        if ($lgroups = get_records_sql($strsql))    {
            foreach ($lgroups  as $lgroup)  {
                $lgrnames[] = $lgroup->group1;
            }
        }
        
        $agrnames = array();
        foreach ($agroups as $agroup)   {
            $agrnames[] = $agroup->name;
            if (in_array($agroup->name, $lgrnames))  {
                notify ($agroup->name, 'green');
            } else {
                if (move_graduate_group($agroup)) {
                    notify ($agroup->name. ' moving in graduated group.');
                } else {
                    notify ($agroup->name. ' NOT moving in graduated group !!!');
                    exit(0);
                }    
            }      
        }
        
        echo '$agrnames<br>';
        $result = array_diff($agrnames, $lgrnames);
        print_r($result);

        echo '<br>$lgrnames<br>';
        $result = array_diff($lgrnames, $agrnames);
        print_r($result);
    }

    notify ('Complete all.');
    
	print_footer();



function move_to_dean_user_students_g($academygroup, $academystudents)
{
    global $CFG;
    
	if ($mgroups = get_records_sql("SELECT courseid, name FROM {$CFG->prefix}groups WHERE name = '{$academygroup->name}'"))   {
    
	    foreach ($academystudents as $astud)	  {
	      
	         // if ( $std_courses = get_records('user_students', 'userid', $astud->userid)){
             // foreach ($std_courses as $s){
             foreach ($mgroups as $mg) {
                unset($studentss);
                
                $studentss->userid = $astud->userid;
				$studentss->course = $mg->courseid;
				// $studentss->timestart = $s->timestart;
				// $studentss->timeend = $s->timeend;
				$studentss->time = time();
				// $studentss->timeaccess = $s->timeaccess;
				// $studentss->enrol = $s->enrol;
                
                // echo '<pre>'; print_r($studentss); echo '</pre>';
 
   				if (!insert_record('dean_user_students_g', $studentss))	{
				   notify("Ошибка при добавлении студента в таблицу dean_user_students_g.");
      			}
            }
       }
   }
}            



function how_many_group_have_student($userid)   
{
    global $CFG;
    
    $countgroups = 0;
    if ($dams = get_records_select('dean_academygroups_members', "userid = $userid", 'id', 'id, academygroupid'))   {
        $cnt = count($dams);
        if ($cnt == 1) $countgroups = 1;
        else {
            $grids = array();
            foreach ($dams as $dam) {
                $grids[] = $dam->academygroupid; 
            }    
            $result = array_unique($grids);
            $countgroups = count($result);
        }
    }
    return $countgroups; 
}



function move_to_dean_user_graduates($academygroup, $idnewgr, $astud)    
{
    if ($usr = get_record('user', 'id', $astud->userid))	{
        
        if (record_exists('dean_ldap', 'login', $usr->username))  {
            notify ("Студент $usr->username $usr->lastname числится в LDAP");
            return; 
        }    
        
        $primaryadmin = get_admin();
        if ($usr->id == $primaryadmin->id) {
           notify("You are not allowed to delete the primary admin user!");
           continue;
	    }

    	$usr->userid = $usr->id;
    	$usr->description = addslashes ($usr->description);

		if (insert_record('dean_user_graduates', $usr))	 {

			if (!$usr->deleted) {
			  
                unset($updateuser);
                $updateuser->id = $astud->userid;
                $updateuser->deleted = "1";
                // $updateuser->username = gen_psw($usr->username.$usr->email) . $usr->email . $usr->username;  // Remember it just in case
    			$updateuser->username = "$usr->email.".time().".$usr->username";  // Remember it just in case
                $updateuser->email = "";               // Clear this field to free it up
                $updateuser->idnumber = "";               // Clear this field to free it up
                $updateuser->timemodified = time();
                if (update_record("user", $updateuser))   {
                    unenrol_student_dean($astud->userid);  // From all courses
                    remove_teacher($astud->userid);   // From all courses
                    // remove_admin($astud->userid);
                    notify(get_string("deletedactivity", "", fullname($usr, true)), 'green');;

					$student->academygroupid = $idnewgr;
					$student->userid = $astud->userid;
					$student->timeadded = time();
					if (insert_record('dean_academygroups_members_g', $student))	{
  			        	 delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
						 // $timeadded = time();
						 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					} else  {
					    print_r($student);
						error(get_string('errorinaddinggroupmember','block_dean'), '');
					}

                } else {
                   // notify(get_string("deletednot", "", fullname($usr, true)));
                    error(get_string("deletednot", "", fullname($usr, true)));
                }

            } else  {
				notify('User '. fullname ($usr, true) .'already deleted.');
            }
        } else {
			notify('Can not insert record into dean_user_graduates!!!');
        }
    }
}


function get_id_dean_academygroups_g($academygroup)
{
    global $CFG;
    
    $idnewgr = 0;
    $oldid = $academygroup->id;
    if ($agr = get_record('dean_academygroups_g', 'name', $academygroup->name)) {
        $idnewgr = $agr->id; 
    }  else {
        $newagroup->timemodified = time();        
  		if ($idnewgr = insert_record('dean_academygroups_g', $academygroup))	{
			 // add_to_log(1, 'dean', 'agroup to graduates', "blocks/dean/groups/movegraduategroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
			 notify(get_string('groupadded','block_dean'), 'green');
		} else {
			error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
		}
    }
    
    $academygroup->id = $oldid;
    
    return $idnewgr;
}       


function move_graduate_group($academygroup) 
{
   global $CFG;    

   $idnewgr = get_id_dean_academygroups_g($academygroup);
   
   // echo $idnewgr . '<br>';    print_r($academygroup);

   // get all students
   if ($academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id))	{
                
        move_to_dean_user_students_g($academygroup, $academystudents);
        
        foreach ($academystudents as $astud)	  {        
            $howmany = how_many_group_have_student($astud->userid);
            // echo "!$howmany!<br>";
                    
            if ($howmany > 1)   {
                unset($student);
				$student->academygroupid = $idnewgr;
				$student->userid = $astud->userid;
				$student->timeadded = time();
                // echo '<pre>'; print_r($student); echo '</pre>';
                
				if (insert_record('dean_academygroups_members_g', $student))	{
		        	 delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
					 // $timeadded = time();
					 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
				} else  {
					error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
				}
            } else  {
                move_to_dean_user_graduates($academygroup, $idnewgr, $astud);
            }
        }    
                
	    if ($delgrps = get_records("groups", "name", $academygroup->name))  {
	    	foreach ($delgrps as $delgrp)	{
                delete_records("groups", "id", $delgrp->id);
			}
	    }
        delete_records('dean_academygroups', 'id', $academygroup->id);
        
        return true;
   } else {
        if ($idnewgr > 0)   {
            notify ("Группа $academygroup->name не содержит студентов. Группа удалена.", 'green');
            delete_records('dean_academygroups', 'id', $academygroup->id);
            return true;
        } else {
            notify ("Идентификатор группы равен 0. Группа $academygroup->name не содержит студентов", 'green');            
            return false;
        }
        
   }
}


?>
