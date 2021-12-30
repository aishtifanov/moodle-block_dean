<?php // $Id: roll2.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $did = required_param('did', PARAM_INT);          // Discipline id
    $rid = required_param('rid', PARAM_INT);          // Roll id
	$tabroll = optional_param('tabroll', 'zaschetexam', PARAM_ALPHA);
	$retake = optional_param('retake', 1, PARAM_INT);


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

    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strgroup";


    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
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

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
 	}

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("roll2.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;did=$did&amp;fid=", $fid);
    listbox_speciality("roll2.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;did=$did&amp;sid=", $fid, $sid);
    listbox_curriculum("roll2.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;cid=", $fid, $sid, $cid);
    listbox_group("roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;did=$did&amp;gid=", $fid, $sid, $cid, $gid);
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode >= 4)  {

	    $currenttab = 'roll';
   	    include('../gruppa/tabsonegroup.php');

	    $toprow = array();
  	    $toprow[] = new tabobject('zaschetexam', $CFG->wwwroot."/blocks/dean/rolls/rollofdiscipline.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=zaschetexam",
                get_string('zaschetexam', 'block_dean'));
   		$toprow[] = new tabobject('retake', $CFG->wwwroot."/blocks/dean/rolls/roll.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=retake",
       	        get_string('retake', 'block_dean'));
   		$toprow[] = new tabobject('listofrolls', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0",
       	        get_string('listofrolls', 'block_dean'));
        $tabs = array($toprow);
        print_tabs($tabs, $tabroll, NULL, NULL);

        if ($mode == 99)	{
        	// Взять двоечников
        	$prevretake = $retake - 1;
        	$nextretake = $retake + 1;
       	    $gotoretake = "SELECT * FROM {$CFG->prefix}dean_reroll
			                WHERE disciplineid = $did AND academygroupid = $gid AND retake=$retake AND (mark=2 OR mark>5)";
           	if ($studentss = get_records_sql($gotoretake)){
		      //	$tabledata2 = array(fullname($user2), $studentt->datoftake,
			//                           $studentt->datofgive, $studentt->mark);				foreach ($studentss as $studentt)  {			   	    $strsql = "SELECT * FROM {$CFG->prefix}dean_reroll
			                   WHERE disciplineid = $did AND academygroupid = $gid AND retake=$nextretake AND studentid = {$studentt->studentid}";

           		   if ($old_stud = get_record_sql($strsql))  {           		 	 notify(get_string('stdalreadyintable','block_dean'));
	      		   } else {
 	          		 $studentt->retake = $nextretake;
  	         		 $studentt->mark = 0;
             		 $add = insert_record('dean_reroll', $studentt);
             		 add_to_log(1, 'dean', 'one reroll added', "blocks/dean/groups/roll2.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
                   }
           		}		}

            $retake = $nextretake;
            $mode = 4;
        }



        $toproww = array();
        $toproww[] = new tabobject('retake1', $CFG->wwwroot."/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;rid=$rid&amp;tabroll=retake&amp;retake=1",
       	get_string('retake1', 'block_dean'));
       	$toproww[] = new tabobject('retake2', $CFG->wwwroot."/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;rid=$rid&amp;tabroll=retake&amp;retake=2",
       	get_string('retake2', 'block_dean'));
       	$toproww[] = new tabobject('retake3', $CFG->wwwroot."/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;rid=$rid&amp;tabroll=retake&amp;retake=3",
       	get_string('retake3', 'block_dean'));
       	$tabs = array($toproww);
        print_tabs($tabs, 'retake'.$retake, NULL, NULL);



        if (($frm = data_submitted()) && ($mode != 99))	{        //	print_r($frm);
        	//exit();
			// print_r ($frm);
				foreach ($frm as $key => $value) {
					// echo "$key => $value<br>";
					$sym = substr($key, 0, 1);
					if ($sym == 'z')   {
						$nums = explode ('_', $key);
						$rerollid = $nums[1];

						$reroll = get_record_sql("SELECT *
											      FROM  {$CFG->prefix}dean_reroll
											      WHERE id = {$rerollid}");

						if ($reroll)	{
							if ($reroll->mark != $value)	{
								$reroll->mark = $value;
								$reroll->retake = $retake;
			           			if (!update_record('dean_reroll', $reroll))	{
									error(get_string('errorinsavemark','block_dean').'(update)', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0");
								}
							}
						}


					} // if z


				} // foreach
				redirect($CFG->wwwroot."/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;rid=$rid&amp;tabroll=retake&amp;retake=$retake");
		} // if frm



		    $table->head  = array (get_string('fio', 'block_dean'), get_string('dateoftake', 'block_dean'), get_string('dateofgive', 'block_dean'), get_string('mark','block_dean'), get_string('edit','block_dean'));
			$table->align = array ("left", "center", "center", "center", "center");

	 	   // $roll =  get_record('dean_reroll', 'disciplineid', $did, 'academygroupid', $gid);

			for ($i=0; $i<=7; $i++)		{
				$menumark[$i] = get_string('o_'.$i, 'block_dean');
			}

			$strsql = "SELECT * FROM {$CFG->prefix}dean_reroll
			           WHERE disciplineid = $did AND academygroupid = $gid AND retake=$retake";



			if ($students = get_records_sql($strsql))  {
                $roll =  get_record('dean_rolls', 'disciplineid', $did, 'academygroupid', $gid);

		         foreach ($students as $student)	{				    $tabledata[0] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";					$user = get_record('user', 'id', $student->studentid);
                   //  $tabledata = array(fullname($user),convert_date($student->datoftake, 'en','ru'),
					//						convert_date($student->datofgive, 'en','ru'), $student->mark, $strlinkupdate);
     				//$tabledata[0] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";

		           // $allfacs = get_records_sql("SELECT * FROM {$CFG->prefix}dean_reroll ORDER BY disciplineid");
		            $title = get_string('printexcel','block_dean');
					$strlinkupdate = "<a title=\"$title\" href=\"retakeone.php?action=excel&amp;fid=$fid&amp;did=$did&amp;tabroll=retake&amp;retake=$retake&amp;rid=$rid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->studentid}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/f/excel.gif\" alt=\"$title\" /></a>&nbsp;";
					$title = get_string('deleteretake','block_dean');
				   // $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delretake.php?rid={$student->id}\">";
	 				//$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
					$dean_user = get_record('user', 'id', $student->id);
	  				$dname = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$dean_user->id.'&amp;course=1">'.fullname($dean_user).'</a></strong>';

					$tabledata = array(fullname($user),convert_date($student->datoftake, 'en','ru'),
											convert_date($student->datofgive, 'en','ru'), $student->mark, $strlinkupdate);

					if ($roll)	{
						// $markonestudent = get_record('dean_reroll', 'id', $roll->id, 'studentid', $student->id);
						if ($student->mark > 0)
						{
							$tabledata[3] = choose_from_menu($menumark, 'z_'.$student->id, $student->mark, '', '', '0', true);
						} else  {
							$tabledata[3] = choose_from_menu($menumark, 'z_'.$student->id, '0', '', '', '0', true);
						}


					} else {
						$tabledata[3] = choose_from_menu($menumark, 'z_'.$student->id, '0', '', '', '0', true);
					}
					$table->data[] = $tabledata;
			 	}
            }
				echo  '<form name="savereroll" method="post" action="roll2.php">';
                echo  '<input type="hidden" name="mode" value="'.$mode.'" />';
				echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
				echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
				echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
				echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
				echo  '<input type="hidden" name="did" value="'.$did.'" />';
				echo  '<input type="hidden" name="rid" value="'.$rid.'" />';
				echo  '<input type="hidden" name="retake" value="'.$retake.'" />';
				echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
				print_table($table, 1, true);
				echo  '<div align="center">';
			    echo  '<input type="submit" name="mark" value="';
			    print_string('savechanges');
			    echo '"></div></form><p><p><p><p>';
			} else	 {
				notify(get_string('studentsnotinreroll', 'block_dean'));
			}




 ?>
 <table align="center">
	<tr align="center">
	<?php if ($retake == 1)  { ?>
	<td align="center">
		<form name="addreroll" method="post" action="addreroll.php?mode=new">
		<input type="hidden" name="fid" value="<?php echo $fid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="cid" value="<?php echo $cid ?>" />
		<input type="hidden" name="gid" value="<?php echo $gid ?>" />
		<input type="hidden" name="did" value="<?php echo $did ?>" />
		<input type="hidden" name="rid" value="<?php echo $rid ?>" />
		<input type="hidden" name="retake" value="<?php echo $retake ?>" />
		<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />
		<input type="submit" name="addreroll" value="<?php print_string('addreroll','block_dean')?>"/>
		</form>
  	</td>
  	<?php }
    if ($retake < 3)  {
 ?>
	<td align="center">
		<form name="gotoretake" id = "gotoretake" method="post" action="roll2.php">
		<input type="hidden" name="mode" value="<?php echo '99' ?>" />
		<input type="hidden" name="fid" value="<?php echo $fid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="cid" value="<?php echo $cid ?>" />
		<input type="hidden" name="gid" value="<?php echo $gid ?>" />
		<input type="hidden" name="did" value="<?php echo $did ?>" />
		<input type="hidden" name="rid" value="<?php echo $rid ?>" />
		<input type="hidden" name="retake" value="<?php echo $retake ?>" />
		<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />
		<input type="submit" name="gotoretake" value="<?php
           if ($retake == 1){           print_string('gotoretake','block_dean');           }
           else{           print_string('gotoretake2','block_dean');           }
		   ?>"/>
	    </form>
  	</td>
  	 <?php } ?></tr>
	<tr align="center">
  	<td colspan=2 align="center">
		<form name="download" method="post" action="retake.php?action=excel">
			<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
     	</form>
	</td>
	</tr>
  </table>
 <?php
	print_simple_box_end();

    print_footer();


?>


