<?php // $Id: importsynchrogroup.php,v 1.2 2011/09/14 07:01:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$cid = required_param('cid', PARAM_INT);		  // Curriculum id


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



       $csv_delimiter = ';';

	/// If a file has been uploaded, then process it
    require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);

    if ($um->preprocess_files()) {

   	    $filename = $um->files['userfile']['tmp_name'];

       	$fp = fopen($filename, "r");
        list($coursekeyword, $courseyear) = split($csv_delimiter, fgets($fp,1024));
		$courseyear = trim($courseyear);
		if ($coursekeyword != get_string('enrolyear', 'block_dean'))  {
               error(get_string('unknownlabel', 'block_dean', $coursekeyword), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}
		if (empty($courseyear))	{
               error(get_string('unknownnumber', 'block_dean'), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}

        list($groupkeyword, $groupnumber) = split($csv_delimiter, fgets($fp,1024));
		$groupnumber = trim($groupnumber);
		if ($groupkeyword != get_string("group"))  {
               error(get_string('unknownlabel', 'block_dean', $groupkeyword), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}
		if (empty($groupnumber))	{
               error(get_string('unknownnumber', 'block_dean'), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}

     	fgets($fp,1024);  // pass string

     	$title = split($csv_delimiter, fgets($fp,1024)); // read title \

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

        $surname = 2;
        $formlearning = 3;

		$countmember = 0;
		$countinfile = 0;

		if($academygroup = get_record('dean_academygroups', 'name', $groupnumber))  {

			$idnewgr = $academygroup->id;
			if ($allmember = get_records('dean_academygroups_members', 'academygroupid', $idnewgr)) {
				$countmember = count($allmember);
			}

            notify(get_string('groupexists', 'block_dean', $groupnumber));

		    while (!feof ($fp)) {
				$line = split($csv_delimiter, fgets($fp,1024));
                if (!isset($line[0])) break;
                if (empty($line[0])) break;

				$countinfile++;

				$userlastnames[$countinfile] = trim($line[0]);
				$userfirstnames[$countinfile] = trim($line[1]);

    			if ($surname > 0)	{
					if (isset($line[$surname]) && ($line[$surname]!= '<noname>'))	{
						$userfirstnames[$countinfile] .= ' ' . trim($line[$surname]);
					}
				}

				if ($formlearning > 0) 	{
					if (isset($line[$formlearning]))	{
						$userformlearnings[$countinfile] = trim($line[$formlearning]);
					}
				}

            }
			fclose($fp);

            notify("countmember:$countmember -|- countinfile:$countinfile");
            $i=0;
            foreach ($allmember as $member)		{
            	$u = get_record('user', 'id', $member->userid);
            	$allstudent[$i]->lastname = $u->lastname;
            	$allstudent[$i]->firstname = $u->firstname;
				$allstudent[$i]->userid = $u->id;
				$i++;
            }

            $c=0;
            foreach ($allstudent as $student)		{
            	$i = array_search($student->lastname, $userlastnames);
            	if ($i)  {
            	    if ($student->firstname == $userfirstnames[$i])  {
            	    	$c++;
       		     		notify("FOUND!!! $student->lastname $student->firstname === $userlastnames[$i] $userfirstnames[$i]");
       		     		$card = get_record('dean_student_studycard', 'userid', $student->userid);
       		     		if ($card)	{
       		     			if ($userformlearnings[$i] == 'Á')	{
		    		      		$card->financialbasis = 'fb';
		    		      	} else if ($userformlearnings[$i] == 'Ä')	{
		    		      		$card->financialbasis = 'fd';
		    		      	}
							if (update_record('dean_student_studycard', $card))	{
			 				   notify(get_string('studycardupdate','block_dean'));
							} else {
							   error(get_string('errorinupdatingstudycard','block_dean', $student->lastname), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
							}
      		     		} else {
				 	       $user_student->userid = $student->userid;
						   $user_student->facultyid = $fid;
				      	   $user_student->specialityid = $sid;
				      	   $user_student->recordbook = 0;
				      	   $user_student->birthday = time();
				      	   $user_student->whatgraduated = '';
				      	   $user_student->previousdiploma = '';
				      	   $user_student->issueday = time();
				      	   $user_student->job = '';
				      	   $user_student->appointment = '';
       		     			if ($userformlearnings[$i] == 'Á')	{
		    		      		$user_student->financialbasis = 'fb';
		    		      	} else if ($userformlearnings[$i] == 'Ä')	{
		    		      		$user_student->financialbasis = 'fd';
		    		      	} else {
				           	 	$user_student->financialbasis = '-';
				            }
						   $user_student->ordernumber = '0';
				   		   $user_student->enrolmenttype = 10;
		     			   if (insert_record('dean_student_studycard', $user_student))	{
			 				   notify(get_string('studycardcreate','block_dean').":$userformlearnings[$i]");
	       				   } else  {
 	       					   error(get_string('errorincreatingstudentstudycard','block_dean'), "$CFG->wwwroot/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user_student->id}");
		     	 	 	   }

      		     		}

            		}
            	} else {
   		            notify("NOT FOUND??? $student->lastname $student->firstname");
            	}
            }
            notify("--- $c ---");
		}
		else  {   // BEGIN CREATE GROUP AND ENROL AND CREATE STUDYCARD
               // error(get_string('groupnotexists', 'block_dean', $groupnumber), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
               notify(get_string('groupnotexists', 'block_dean', $groupnumber));

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
				 // add_to_log(1, 'dean',z 'one academygroup added', "blocks/dean/groups/addgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
                   notify(get_string('groupadded','block_dean'));
			} else {
				error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
			}
			$count = 0;
		    while (!feof ($fp)) {
				$line = split($csv_delimiter, fgets($fp,1024));
                if (!isset($line[0])) break;
                if (empty($line[0])) break;
				$count++;

				$user->lastname = trim($line[0]);
				$user->firstname = trim($line[1]);
				$user->firstname .= ' ' . trim($line[2]);
				$fl = trim($line[3]);

				if ($count <= 9) $user->username = trim($groupnumber) . '0' . trim($count);
				else $user->username = trim($groupnumber) . trim($count);

                $user->password = md5($user->username);
				$user->email = 'temp@temp.ru';
				$user->city = get_string('town', 'block_dean');
				$user->country = 'RU';
				$user->lang = 'ru_utf8';
                $user->confirmed = 1;
                $user->timemodified = time();

	            if ($user1 = get_record("user","username",$user->username)) {
                    notify(get_string('usernotaddedregistered', 'error', "$user->lastname ($user->username)"));
					$user = $user1;
                }
				else {
                    if(!($user->id = insert_record("user", $user)))  {
                       notify(get_string('usernotaddederror', 'error', $user->username));
                       continue;
					}
					else {
					   notify("$struser: $user->id &raquo; $user->username &raquo; $user->lastname $user->firstname");
                       $numusers++;
					}

					$student->academygroupid = $idnewgr;
					$student->userid = $user->id;
					$student->timeadded = time();
					if (insert_record('dean_academygroups_members', $student))	{
  						 $timeadded = time();
						 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					} else  {
						error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
					}
					unset ($student);

					$student->userid = $user->id;
					$student->facultyid = $fid;
					$student->specialityid = $sid;
					$student->timemodified = time();
					if ($fl == 'Á')	{
	    		       $student->financialbasis = 'fb';
	    			} else if ($fl == 'Ä')	{
	    		 	   $student->financialbasis = 'fd';
	    		    } else {
			           $student->financialbasis = '-';
			        }

					if (insert_record('dean_student_studycard', $student))	{
  						 $timeadded = time();
	 				     notify(get_string('studycardcreate','block_dean').":$fl");
						 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					} else  {
						error(get_string('errorinaddingstudcard','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
					}

					unset ($user);
					unset ($student);
                }
            }
	        fclose($fp);
		    $strusersnew = get_string("usersnew");
    	    notify("$strusersnew: $numusers");
	        echo '<hr />';
		} // END CREATE GROUP AND ENROL AND CREATE STUDYCARD
    }

/// Print the form
    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose"). ':';

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

