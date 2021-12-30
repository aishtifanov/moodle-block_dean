<?php // $Id: zachbooks.php,v 1.2 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    require_once('../lib_dean.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		// Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = required_param('uid', PARAM_INT);           //user id
   // $rid = required_param('rid', PARAM_INT);
 //   $did = required_param('did', PARAM_INT);          // Discipline id
	$term = optional_param('term', 1, PARAM_INT);		// # semestra


    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string('speciality','block_dean');
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
    $strrolls = get_string("rolls","block_dean");
    $strstudent = get_string('student','block_dean');

    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/rolls/rolls.php?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroup</a>";
	$breadcrumbs .= " -> $strstudent";

    print_header("$site->shortname: $strstudent", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$teacher_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
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

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("zachbooks.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;uid=$uid&amp;fid=", $fid);
    listbox_speciality("zachbooks.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;uid=$uid&amp;sid=", $fid, $sid);
    listbox_curriculum("zachbooks.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;uid=$uid&amp;cid=", $fid, $sid, $cid);
    listbox_group_pegas("zachbooks.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;uid=$uid&amp;gid=", $fid, $sid, $cid, $gid);
    listbox_student("zachbooks.php?mode=5&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;uid=", $fid, $sid, $cid, $gid, $uid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $uid != 0 && $mode >= 5)  {

   	    $currenttab = 'zachbook';
    	$toprow = array();
        $toprow[] = new tabobject('profile', $CFG->wwwroot."/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid=$uid",
                get_string('profile'));

        $toprow[] = new tabobject('registrationcard', $CFG->wwwroot."/blocks/dean/student/registrationcard.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid=$uid",
    	            // get_string('registrationcard', 'block_dean'));
					get_string('editmyprofile'));

		$toprow[] = new tabobject('studycard', $CFG->wwwroot."/blocks/dean/student/studycard.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid=$uid",
    	            get_string('studycard', 'block_dean'));

		$toprow[] = new tabobject('zachbook', $CFG->wwwroot."/blocks/dean/student/zachbooks.php?mode=5&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;uid=$uid",
    	            get_string('zachbook', 'block_dean'));

    	$tabs = array($toprow);
    	print_tabs($tabs, $currenttab,NULL,NULL);

	   	print_tabs_semestr($term, "zachbooks.php?mode=5&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;uid=$uid&amp;term=");

        echo "<table width=\"80%\" align=\"center\" border=\"0\" cellspacing=\"0\" class=\"userinfobox\">";
	    echo "<tr>";
	    echo "<td width=\"100\" valign=\"top\" class=\"side\">";
    	//print_user_picture($user->id, $course->id, $user->picture, true, false, false);
	    echo "</td><td width=\"100%\" class=\"content\">";
        echo '<table border="0" cellpadding="0" cellspacing="0" class="list">';
        print_dean_box_start("center");

        ?>
        			<tr valign="top">
			    <td align="center"><b><?php  print_string('teoretic','block_dean') ?>:</b></td>
			</tr>
        <?
        print_dean_box_end();
		//print_header('teoretic','block_dean');

	    $table->head  = array ('№', get_string('disciplinename', 'block_dean'), get_string('hoursnumber', 'block_dean'), $SITE->teacher,
	    						get_string('exmark','block_dean'),get_string('dateofgive','block_dean'));
	    $table->align = array ('left', 'left', 'center', 'left', 'center', 'center');



		$currcourse =  get_records_sql ("SELECT *
		 							    FROM  {$CFG->prefix}dean_discipline
									    WHERE curriculumid=$cid AND term=$term AND controltype='examination'
									    ORDER BY courseid");

		$notis =  get_records_sql ("SELECT *
		 							    FROM  {$CFG->prefix}dean_discipline
									    WHERE curriculumid=$cid AND term=$term AND controltype='notis'
									    ORDER BY courseid");

		$i = 0;
		if ($currcourse)
		foreach ($currcourse as $discipline) {

            $strdiscipline = $discipline->name;

			if (!$roll =  get_record('dean_rolls', 'disciplineid', $discipline->id, 'academygroupid', $gid)) continue;
          // $studroll = get_record('dean_roll_marks','studentid', $uid, 'rollid', $rid);

          $markone = get_record_sql("SELECT * FROM  {$CFG->prefix}dean_roll_marks
	 									 	 WHERE rollid={$roll->id} AND studentid=$uid");

           $hours = get_record('dean_discipline', 'id', $discipline->id);
           $allhours = ($hours->auditoriumhours) + ($hours->selfinstructionhours);//всего часов


			if ($roll) 	{
 				//$str1 = get_string('fill'.$discipline->controltype, 'block_dean');
 				$rid = $roll->id;
 				$teacher = get_record('user', 'id',  $roll->teacherid);
 				$teachername = fullname($teacher);
 				$printdate = get_rus_format_date($roll->datecreated, 'full');
 				//$numroll = $roll->number;
			}
			else 	{
 				//$str1 = get_string('create'.$discipline->controltype, 'block_dean');
 				$rid = 0;
 				$teachername = '-';
 				$printdate = '-';
 				//$numroll = '-';
			}

			$i++;
			$table->data[] = array ($i,  $strdiscipline,$allhours, $teachername, $markone->mark, $printdate);

  		}


		print_table($table);

        print_dean_box_start("center");
        ?>
        			<tr valign="top">
			    <td align="center"><b><?php  print_string('practic','block_dean') ?>:</b></td>
			</tr>
        <?
        print_dean_box_end();
        //print_heading('practic','block_dean');

  	 $tablee->head  = array ('№', get_string('disciplinename', 'block_dean'), get_string('hoursnumber', 'block_dean'), $SITE->teacher,
	    						get_string('zachmark','block_dean'),get_string('dateofgive','block_dean'));
	 $tablee->align = array ('left', 'left', 'center', 'left', 'center', 'center');



	 $currzach =  get_records_sql ("SELECT *
		 							    FROM  {$CFG->prefix}dean_discipline
									    WHERE curriculumid=$cid AND term=$term AND controltype='zaschet'
									    ORDER BY courseid");


	 $j = 0;
	 if ($currzach)
	  foreach ($currzach as $discip) {

 	   $strdiscip = $discip->name;

			if (!$rol =  get_record('dean_rolls', 'disciplineid', $discip->id, 'academygroupid', $gid)) continue;
          // $studroll = get_record('dean_roll_marks','studentid', $uid, 'rollid', $rid);

          $mark = get_record_sql("SELECT * FROM  {$CFG->prefix}dean_roll_marks
	 									 	 WHERE rollid={$rol->id} AND studentid=$uid");

           $hour = get_record('dean_discipline', 'id', $discip->id);
           $allhour = ($hour->auditoriumhours) + ($hour->selfinstructionhours);//всего часов


			if ($rol) 	{
 				//$str1 = get_string('fill'.$discipline->controltype, 'block_dean');
 				$rid = $rol->id;
 				$teacher = get_record('user', 'id',  $rol->teacherid);
 				$teachername = fullname($teacher);
 				$printdate = get_rus_format_date($rol->datecreated, 'full');
 				//$numroll = $roll->number;
			}
			else 	{
 				//$str1 = get_string('create'.$discipline->controltype, 'block_dean');
 				$rid = 0;
 				$teachername = '-';
 				$printdate = '-';
 				//$numroll = '-';
			}

			$j++;
			$tablee->data[] = array ($j,  $strdiscip,$allhour, $teachername, $mark->mark, $printdate);

	  }

     print_table($tablee);

        print_dean_box_start("center");
        ?>
        			<tr valign="top">
			    <td align="center"><b><?php  print_string('notidentify','block_dean') ?>:</b></td>
			</tr>
        <?
          print_dean_box_end();

     	$tab->head  = array ('№', get_string('disciplinename', 'block_dean'), get_string('hoursnumber', 'block_dean'), $SITE->teacher,
	    						get_string('notismark','block_dean'),get_string('dateofgive','block_dean'));
	    $tab->align = array ('left', 'left', 'center', 'left', 'center', 'center');

   	    $k = 0;
     	if ($notis){

           foreach ($notis as $not) {

 	         $strnot = $not->name;

			if (!$rols =  get_record('dean_rolls', 'disciplineid', $not->id, 'academygroupid', $gid)) continue;
             // $studroll = get_record('dean_roll_marks','studentid', $uid, 'rollid', $rid);

           	 $marks = get_record_sql("SELECT * FROM  {$CFG->prefix}dean_roll_marks
	 									 	 WHERE rollid={$rols->id} AND studentid=$uid");

          	 $hourss = get_record('dean_discipline', 'id', $not->id);
          	 $allhourss = ($hourss->auditoriumhours) + ($hourss->selfinstructionhours);//всего часов


				if ($rols) 	{
 					//$str1 = get_string('fill'.$discipline->controltype, 'block_dean');
 					$rid = $rol->id;
 					$teacher = get_record('user', 'id',  $rols->teacherid);
 					$teachername = fullname($teacher);
 					$printdate = get_rus_format_date($rols->datecreated, 'full');
 					//$numroll = $roll->number;
				}
				else 	{
 					//$str1 = get_string('create'.$discipline->controltype, 'block_dean');
 					$rid = 0;
 					$teachername = '-';
 					$printdate = '-';
 					//$numroll = '-';
				}

			$k++;
			$tab->data[] = array ($k,  $strnot,$allhourss, $teachername, $marks->mark, $printdate);

	       }
           print_table($tab);

     	}
            echo "</table>";
                echo "</td></tr></table>";
     }
     //print_header_simple('teoretic','block_dean');
    print_footer();

?>

