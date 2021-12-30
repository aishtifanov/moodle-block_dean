<?php // $Id: sybchrfaculty.php,v 1.4 2013/09/03 14:01:07 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $fid = required_param('fid', PARAM_INT);          // Faculty id

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');
    $struser = get_string("user");
    $strimportfac = get_string('sybchrfaculty','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> $strimportfac";

    print_header("$site->shortname: $strimportfac", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();

    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0)  {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
	}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("importfaculty.php?fid=", $fid);
	echo '</table>';

	if ($fid != 0)  {

        $csv_delimiter = ';';

		/// If a file has been uploaded, then process it
	    require_once($CFG->dirroot.'/lib/uploadlib.php');

    	$um = new upload_manager('userfile',false,false,null,false,0);

	    if ($um->preprocess_files())  {
    	    $filename = $um->files['userfile']['tmp_name'];

        	$fp = fopen($filename, "r");
		    $fequal = fopen ($CFG->dataroot.'/equal.csv', 'w');
		    fwrite($fequal, "username;lastname;firstname;groupnumber\n");
		    $fexist = fopen ($CFG->dataroot.'/exist.csv', 'w');
		    fwrite($fexist, "username;lastname;firstname;groupnumber\n");
		    $fnot = fopen ($CFG->dataroot.'/not.csv', 'w');
		    fwrite($fnot, "username;lastname;firstname;groupnumber\n");

      		$title = split($csv_delimiter, fgets($fp,1024)); // read title \

      		// print_r($title);  echo '<hr>';

            $countitle = count($title);
            if ($countitle < 5)  {
				error(get_string('errorintitle2','block_dean'), '..\faculty\faculty.php');
            }

            for ($i=0; $i<$countitle; $i++)
            	$title[$i] = trim ($title[$i]);

            if ($title[0] != 'username') 	{
				error(get_string('errorintitle','block_dean', 1), '..\faculty\faculty.php');
            }

            if ($title[1] != 'lastname') 	{
				error(get_string('errorintitle','block_dean', 2), '..\faculty\faculty.php');
            }

            if ($title[2] != 'firstname') 	{
				error(get_string('errorintitle','block_dean', 3), '..\faculty\faculty.php');
            }

            if ($title[3] != 'group') 	{
				error(get_string('errorintitle','block_dean', 4), '..\faculty\faculty.php');
            }

            if ($title[4] != 'speciality') 	{
				error(get_string('errorintitle','block_dean', 5), '..\faculty\faculty.php');
            }

			$numusers = 0;

		    while (!feof ($fp)) {
				$line = split($csv_delimiter, fgets($fp,1024));

				$username = trim($line[0]);
                if (!isset($username) || empty($username)) break;

				$lastname = trim($line[1]);
                if (!isset($lastname) || empty($lastname)) break;

				$firstname = trim($line[2]);
                if (!isset($firstname) || empty($firstname)) break;

				$groupnumber1 = trim($line[3]);
                if (!isset($groupnumber1) || empty($groupnumber1))   break;

                if (strlen($groupnumber1) == 7) {
                	$groupnumber = '0'.$groupnumber1;
                } else {
                	$groupnumber = $groupnumber1;
                }

				$specialityname = trim($line[4]);
                if (!isset($specialityname) || empty($specialityname))   break;

	      		// print_r($line);  echo '<hr>';

                if ($olduser = get_record_sql("SELECT id, auth, username, firstname, lastname FROM {$CFG->prefix}user WHERE username = $username"))	{

					if ($academygroup_member = get_record('dean_academygroups_members', 'userid', $olduser->id))	{
						$academygroup = get_record('dean_academygroups', 'id', $academygroup_member->academygroupid);

						if ($olduser->lastname != $lastname)  {
						    fwrite($fexist, "$username;$lastname;$firstname;$groupnumber\n");
						    fwrite($fexist, "$olduser->username;$olduser->lastname;$olduser->firstname;$academygroup->name\n\n");
						    $olduser->lastname = $lastname;
						    $olduser->firstname = $firstname;
							if(update_record("user", $olduser))  {
								 notify("$struser: $olduser->username ", 'green', 'left');
			                     $numusers++;
	  						}
		                }

						if (($olduser->lastname == $lastname) && ($groupnumber != $academygroup->name))	{
						    fwrite($fequal, "$username;$lastname;$firstname;$groupnumber\n");
						    fwrite($fequal, "$olduser->username;$olduser->lastname;$olduser->firstname;$academygroup->name\n\n");

							if ($academygroup_new = get_record('dean_academygroups', 'name', $groupnumber))	{
		                        $academygroup_member->academygroupid = $academygroup_new->id;
								if(update_record("dean_academygroups_members", $academygroup_member))  {
									 notify("dean_academygroups_members: $olduser->username ", 'green', 'left');
				                     $numusers++;
		  						}
		                    }  else {
		                    	notify("Dean academy group #$groupnumber not found !!!");
		                    }
						}
					} else {
						    $olduser->firstname = $firstname;
							if(update_record("user", $olduser))  {
								 notify("$struser: $olduser->username ", 'green', 'left');
			                     $numusers++;
	  						}
							if ($academygroup_new = get_record('dean_academygroups', 'name', $groupnumber))	{
								$academygroup_member->userid =  $olduser->id;
		                        $academygroup_member->academygroupid = $academygroup_new->id;
								$academygroup_member->timeadded = time();
								if (insert_record('dean_academygroups_members', $academygroup_member))	{
									 notify("dean_academygroups_members: $olduser->username ", 'green', 'left');
				                     $numusers++;
		  						}
		                    }  else {
		                    	notify("Dean academy group #$groupnumber not found !!!");
		                    }
					}

                } else {
					    fwrite($fnot, "$username;$lastname;$firstname;$groupnumber\n");
						if ($academygroup = get_record('dean_academygroups', 'name', $groupnumber))		{
			                $user->auth='cas';
			                $user->confirmed = 1;
							$user->username = $username;
					        $user->password = 'not cached';  //Unusable password
							$user->firstname = $firstname;
							$user->lastname = $lastname;
							$user->email = $username.'@bsu.edu.ru';
							$user->city = get_string('town', 'block_dean');
							$user->country = 'RU';
							$user->lang = 'ru_utf8';
							$user->description = get_string('studentsofgroup', 'block_dean') . ' ' . $groupnumber;
			                $user->timemodified = time();

							// print_r($user);  echo '<hr />';

							$flagexistence = false;   // check already registred user
							if ($user1 = get_record('user', 'lastname', $user->lastname, 'firstname', $user->firstname, 'deleted', 0))   {
							    if ($amember = get_record('dean_academygroups_members', 'userid', $user1->id))   {
							    	if ($amember->academygroupid == $academygroup->id)	 {
					                    notify(get_string('usernotaddedregistered', 'error', "$user->lastname $user->firstname"));
										$user = $user1;
										$flagexistence = true;
							    	}
							    }
							}

							if (!$flagexistence) {
								if($user->id = insert_record("user", $user))  {
									 notify("$struser: $user->username ", 'green', 'left');
				                     $numusers++;
		  						}
								else {
				                    notify(get_string('usernotaddederror', 'error', $user->username));
				                    continue;
								}
							}  else {
				                    notify(get_string('alreadyregister', 'block_dean', $user->username));
				                    continue;
							}

			                if (!$amember1 = get_record('dean_academygroups_members', 'userid', $user->id, 'academygroupid', $academygroup->id)) {
								$student->academygroupid = $academygroup->id;
								$student->userid = $user->id;
								$student->timeadded = time();
								if (insert_record('dean_academygroups_members', $student))	{
									 $timeadded = time();
									 notify("Student: $user->lastname $user->firstname", 'green', 'left');
								} else  {
									error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/groups/img.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
								}

								unset ($student);
								$student->userid = $user->id;
								$student->facultyid = $fid;
								$student->specialityid = 0;
								$student->timemodified = time();
								if (insert_record('dean_student_studycard', $student))	{
									$timeadded = time();
									notify("Student studycard added!", 'green', 'left');
								} else  {
									error(get_string('errorinaddingstudcard','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
								}
							}


	                    }  else {
	                    	notify("Dean academy group #$groupnumber not found !!!");
	                    }
                }


            }
	        fclose($fp);
   	        fclose($fequal);
	        fclose($fexist);
	        fclose($fnot);
		    $strusersnew = get_string("usersnew");
    	    notify("$strusersnew: $numusers", 'green', 'left');
	        echo '<hr />';
        }
    }


/// Print the form
    print_heading_with_help($strimportfac, 'importfaculty');


    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose"). ':';
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="sybchrfaculty.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="fid" value="'. $fid .'" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.$strimportfac.'">'.
         '</form></br>';
    echo '</center>';

    print_dean_box_start ('center', '50%', 'white');

    echo '<b>Образец файла синхронизации</b><p>';
?>

<?php
    print_dean_box_end();
	print_footer();



?>