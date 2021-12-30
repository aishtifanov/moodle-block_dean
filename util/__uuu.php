<?php // $Id: __uuu.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __ Move foto users in new place";
    print_header("$SITE->shortname: __ Move foto users in new place", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();


    $teachers = get_records_sql("SELECT distinct c.userid, u.username FROM mdl_dean_teacher_card c
                                inner join mdl_user u on u.id=c.userid");
    $ateach = array();                            
    foreach ($teachers as $teacher) {
        $ateach[$teacher->userid] = $teacher->username;  
    }                      
    
    $teachers = get_records('dean_teacher_card');
    foreach ($teachers as $teacher) {
        if (isset($ateach[$teacher->userid]))   {
            set_field('dean_teacher_card', 'username', $ateach[$teacher->userid], 'userid', $teacher->userid);
        }
    }          

    $teachers = get_records_sql("select distinct c.staffid, s.subdepartmentid from mdl_dean_teacher_card c
                                 inner join mdl_bsu_staffform s on s.id=c.staffid");
    $ateach = array();                            
    foreach ($teachers as $teacher) {
        $ateach[$teacher->staffid] = $teacher->subdepartmentid;  
    }                      
    
    $teachers = get_records('dean_teacher_card');
    foreach ($teachers as $teacher) {
        if (isset($ateach[$teacher->staffid]))   {
            set_field('dean_teacher_card', 'subdepartmentid', $ateach[$teacher->staffid], 'staffid', $teacher->staffid);
        }
    }          
    

    echo 'Complete';
    
    
	print_footer();
/*
    exit();
    
        // Get list of users by browsing moodledata/user
        $oldusersdir = $CFG->dataroot . '/users';
        $folders = get_directory_list($oldusersdir, '', false, true, false);

        foreach ($folders as $userid) {
            $olddir = $oldusersdir . '/' . $userid;
            $files = get_directory_list($olddir);

            if (empty($files)) {
                continue;
            }

            // Create new user directory
            if (!$newdir = make_user_directory($userid)) {
                $result = false;
                break;
            }

            // Move contents of old directory to new one
            if (file_exists($olddir) && file_exists($newdir)) {
                foreach ($files as $file) {
                    copy($olddir . '/' . $file, $newdir . '/' . $file);
                }
            } else {
                notify("Could not move the contents of $olddir into $newdir!");
                $result = false;
                break;
            }
        }

        // Leave a README in old users directory
        $readmefilename = $oldusersdir . '/README.txt';
        if ($handle = fopen($readmefilename, 'w+b')) {
            if (!fwrite($handle, get_string('olduserdirectory'))) {
                // Could not write to the readme file. No cause for huge concern
                notify("Could not write to the README.txt file in $readmefilename.");
            }
            fclose($handle);
        } else {
            // Could not create the readme file. No cause for huge concern
            notify("Could not create the README.txt file in $readmefilename.");
        }

 
	print_footer();
*/	

?>


