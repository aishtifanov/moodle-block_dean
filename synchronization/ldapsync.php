<?php // $Id: ldapsync.php,v 1.17 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');    

    $namegroup = optional_param('namegroup', '');		// Group name (number)
   	$go = optional_param('go', 0, PARAM_INT);			//     
   	$action = optional_param('action', '');
   	$_username = optional_param('username', '');
   	$_login = optional_param('login', '');   	
    $globalsync = optional_param('globalsync', '', PARAM_TEXT);		// BUTTON
    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id   	

	$strsynchronization = get_string('ldapsynchronization','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    $currenttab = 'ldapsync';
    include('ldaptabs.php');

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
    
    if (!empty($globalsync)) {
        $yid = get_current_edyearid();
        all_group_sync_with_infobelgu($yid, $fid);
    }

 	// $admin_is = true; $go=1; $namegroup = '100459';
	 
	if ($action == 'rokirovka')	{
		if (!empty($_username) && !empty($_login))	{
			 if ($usermanual = get_record('user', 'username', $_username))	{
			 	// print_r($usermanual); echo '<hr>';
		 		if ($userlogin = get_record('user', 'username', $_login))	{
		 			// print_r($userlogin); echo '<hr>';
		 			if ($usermanual->lastname == $userlogin->lastname)	{
		 				// print_r($usermanual); echo '<hr>'; print_r($userlogin); echo '<hr>';
		 				if ($dms = get_records('dean_academygroups_members', 'userid', $userlogin->id))	{
		 					
		 					foreach ($dms as $dm)	{
								$academygroup = get_record_sql("SELECT * FROM {$CFG->prefix}dean_academygroups
	  		       								  			    WHERE id={$dm->academygroupid}");

								if ($mgroups = get_records("groups", "name", $academygroup->name))  {
									 foreach($mgroups as $mgroup)  {
										unenrol_student_dean ($userlogin->id, $mgroup->courseid);
					                	delete_records('groups_members', 'groupid', $mgroup->id, 'userid', $userlogin->id);
									 }
								}
								delete_records('dean_academygroups_members', 'userid', $userlogin->id, 'academygroupid', $academygroup->id);
		 					}
		 					
		 				}
		 				delete_records('user', 'id', $userlogin->id);
		 				notify ("Пользователь с логином  $_login удален.");
		 			} 
		 		}	
			 }
		}
	} 
	
	sync_group_with_infobelgu($namegroup, $go);

    if ($go != 1)	{
	    print_heading($strsynchronization . ' (с данными ИнфоБелГУ:Учебный процесс)', 'center', 2);
    } else {
        notify ('Синхронизация группы с данными LDAP-сервера выполнена. Для просмотра изменений нажмите кнопку "Предварительный просмотр".', 'green');
    }
       
	// notify (get_string('description_synchronization','block_dean'), 'black');
	
    print_dean_box_start('center', '80%');


	echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapsync.php">'.
		 get_string('numgroup', 'block_dean'). '&nbsp&nbsp'.
		 '<input type="text" name="namegroup" size="10" value="' . $namegroup. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsyncpreview   . '" />'.
		 '</form></div>';
	if (isset($namegroup) && !empty($namegroup)) 	{	 
			echo '<div align=center>
			<form name="studentform" id="studentform" method="post" action="ldapsync.php">'.
				 '<input type="hidden" name="go" value="1">' .				 
				 '<input type="hidden" name="namegroup" value="' . $namegroup . '" />'.
			     '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
				 '</form></div>';
	}			 

   	print_dean_box_end();


    if (isadmin()) {
        echo '<br><br><hr>';
    
        print_dean_box_start('center', '80%');
        
        print_heading('Синхронизация состава ВСЕХ групп ИнфоБелГУ:Учебный процесс выбранного факультета с группами Пегаса', 'center', 2);
    	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    	listbox_faculty("ldapsync.php?fid=", $fid);
    	echo '</table>';
        
        if ($fid > 0)   {
            echo '<div align=center>
        			<form name="studentform" id="studentform" method="post" action="ldapsync.php">'.
        				 '<input type="hidden" name="go" value="1">' .				 
                         '<input type="hidden" name="fid" value="' . $fid . '" />'.
        			     '<input name="globalsync" id="globalsync" type="submit" value="Синхронизировать состав ВСЕХ групп выбранного факультета" />'.
        				 '</form></div>';
        }                         
        print_dean_box_end();                     
    }
   	
    print_footer();



function all_group_sync_with_infobelgu($yid, $fid)
{
    // $facultys = get_records_select_menu('dean_faculty', " number > 10000", '', 'number, id');
    $depcode = get_field_select('dean_faculty', 'number', "id = $fid");
    if ($groups = get_records_select('bsu_ref_groups', "yearid=$yid and departmentcode=$depcode", 'name'))   {
        foreach ($groups as $group) {
            sync_group_with_infobelgu($group->name, 1);
            notify ("Синхронизация группы $group->name выполнена.", 'green');            
        }      
    }
}

?>