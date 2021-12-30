<?PHP // $Id: delstud.php,v 1.2 2011/02/07 13:15:52 shtifanov Exp $

    require_once("../../../config.php");
	require_once('../lib.php');    

    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);          // Speciality id
    $cid = optional_param('cid', 0, PARAM_INT);          // Curriculum id
    $gid = optional_param('gid', 0, PARAM_INT);       	  // Group id
    $delete  = required_param('uid', PARAM_INT);
	$confirm = optional_param('confirm');

	require_login();

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> Delete student";
    print_header("$SITE->shortname: Delete student", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();

    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

 	if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation

        // if (!has_capability('moodle/user:delete', $sitecontext)) {
            // error('You do not have the required permission to delete a user.');
        // }
		$redirlink = "searchstudent.php";
		
        if (!$user = get_record('user', 'id', $delete)) {
            error("No such user!", '', true);
        }

        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            error("You are not allowed to delete the primary admin user!", '', true);
        }

        if ($confirm != md5($delete)) {
            $fullname = fullname($user, true);
            // print_heading(get_string('deleteprofilepupil', 'block_mou_school'));
            $optionsyes = array('fid'=>$fid, 'sid'=>$sid, 'cid'=>$cid, 'gid'=>$gid, 'uid'=>$delete,
            					'confirm'=>md5($delete), 'sesskey'=>sesskey());
	        notice_yesno(get_string('deletecheckfull', '', "'$fullname'"), 'delstud.php', $redirlink, $optionsyes, $optionsyes, 'post', 'get');

        } else if (data_submitted() and !$user->deleted) {
            //following code is also used in auth sync scripts
			delete_records('user', 'id', $user->id);
            redirect($redirlink, get_string('deletedactivity', '', fullname($user, true)), 3);
			/*
            $updateuser = new object();
            $updateuser->id           = $user->id;
            $updateuser->deleted      = 1;
            $updateuser->username     = addslashes("$user->email.".time());  // Remember it just in case
            $updateuser->email        = '';               // Clear this field to free it up
            $updateuser->idnumber     = '';               // Clear this field to free it up
            $updateuser->timemodified = time();
            if (update_record('user', $updateuser)) {
                // Removing a user may have more requirements than just removing their role assignments.
                // Use 'role_unassign' to make sure that all necessary actions occur.
                // role_unassign(0, $user->id);
                unenrol_student_dean ($user->id); 
                // remove all context assigned on this user?
                // notify(get_string('deletedactivity', '', fullname($user, true)) );
		   		redirect($redirlink, get_string('deletedactivity', '', fullname($user, true)), 3);

            } else {
           		redirect($redirlink, get_string('deletednot', '', fullname($user, true)), 5);
               // notify(get_string('deletednot', '', fullname($user, true)));
            }
            */
        }
    }

	print_footer();
?>
