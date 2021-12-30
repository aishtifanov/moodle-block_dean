<?php // $Id: registrationcard.php,v 1.2 2012/10/19 06:24:43 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/gdlib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/user/editlib.php');
    require_once($CFG->dirroot.'/user/profile/lib.php');
    require_once('registrationcard_form.php');
	require_once('../lib.php');    

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);          // Curriculum id
    $gid = required_param('gid', PARAM_INT);       	  // Group id
    $uid = required_param('uid', PARAM_INT);          // User id
	// $id  = optional_param('id', $USER->id, PARAM_INT);    // user id; -1 if creating new user
	$id = $uid;
	$course = optional_param('course', SITEID, PARAM_INT);   // course id (defaults to Site)

    if (!$course = get_record('course', 'id', $course)) {
        error('Course ID was incorrect');
    }

    include('incl_stud.php');

    if ($course->id == SITEID) {
        $coursecontext = get_context_instance(CONTEXT_SYSTEM);   // SYSTEM context
    } else {
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
    }
    $systemcontext = get_context_instance(CONTEXT_SYSTEM);

	if ($mode != 5) 	{
 		print_footer($course);
 		exit();
 	}	

    if (!$user = get_record('user', 'id', $id)) {
           error('User ID was incorrect');
    }

    // remote users cannot be edited
    if ($user->id != -1 and is_mnet_remote_user($user)) {
        redirect($CFG->wwwroot . "/user/view.php?id=$id&course={$course->id}");
    }

    if ($user->id != $USER->id and is_primary_admin($user->id)) {  // Can't edit primary admin
        print_error('adminprimarynoedit');
    }

    if (isguestuser($user->id)) { // the real guest user can not be edited
        print_error('guestnoeditprofileother');
    }

    if ($user->deleted) {
        print_header();
        print_heading(get_string('userdeleted'));
        print_footer($course);
        die;
    }

    //load user preferences
    useredit_load_preferences($user);

    //Load custom profile fields data
    profile_load_data($user);

    //user interests separated by commas
    if (!empty($CFG->usetags)) {
        require_once($CFG->dirroot.'/tag/lib.php');
        $user->interests = tag_get_tags_csv('user', $id, TAG_RETURN_TEXT); // formslib uses htmlentities itself
    }

    //create form
    $userform = new user_editadvanced_form();
    $userform->set_data($user);

    if ($usernew = $userform->get_data()) {
        add_to_log($course->id, 'user', 'update', "view.php?id=$user->id&course=$course->id", '');

        if (empty($usernew->auth)) {
            //user editing self
            $authplugin = get_auth_plugin($user->auth);
            unset($usernew->auth); //can not change/remove
        } else {
            $authplugin = get_auth_plugin($usernew->auth);
        }

        $usernew->username     = trim($usernew->username);
        $usernew->timemodified = time();

        if ($usernew->id == -1) {
            //TODO check out if it makes sense to create account with this auth plugin and what to do with the password
            unset($usernew->id);
            $usernew->mnethostid = $CFG->mnet_localhost_id; // always local user
            $usernew->confirmed  = 1;
            $usernew->password = hash_internal_user_password($usernew->newpassword);
            if (!$usernew->id = insert_record('user', $usernew)) {
                error('Error creating user record');
            }
            $usercreated = true;
        } else {
        	if ($usernew->auth == 'cas' || $usernew->auth == 'ldap' || $usernew->auth == 'ldap2')	{
        		$usernew->password = 'not cached';
	            if (!update_record('user', $usernew)) {
	                error('Error updating user record');
	            }
        	} else { 
	            if (!update_record('user', $usernew)) {
	                error('Error updating user record');
	            }
	            // pass a true $userold here
	            if (! $authplugin->user_update($user, $userform->get_data(false))) {
	                // auth update failed, rollback for moodle
	                update_record('user', addslashes_object($user));
	                print_r($usernew);
	                error('Failed to update user data on external auth: '.$user->auth.
	                        '. See the server logs for more details.');
	            }
	
	            //set new password if specified
	            if (!empty($usernew->newpassword)) {
	                if ($authplugin->can_change_password()) {
	                    if (!$authplugin->user_update_password($usernew, $usernew->newpassword))	{
	                    	print_r($usernew);
	                        error('Failed to update password on external auth: ' . $usernew->auth .
	                                '. See the server logs for more details.');
	                    }
	                }
	            }
	        }    
            $usercreated = false;
        }

        //update preferences
        useredit_update_user_preference($usernew);

        // update tags
        if (!empty($CFG->usetags)) {
            useredit_update_interests($usernew, $usernew->interests);
        }

        //update user picture
        if (!empty($CFG->gdversion)) {
            useredit_update_picture($usernew, $userform);
        }

        // update mail bounces
        useredit_update_bounces($user, $usernew);

        // update forum track preference
        useredit_update_trackforums($user, $usernew);

        // save custom profile fields data
        profile_save_data($usernew);

        // reload from db
        $usernew = get_record('user', 'id', $usernew->id);


        if ($user->id == $USER->id) {
            // Override old $USER session variable
            foreach ((array)$usernew as $variable => $value) {
                $USER->$variable = $value;
            }
            if (!empty($USER->newadminuser)) {
                unset($USER->newadminuser);
                // apply defaults again - some of them might depend on admin user info, backup, roles, etc.
                admin_apply_default_settings(NULL , false);
                // redirect to admin/ to continue with installation
                redirect($CFG->wwwroot. "/blocks/dean/student/student.php?mode=$mode&fid=$fid&sid=$sid&cid=$cid&gid=$gid&uid=$uid", 0);
            } else {
                redirect($CFG->wwwroot. "/blocks/dean/student/student.php?mode=$mode&fid=$fid&sid=$sid&cid=$cid&gid=$gid&uid=$uid", 0);
            }
        } else {
            redirect($CFG->wwwroot. "/blocks/dean/student/student.php?mode=$mode&fid=$fid&sid=$sid&cid=$cid&gid=$gid&uid=$uid", 0);
        }
        //never reached
    }
		
		
	/// Display page header		
   	$fullname = fullname($user);
    $personalprofile = get_string("personalprofile");
    $participants = get_string("participants");

    if ($user->deleted) {
        print_heading(get_string("userdeleted"));
    }

	/// Print tabs at top
	/// This same call is made in:
	///     ????????
    $currenttab = 'registrationcard';
    include('tabstudent.php');

    $userfullname = fullname($user, true);
    print_heading($userfullname);
		

/// Finally display THE form
    $userform->display();


?>