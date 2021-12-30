<?php // $Id: importmanygroups.php,v 1.7 2012/10/19 06:24:42 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $numusers = optional_param('numusers', 0, PARAM_INT);
    $withlogin = optional_param('withlogin', 1, PARAM_INT);

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

    print_header("$SITE->shortname: $strgroups", $SITE->fullname, $breadcrumbs);

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
	listbox_faculty("importmanygroups.php?mode=1&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("importmanygroups.php?mode=2&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("importmanygroups.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=", $fid, $sid, $cid);
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $mode > 2)  {
	    $currenttab = 'importmanygroup';
	    include('tabsgroups.php');

        $csv_delimiter = ';';

		/// If a file has been uploaded, then process it
	    require_once($CFG->dirroot.'/lib/uploadlib.php');
    	$um = new upload_manager('userfile',false,false,null,false,0);
	    if ($um->preprocess_files())  {
    	    $filename = $um->files['userfile']['tmp_name'];

			$textlib = textlib_get_instance();

        	$fp = fopen($filename, "r");
			$shapka = fgets($fp,1024);
			$shapka = $textlib->convert($shapka, 'win1251');

      		$title = split($csv_delimiter, $shapka); // read title \

      		// print_r($title);
      		$redirlink = "$CFG->wwwroot/blocks/dean/groups/importmanygroups.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid";

            $countitle = count($title);
            if ($countitle < 3)  {
				error(get_string('errorintitle2','block_dean'), $redirlink);
            }

            for ($i=0; $i<$countitle; $i++)
            	$title[$i] = trim ($title[$i]);


            if ($title[0] != get_string('lastname')) 	{
				error(get_string('errorintitle0','block_dean'), $redirlink);
            }

            if ($title[1] != get_string('firstname')) 	{
				error(get_string('errorintitle1','block_dean'), $redirlink);
            }

            if ($title[2] != get_string('group')) 	{
				error(get_string('errorintitle5','block_dean'), $redirlink);
            }

			switch($withlogin)	{
				case 1: case 5:
				case 11:if ($title[3] != get_string('username')) 	{
							error(get_string('errorintitle3','block_dean'), $redirlink);
	            	    }
	            break;
				case 2: if ($title[3] != get_string('username')) 	{
							error(get_string('errorintitle3','block_dean'), $redirlink);
	            	    }		
				        if ($title[4] != get_string('password')) 	{
							error(get_string('errorintitle4','block_dean'), $redirlink);
	            		}
	            break;
			}


		    while (!feof ($fp)) {
				$shapka = fgets($fp,1024);
				$shapka = $textlib->convert($shapka, 'win1251');
		    	
				$line = split($csv_delimiter, $shapka);

				$user->lastname = trim($line[0]);
                if (!isset($user->lastname) || empty($user->lastname)) break;

				$user->firstname = trim($line[1]);
                if (!isset($user->firstname) || empty($user->firstname)) break;

				$groupnumber = trim($line[2]);
                if (!isset($groupnumber) || empty($groupnumber))   break;

				if ($withlogin == 1 || $withlogin == 11 || $withlogin == 5)	{
					$user->username = trim($line[3]);
	                if (!isset($user->username) || empty($user->username)) break;
	                $user->password = 'not cached';
	                /*
					$user->password = trim($line[4]);
	                if (!isset($user->password) || empty($user->password)) break;
	                */ 
	            } else if ($withlogin == 2)	{
					$user->username = trim($line[3]);
	                if (!isset($user->username) || empty($user->username)) break;

					$user->password = trim($line[4]);
	                if (!isset($user->password) || empty($user->password)) break;
	                $user->pswtxt = $user->password; 
	            }	


				$count = 1;
				if($academygroup = get_record('dean_academygroups', 'name', $groupnumber))  {
					$idnewgr = $academygroup->id;

	                $strsql = "SELECT id, username FROM {$CFG->prefix}user WHERE username LIKE '{$academygroup->name}%' ORDER BY username";
	                if ($allusernamegroup = get_records_sql($strsql))	{

	                	$arrayaun =  array();
					    foreach ($allusernamegroup as $aun)		{
						   $arrayaun[] = $aun->username;
					    }

						$newnum = count($arrayaun);

	                    $flag = true;
	                    while ($flag)  {

	                    	$newnum++;
							if ($newnum <= 9) $newnumusername = trim($groupnumber) . '0' . trim($newnum);
						    else $newnumusername = trim($groupnumber) . trim($newnum);

	                    	if (!in_array($newnumusername, $arrayaun)) $flag=false;
	                    }

	   					$count = $newnum;
	                }

					$strgroupnumber = "<a href=\"$CFG->wwwroot/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$academygroup->facultyid}&amp;sid={$academygroup->specialityid}&amp;cid={$academygroup->curriculumid}&amp;gid=$idnewgr\">$groupnumber</a>";
	                notify(get_string('groupexists', 'block_dean', $strgroupnumber), 'green', 'left');

				} else  {
					$rec->facultyid = $fid;
					$rec->specialityid = $sid;
					$rec->curriculumid = $cid;
					$rec->name = $groupnumber;
					$courseyear = date ("Y");
					$rec->startyear = $courseyear;
					$rec->term = get_kurs($courseyear);
					$rec->description = "";
					$rec->timemodified = time();
                    $rec->idotdelenie =  get_idotdelenie_group($groupnumber);

					if ($idnewgr = insert_record('dean_academygroups', $rec))	{
						// add_to_log(1, 'dean', 'one academygroup added', "blocks/dean/groups/addgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
	                    notify(get_string('groupadded','block_dean', $rec->name), 'green', 'left');
					} else {
						error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importmanygroups.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
					}
				}


                if ($withlogin == 5)	{
                    
                    // $user->auth = 'ldap2';
                    $user->auth = 'cas';
                    $user->email = $user->username . '@bsu.edu.ru';
                    
				} else if ($withlogin == 11)	{
					
					$user->auth = 'ldap3';
                    $user->email = $user->username . '@bsu.edu.ru';
					
				} else if ($withlogin == 2)	{
					
					$user->auth = 'manual';
                    $user->pswtxt = $user->password;
				    $user->password = hash_internal_user_password($user->pswtxt);
                    $user->email = $user->username . '@temp.ru';
				    
                } else if ($withlogin == 1)	{
                	
                    $user->auth = 'cas';
                    $user->email = $user->username . '@bsu.edu.ru';
					
                } else if ($withlogin == 0) {
                	
	                $user->auth = 'manual';
					                	
					if ($count <= 9) $user->username = trim($groupnumber) . '0' . trim($count);
					else $user->username = trim($groupnumber) . trim($count);

	                $user->pswtxt = gen_psw($user->username);
                 	$user->password = hash_internal_user_password($user->pswtxt);
                    $user->email = $user->username . '@temp.ru';                    
	            }


                $user->mnethostid = 1;
				$user->city = get_string('town', 'block_dean');
				$user->country = 'RU';
				$user->lang = 'ru_utf8';
                $user->confirmed = 1;
                $user->timemodified = time();
                $user->description = '-';                

				// print_r($user);    echo '<hr />';
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
				/*
                if ($flagexistence)	{
                	
                	if($withlogin == 1)	{
                		continue;
                	} else if (	
                }*/


				if (!$flagexistence) {
					if(!($user->id = insert_record("user", $user)))  {
	                    notify(get_string('usernotaddederror', 'error', $user->username));
	                    continue;
					}
					else {
						 notify("$struser: $user->id &raquo; $user->username &raquo; $user->pswtxt &raquo; $user->lastname $user->firstname", 'green', 'left');
	                     $numusers++;
					}
				}

                if (!$amember1 = get_record('dean_academygroups_members', 'userid', $user->id, 'academygroupid', $idnewgr)) {
					$student->academygroupid = $idnewgr;
					$student->userid = $user->id;
					$student->timeadded = time();
					if (insert_record('dean_academygroups_members', $student))	{
						 $timeadded = time();
						 notify(fullname($user) . ' добавлен в группу.', 'green', 'left');
						 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					} else  {
						error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importmanygroups.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
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
	                    notify(fullname($user) . ' подписан на курсы.', 'green', 'left');
					} //if

					unset ($student);
					$student->userid = $user->id;
					$student->facultyid = $fid;
					$student->specialityid = $sid;
					$student->timemodified = time();
					if (insert_record('dean_student_studycard', $student))	{
						 $timeadded = time();
						 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					} else  {
						error(get_string('errorinaddingstudcard','block_dean'), "$CFG->wwwroot/blocks/dean/groups/importgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
					}
                } else {
                	notify(fullname($user) . ' уже числится в группе ' . $groupnumber, 'green', 'left');
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
    $struploadusers = get_string('uploadusersmanygroup', 'block_dean');
    print_heading_with_help($struploadusers, 'importgroup');


    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose"). ':';
	print_dean_box_start ('center', '50%', 'white');
    echo '<b>1-й образец файла импорта. Предназначен для автоматической регистрации студентов БЕЗ ИСПОЛЬЗОВАНИЯ ДАННЫХ СЕРВЕРА LDAP университета с автоматической генерацией логина и пароля.</b><p>';
?>
Фамилия;Имя;Группа;<br>
Афанасов;Кирилл Владимирович;010404;<br>
Федорова;Александра Ивановна;010404;<br>
Марунченко;Дмитрий Петрович;010406;<br>
Селезнева;Татьяна Ивановна;010406;<br>
Романченко;Татьяна Ивановна;010409;<br>
<?php
 	print_dean_box_end();
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importmanygroups.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
		 '<input type="hidden" name="withlogin" value="0" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="Загрузить список по 1-му образцу">'.
         '</form></br>';
    echo '</center><hr>';

    print_dean_box_start ('center', '50%', 'white');
    echo '<p><p><b>2-й образец файла импорта. Предназначен для регистрации LDAP-студентов.</b><p>';
?>

Фамилия;Имя;Группа;Логин<br>
Борзова;Ольга Петровна;100701;234244<br>
Былдина;Диана Николаевна;100701;1234123<br>
Вигерина;Ирина Владимировна;100701;1232131<br>
Головин;Игорь Константинович;100701;345344<br>
Голубинская;Татьяна Васильевна;100701;343<br>

<?php
    print_dean_box_end();
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importmanygroups.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
		 '<input type="hidden" name="withlogin" value="1" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="Загрузить список по 2-му образцу">'.
         '</form></br>';
    echo '</center><hr>';


    print_dean_box_start ('center', '50%', 'white');
    echo '<p><p><b>3-й образец файла импорта. Предназначен для регистрации студентов группы с указанием их логина и пароля.</b><p>';
?>

Фамилия;Имя;Группа;Логин;Пароль<br>
Уханев;Сергей Александрович;100601;08060126;347564<br>
Федоров;Игорь Викторович;100601;08060127;294751<br>
Шугаев;Алексей Юрьевич;100601;08060128;08060128<br>
Якимчев;Александар Дмитриевич;100602;08060129;618213<br>
Черевков;Дмитрий Владимирович;100602;080601118;342012<br>

<?php
    print_dean_box_end();
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importmanygroups.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
		 '<input type="hidden" name="withlogin" value="2" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="Загрузить список по 3-му образцу">'.
         '</form></br>';
    echo '</center><hr>';


    print_dean_box_start ('center', '50%', 'white');
    echo '<p><p><b>4-й образец файла импорта. Предназначен для регистрации LDAP3-студентов Алексеевского филиала.</b><p>';
?>

Фамилия;Имя;Группа;Логин<br>
Борзова;Ольга Петровна;100701;234244<br>
Былдина;Диана Николаевна;100701;1234123<br>
Вигерина;Ирина Владимировна;100701;1232131<br>
Головин;Игорь Константинович;100701;345344<br>
Голубинская;Татьяна Васильевна;100701;343<br>

<?php
    print_dean_box_end();
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importmanygroups.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
		 '<input type="hidden" name="withlogin" value="11" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="Загрузить список по 4-му образцу">'.
         '</form></br>';
    echo '</center><hr>';


print_dean_box_start ('center', '50%', 'white');
    echo '<p><p><b>5-й образец файла импорта. Предназначен для регистрации преподавателей БелГУ (LDAP2).</b><p>';
?>

Фамилия;Имя;Группа;Логин<br>
Иванов;Иван Иванович;DO_2011;ivanov<br>
Петров;Петр Петрович;DO_2011;petrov<br>
Сидоров;Сидор Сидорович;DO_2011;sidorov<br>

<?php
    print_dean_box_end();
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importmanygroups.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="mode" value="'.$mode.'" />'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
		 '<input type="hidden" name="withlogin" value="5" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="Загрузить список по 5-му образцу">'.
         '</form></br>';
    echo '</center><hr>';
	print_footer();



?>