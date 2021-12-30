<?php // $Id: importgroup.php,v 1.2 2011/09/14 07:01:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $numusers = optional_param('numusers', 0, PARAM_INT);

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');
    $struser = get_string("user");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $strgroups";

    print_header("$site->shortname: $strgroups", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0 && $sid != 0)  {
       error(get_string("errorselect","block_dean"), "importgroup.php?mode=1&amp;fid=0&amp;sid=0&amp;cid=0");
	}

	if ($fid == 0 && $cid != 0)  {
       error(get_string("errorselect","block_dean"), "importgroup.php?mode=1&amp;fid=0&amp;sid=0&amp;cid=0");
	}

	if ($sid == 0 && $cid != 0)  {
       error(get_string("errorselectspec","block_dean"), "importgroup.php?mode=1&amp;fid=$fid&amp;sid=0&amp;cid=0");
	}

	if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }

	if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	}
	elseif (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
    }

	if ($cid == 0)  {
	   $curriculum = get_record_sql("SELECT * FROM {$CFG->prefix}dean_curriculum ", true);
	}
	elseif (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
    }

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("academygroup.php?mode=1&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("academygroup.php?mode=2&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("academygroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=", $fid, $sid, $cid);
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $mode > 2)  {
	    $currenttab = 'importgroup';
	    include('tabsgroups.php');

        $csv_delimiter = ';';

		/// If a file has been uploaded, then process it
	    require_once($CFG->dirroot.'/lib/uploadlib.php');
    	$um = new upload_manager('userfile',false,false,null,false,0);
	    if ($um->preprocess_files())  {
    	    $filename = $um->files['userfile']['tmp_name'];

        	//Fix mac/dos newlines
	        /*$text = my_file_get_contents($filename);
    	    $text = preg_replace('!\r\n?!',"\n",$text);
        	$fp = fopen($filename, "w");
	        fwrite($fp,$text);
    	    fclose($fp); */

			$textlib = textlib_get_instance();

        	$fp = fopen($filename, "r");
			$shapka = fgets($fp,1024);
			$shapka = $textlib->convert($shapka, 'win1251');
	        list($coursekeyword, $courseyear) = split($csv_delimiter, $shapka);
			$courseyear = trim($courseyear);
			if ($coursekeyword != get_string('enrolyear', 'block_dean'))  {
                error(get_string('unknownlabel', 'block_dean', $coursekeyword), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
			}
			if (empty($courseyear))	{
                error(get_string('unknownnumber', 'block_dean'), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
			}

			$shapka = fgets($fp,1024);
			$shapka = $textlib->convert($shapka, 'win1251');

	        list($groupkeyword, $groupnumber) = split($csv_delimiter, $shapka);
			$groupnumber = trim($groupnumber);
			if ($groupkeyword != get_string("group"))  {
                error(get_string('unknownlabel', 'block_dean', $groupkeyword), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
			}
			if (empty($groupnumber))	{
                error(get_string('unknownnumber', 'block_dean'), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
			}

      		fgets($fp,1024);  // pass string


			$shapka = fgets($fp,1024);
			$shapka = $textlib->convert($shapka, 'win1251');

      		$title = split($csv_delimiter, $shapka); // read title \

      		// print_r($title);

            $countitle = count($title);
            if ($countitle < 2)  {
				error(get_string('errorintitle2','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
            }
            for ($i=0; $i<$countitle; $i++)
            	$title[$i] = trim ($title[$i]);

            // $ss = trim();
            if ($title[0] != get_string('lastname')) 	{
				error(get_string('errorintitle0','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
            }

            // $ss = trim($title[1]);
            if ($title[1] != get_string('firstname')) 	{
				error(get_string('errorintitle1','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
            }

           	$s2 = get_string ('surname', 'block_dean');
           	$s3 = get_string ('inostrnii', 'block_dean');
           	$surname = -1;
           	$inostrnii = -1;

            if (isset($title[2]) && !empty($title[2])) 	{
               	if ($title[2] == $s2) {
            		$surname = 2;
            	} elseif ($title[2] == $s3) {
            		$inostrnii = 2;
                }  else  {
					error(get_string('errorintitle3','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
                }
            }

            if (isset($title[3]) && !empty($title[3])) 	{
            	if ($title[3] == $s3) {
            		$inostrnii = 3;
                }  else  {
					error(get_string('errorintitle4','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
                }
            }

			$count = 0;

			if($academygroup = get_record('dean_academygroups', 'name', $groupnumber))  {
				$idnewgr = $academygroup->id;
				/*
				if ($countmember = get_records('dean_academygroups_members', 'academygroupid', $idnewgr)) {
					$count = count($countmember);
				}
				*/
                $strsql = "SELECT id, username FROM {$CFG->prefix}user WHERE username LIKE '{$academygroup->name}%' ORDER BY username";
                if ($allusernamegroup = get_records_sql($strsql))	{
                	$arrayaun =  array();
				    foreach ($allusernamegroup as $aun)		{
					   $arrayaun[] = $aun->username;
				    }

					$newnum = count($arrayaun);

                    /*
                	print_r($arrayaun); echo '<hr>';
                	echo $newnum; echo '<hr>';
                	*/

                    $flag = true;
                    while ($flag)  {

                    	$newnum++;
						if ($newnum <= 9) $newnumusername = trim($groupnumber) . '0' . trim($newnum);
					    else $newnumusername = trim($groupnumber) . trim($newnum);

	                	// echo $newnumusername; echo '<hr>'; echo $newnum; echo '<hr>';

                    	if (!in_array($newnumusername, $arrayaun)) $flag=false;
                    }

   					$count = $newnum - 1;
					/*
                	print_r($allusernamegroup);
                	echo '<hr>';
                	echo $count;
                	*/
                }
                // error(get_string('groupexists', 'block_dean', $groupnumber), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
                $strgroupnumber = "<a href=\"$CFG->wwwroot/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$academygroup->facultyid}&amp;sid={$academygroup->specialityid}&amp;cid={$academygroup->curriculumid}&amp;gid=$idnewgr\">$groupnumber</a>";
                notify(get_string('groupexists', 'block_dean', $strgroupnumber));


			}
			else  {
				$rec->facultyid = $fid;
				$rec->specialityid = $sid;
				$rec->curriculumid = $cid;
				$rec->name = $groupnumber;
				$rec->startyear = $courseyear;
				$rec->term = get_kurs($courseyear);
				$rec->description = "";
				$rec->timemodified = time();
                $rec->idotdelenie =  get_idotdelenie_group($groupnumber);
				if ($idnewgr = insert_record('dean_academygroups', $rec))	{
					  add_to_log(1, 'dean','one academygroup inserted', "blocks/dean/groups/importgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
                      notify(get_string('groupadded','block_dean', $rec->name), 'green', 'left');
				} else {
					error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
				}
			}


		    while (!feof ($fp)) 	{
				$shapka = fgets($fp,1024);
				$shapka = $textlib->convert($shapka, 'win1251');
		    	
				$line = split($csv_delimiter, $shapka);

				$user->lastname = trim($line[0]);

                if (!isset($user->lastname) || empty($user->lastname)) break;

                /// print_r($line);  echo '<hr>'; continue;

				$count++;

				$user->firstname = trim($line[1]);

    			if ($surname > 0)	{
					if (isset($line[$surname]) && ($line[$surname]!= '<noname>'))	{
						$user->firstname .= ' ' . trim($line[$surname]);
					}
				}

				if ($inostrnii > 0) 	{
					if (isset($line[$inostrnii]))	{
						$courselang = trim($line[$inostrnii]);
					}
				}

				if ($count <= 9) $user->username = trim($groupnumber) . '0' . trim($count);
				else $user->username = trim($groupnumber) . trim($count);

                $user->mnethostid = 1;
                $pswtxt = gen_psw($user->username);
                // $user->password = md5($user->username);
                $user->password = hash_internal_user_password($pswtxt);                
				$user->email = 'temp' . $user->username . '@temp.ru';
				$user->city = get_string('town', 'block_dean');
				$user->country = 'RU';
				$user->lang = 'ru_utf8';
                $user->confirmed = 1;
                $user->timemodified = time();
                $user->confirmed = 1;
                $user->description = '-';

				// print_r($user); 	echo '<hr />';
				$flagexistence = false;   // check already registred user
	            if ($user1 = get_record('user', 'username', $user->username)) {
                    notify(get_string('usernotaddedregistered', 'error', "$user->lastname ($user->username)"));
					$user = $user1;
					$flagexistence = true;
                }
				else if ($user1 = get_record('user', 'lastname', $user->lastname, 'firstname', $user->firstname))  {
				    if ($amember = get_record('dean_academygroups_members', 'userid', $user1->id)) {
				    	if ($amember->academygroupid == $idnewgr)	{
		                    notify(get_string('usernotaddedregistered', 'error', "$user->lastname $user->firstname"));
							$user = $user1;
							$flagexistence = true;
				    	}
				    }
				}

				if (!$flagexistence) {
					if(!($user->id = insert_record("user", $user)))  {
						add_to_log(1, 'dean', 'one student added', "blocks/dean/groups/importgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
	                    notify(get_string('usernotaddederror', 'error', $user->username));
	                    continue;
					}
					else {
						 notify("$struser: $user->id &raquo; $user->username &raquo; $pswtxt &raquo; $user->lastname $user->firstname", 'green', 'left');
	                     $numusers++;
					}
				}

                if (!$amember1 = get_record('dean_academygroups_members', 'userid', $user->id, 'academygroupid', $idnewgr)) {
					$student->academygroupid = $idnewgr;
					$student->userid = $user->id;
					$student->timeadded = time();
					if (insert_record('dean_academygroups_members', $student))	{
						 add_to_log(1, 'dean','one academygroup added in academygroup', "blocks/dean/groups/importgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
						 $timeadded = time();
					} else  {
						error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
					}

	 			    // Get groups (moodle) in course with equal name
					if ($mgroups = get_records("groups", "name", $academygroup->name))  	{
						 foreach($mgroups as $mgroup)  {
	 		                if (!enrol_student_dean($student->userid, $mgroup->courseid)) 	{
	            	    	   notify('!!> '. get_string('errornotaddstudenttocourse', 'block_dean', $user->username) . $mgroup->courseid);
							}
	  		        		$record->groupid = $mgroup->id;
		      	            $record->userid = $student->userid;
	    	    	        $record->timeadded = time();
	        	    	    if (!insert_record('groups_members', $record)) 	{
								notify('!!> '. get_string('erroraddingusertogroup', 'block_dean', $user->username) . $mgroup->courseid);
		            	    }
	                    } //foreach
					} //if

					unset ($student);
					$student->userid = $user->id;
					$student->facultyid = $fid;
					$student->specialityid = $sid;
					$student->timemodified = time();
					if (insert_record('dean_student_studycard', $student))	{
						 $timeadded = time();
						 add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					} else  {
						error(get_string('errorinaddingstudcard','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
					}

	                if (isset($courselang))	{
						if($course = get_record('course', 'shortname', $courselang))	 {
	                       if (enrol_student_dean($user->id, $course->id)) {
		                          notify('--> '. get_string('enrolledincourse') . $course->fullname, 'green', 'left');
	  	                          $mgroupa = get_record('groups', 'courseid', $course->id, 'name', $academygroup->name);
	  	                          if ($mgroupa)		{
					  		          $record->groupid = $mgroupa->id;
			 	      	              $record->userid = $user->id;
				   	    	          $record->timeadded = time();
			 	      	    	      if (!insert_record('groups_members', $record)) {
		            	    	  		  notify('!!> '. get_string('erroraddingusertogroup', 'block_dean', $academygroup->name) . $course->shortname);
				               	      }
				               	  } else {
		           	    	  		  notify('!!> '. get_string('errorgroupnotfoundincourse', 'block_dean', $academygroup->name) . $course->shortname);
				               	  }

	 	                   } else {
	   	                      notify('!!> '.get_string('enrolledincoursenot') . $course->fullname);
	   	                   }
						} else  {
							error(get_string('errorinfindshortname','block_dean', $courselang), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
						}
					}
                }
				unset ($user);
				unset ($student);
            }
	        fclose($fp);
		    $strusersnew = get_string("usersnew");
    	    notify("$strusersnew: $numusers", 'green', 'left');
	        echo '<hr />';
        }
    }


/// Print the form
    $struploadusers = get_string("uploadusers", "block_dean");
    print_heading_with_help($struploadusers, 'importgroup');

/*
    $z = '0108880';
    for ($i=1; $i<10; $i++)	{
    	$z1 = $z . "$i";
    	$p = gen_psw($z1);
    	echo "$z1 &raquo; $p <br>";
    }
*/

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose"). ':';
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importgroup.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.$struploadusers.'">'.
         '</form></br>';
    echo '</center>';

    $struploadusers = get_string("uploadandsynchrousers", "block_dean");
    print_heading_with_help($struploadusers, 'importgroup');

    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importsynchrogroup.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.$struploadusers.'">'.
         '</form></br>';
    echo '</center>';

    print_footer();


?>

