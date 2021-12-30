<?PHP // $Id: movegraduategroup.php,v 1.6 2011/11/24 11:46:09 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $fid 	= required_param('fid', PARAM_INT);         // Faculty id
    $sid 	= required_param('sid', PARAM_INT);         // Speciality id
	$cid 	= required_param('cid', PARAM_INT);			// Curriculum id
	$gid 	= required_param('gid', PARAM_INT);			// Discipline id
	$confirm = optional_param('confirm', 0, PARAM_INT);

    $strfaculty = get_string('faculty','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');
	$strdelgroup = get_string('deletegroup','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid\">$strgroups</a>";
	$breadcrumbs .= " -> $strdelgroup";

    print_header("$SITE->shortname: $strdelgroup", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Academy group not found!");
	}

    // echo '<pre>'; print_r($academygroup); echo '</pre>';
     
	if ($confirm == 1) {
        $redirlink = "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid";
        // redirect($redirlink, 'Перемещение групп временно заблокировано.', 60);
        
        if (move_graduate_group($academygroup)) {
            redirect($redirlink, get_string('movedgraduategroup','block_dean'), 60);
        } else {
    		notify('Students not found in this group.');
            redirect($redirlink, get_string('movedgraduategroup','block_dean'), 60);
        }                
   }
    
	print_heading(get_string('movinggraduategroup','block_dean', $academygroup->name));

	notice_yesno(get_string('movingcheckfull', 'block_dean', $academygroup->name),
               "movegraduategroup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;confirm=1", 
               "academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");

	print_footer();



function move_to_dean_user_students_g($academygroup, $academystudents)
{
    global $CFG;
    
    $time = time();
    
	if ($mgroups = get_records_sql("SELECT courseid, name FROM {$CFG->prefix}groups WHERE name = '{$academygroup->name}'"))   {
    
	    foreach ($academystudents as $astud)	  {
	      
	         // if ( $std_courses = get_records('user_students', 'userid', $astud->userid)){
             // foreach ($std_courses as $s){
             foreach ($mgroups as $mg) {
                unset($studentss);
                
                $studentss->userid = $astud->userid;
				$studentss->course = $mg->courseid;
				$studentss->timestart = $time; // $s->timestart;
				$studentss->timeend = $time; //$s->timeend;
				$studentss->time = $time;  
				$studentss->timeaccess = $time; //$s->timeaccess;
				$studentss->enrol = ''; // $s->enrol;
                
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
        return false;
   }
}


?>
