<?php // $Id: __zaoch.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    // require_once('lib.php');

    $strtitle = 'Создание ресурсов для заочников';
    
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

  	create_courses_zaochnikov();
 
	print_footer();
	
	
	
function create_courses_zaochnikov() 
{
	global $CFG, $db;
	
    $fid = 1;
    $groupsnames = array('010752'); // '010652', '010653');
    
    foreach ($groupsnames as $groupname)    {
        
        $agroup = get_record_select ('dean_academygroups', "facultyid = 1 and name = $groupname", 'id, name');
        
        $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture
                   FROM {$CFG->prefix}user u
                   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
				   WHERE m.academygroupid = $agroup->id AND u.deleted = 0 AND u.confirmed = 1
				   ORDER BY u.lastname DESC, u.firstname DESC";

        // print_r($studentsql); echo '<hr>';

        if($students = get_records_sql($studentsql)) {

            foreach ($students as $student) {

                $fullname = fullname($student);
                 
                $data->MAX_FILE_SIZE=16777216;
                $data->category=87;
                $data->fullname='Курс ' . $fullname;
                $data->shortname='Курс_' . $student->username;
                $data->idnumber='';
                $data->summary='';
                $data->format='topics';
                $data->numsections=10;
                $data->startdate=1318536000;
                $data->hiddensections=0;
                $data->newsitems=5;
                $data->showgrades=1;
                $data->showreports=0;
                $data->maxbytes=16777216;
                $data->metacourse=0;
                $data->enrol='';
                $data->defaultrole=0;
                $data->enrollable=1;
                $data->enrolstartdate=0;
                $data->enrolstartdisabled=1;
                $data->enrolenddate=0;
                $data->enrolenddisabled=1;
                $data->enrolperiod=0;
                $data->expirynotify=0;
                $data->notifystudents=0;
                $data->expirythreshold=864000;
                $data->groupmode=2;
                $data->groupmodeforce=0;
                $data->visible=1;
                $data->enrolpassword='';
                $data->guest=0;
                $data->lang='';
                $data->restrictmodules=0;
                $data->role_1='';
                $data->role_2='';
                $data->role_3='';
                $data->role_4='';
                $data->role_5='';
                $data->role_6='';
                $data->role_7='';
                $data->role_8='';
                $data->role_9='';
                $data->role_10='';
                $data->role_11='';
                $data->role_12='';
                $data->role_13='';
                $data->role_14='';
                $data->role_15='';
                $data->role_16='';
                $data->submitbutton='Сохранить';
                $data->id=0;
                $data->teacher='Учитель';
                $data->teachers='Учителя';
                $data->student='Студент';
                $data->students='Студенты';
                $data->password='';
                $data->timemodified=1318511954;
                
                echo '<pre>'; print_r($data); echo '</pre>';
                if (!$course = create_course($data)) {
                    print_error('coursenotcreated');
                }
    
                $context = get_context_instance(CONTEXT_COURSE, $course->id);
                role_assign(3, $student->id, 0, $context->id);
                
                // exit(0);
           }      
       }   
        
    }   
   

	return true;
}	 

?>


