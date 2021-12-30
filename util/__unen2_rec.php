<?php // $Id: __unen2_rec.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    require_once('lib.php');

    $year = optional_param('year', '04'); // course id
    $i = optional_param('i', 0, PARAM_INT); // course id    
    
    $strtitle = "Move graduated group $year";

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

/*
    print_list_group('04');
    print_list_group('05');
    print_list_group('06');
*/

    $agroups = array('160450', '130451', '130454', '130453', '060407', '130413', '090401', '090402', '090403', '120451', '080400', '120411', 
                       '100553', '100554', '100551', '100552', '100557', '140553', '140551', '160550', '090558', '130554', '130553', 
                       '170564', '170565', '170566', '170552', '080571', '080572', '080573', '080574', '100565', '100561', '100560',
                        '100558', '100562', '100568', '100559', '100563', '080551', '080552', '080553', '080554', '080555', '080556', 
                        '080557', '080558', '080559', '080560', '120552', '120553', '100567', '180551', '140507', '140508', '140509', 
                        '140511', '140501', '010501', '010504', '010506', '070506', '100503', '170567', '100506', '140503', '140504', 
                        '140505', '140506', '110501', '110502', '110503', '100505', '100507', '100508', '100510', '190504', '190501', 
                        '190503', '180504', '130504', '130506', '130503', '130505', '060502', '060504', '060503', '060501', '060507',
                        '020501', '020502', '040502', '040504', '050501', '050502', '150501', '150502', '150503', '150504', '150505', 
                        '150506', '150507', '150508', '150509', '170503', '170504', '170501', '170502', '100511', '100512', '100514', 
                        '100515', '100517', '170507', '170508', '170509', '100513', '200501', '200502', '090501', '090502', '090503', 
                        '040509', '170506', '130513', '010551', '010510', '020551', '020552', '020503', '020504', '030520', '040550', 
                        '040510', '060553', '060550', '060551', '060554', '060557', '060552', '060556', '060580', '070553', '070551', 
                        '070552', '090510', '090513', '090508', '090506', '090505', '090504', '090507', '090512', '100518', '100556', 
                        '100569', '100570', '110562', '110508', '110516', '110506', '120508', '120551', '120507', '130512', '140513', 
                        '140512', '160503', '190511', '200554', '200555', '200551', '200553', '200552', '160500', '080509', '060508', 
                        '060509', '110561', '110564', '110563', '110504', '110505', '110500', '110521', '170555', '060565',
                        '110650', '100664', '090658', '090659', '090660', '090657', '090656', '150674', '100681', '100682', '030680', 
                        '140601', '140602', '140603', '140609', '070605', '110611', '110612', '110613', '110614', '140610', '150601', 
                        '150602', '140608', '140607', '140604', '140605', '140606', '110601', '110602', '110603', '050601', '050602', 
                        '050603', '100607', '100608', '100610', '100611', '100613', '100614', '100615', '100601', '100602', '100603', 
                        '100604', '100605', '190604', '190601', '190603', '010604', '010605', '010603', '010606', '080610', '080611', 
                        '180601', '180602', '180603', '180604', '130601', '130604', '130606', '130603', '130607', '130605', '060602', 
                        '060605', '060603', '060601', '060604', '050604', '020601', '020602', '040602', '040604', '040608', '040605', 
                        '040601', '070606', '070601', '070602', '150605', '170604', '170605', '170606', '170601', '170602', '170603', 
                        '030603', '030604', '030605', '030606', '030681', '100606', '100609', '100612', '200601', '200602', '010655', 
                        '020650', '030614', '030615', '030616', '030611', '030613', '030608', '030617', '030620', '030612', '030609', 
                        '030621', '030607', '030610', '030618', '030619', '040603', '050600', '090617', '090615', '090616', '090651', 
                        '090650', '090653', '090652', '090600', '100683', '150600', '160602', '160601', '160604', '160600', '040600', 
                        '180600', '170600', '140600', '050600', '110600');
    
    
    notify('Группа ' . $agroups[$i]);
    $agroupname =  $agroups[$i];
    if (record_exists_select('dean_ldap', "group1 = '$agroupname' OR group2 = '$agroupname'"))  {
        notify ($agroupname. ' EXISTS IN dean_ldap and NOT moving in graduated group !!!');
    } else {
        $strsql = "SELECT id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
                   FROM mdl_dean_academygroups
                   WHERE name = '$agroupname'";
        if ($agroup = get_record_sql($strsql))   {               
            move_graduate_group($agroup);
        }    
    }    
  
    $cnt = count($agroups);
    notify ("Выполнено $i из $cnt");
    $i++;        
    if ($i <= $cnt)  { 
        sleep(10);
        redirect("__unen2_rec.php?i=$i");
    }
                             
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
        
        if (!record_exists('dean_user_graduates', 'username', $usr->username))   { 
            if (!insert_record('dean_user_graduates', $usr))	 {
 			    notify('Can not insert record into dean_user_graduates!!!');
            }    
		}      

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
			error(get_string('errorinaddinggroup','block_dean'), "../index.php");
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



function print_list_group($year)
{
    $lgrnames = array();
    $strsql = "SELECT DISTINCT group1 FROM mdl_dean_ldap where group1 like '__{$year}__'";
    if ($lgroups = get_records_sql($strsql))    {
        foreach ($lgroups  as $lgroup)  {
            $lgrnames[] = $lgroup->group1;
        }
    }


    $strsql = "SELECT id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
               FROM mdl_dean_academygroups where name like '__{$year}__'";
    if ($agroups = get_records_sql($strsql))   {
                
        $agrnames = array();
        foreach ($agroups as $agroup)   {
            if (in_array($agroup->name, $lgrnames))  {
                notify ($agroup->name, 'green');
            } else {
                $agrnames[] = $agroup->name;            
            }
        }          
    }
    
    $strgroups = implode("', '", $agrnames);
    
    echo $strgroups;
}

?>
