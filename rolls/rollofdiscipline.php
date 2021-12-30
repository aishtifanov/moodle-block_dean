<?php // $Id: rollofdiscipline.php,v 1.2 2009/09/02 12:08:18 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $did = required_param('did', PARAM_INT);          // Discipline id
    $rid = required_param('rid', PARAM_INT);          // Roll id
	$term = required_param('term', 1, PARAM_INT);	  // # semestra
	$tabroll = optional_param('tabroll', 'zaschetexam', PARAM_ALPHA);

    $frm = data_submitted(); /// load up any submitted data

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
	listbox_faculty("rollofdiscipline.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;did=$did&amp;fid=", $fid);
    listbox_speciality("rollofdiscipline.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;did=$did&amp;sid=", $fid, $sid);
    listbox_curriculum("rollofdiscipline.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=$did&amp;cid=", $fid, $sid, $cid);
    listbox_group("rollofdiscipline.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;did=$did&amp;gid=", $fid, $sid, $cid, $gid);
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode >= 4)  {

	    $currenttab = 'roll';
   	    include('../gruppa/tabsonegroup.php');

        $tabroll = 'zaschetexam';
   	    include('tabsroll.php');

		if ( $frm )	{

			if (find_form_rolls_errors($frm, $err) == 0) {
			    if ($rid == 0)  {
					$roll =  get_record('dean_rolls', 'disciplineid', $did, 'academygroupid', $gid);
				} else {
					$roll =  get_record('dean_rolls', 'id', $rid);
				}
				// print_r ($frm);  exit();
				if (!$roll) 	{
					//insert
					$rec->disciplineid = $did;
					$rec->academygroupid = $gid;
					$rec->teacherid = $frm->teacher;
					$rec->number = $frm->number;
					$rec->datecreated = make_timestamp ($frm->year, $frm->month, $frm->day);
					$rec->timemodified = time();

	        		if (!insert_record('dean_rolls', $rec))	{
	        			add_to_log(1, 'dean', 'one roll added', "blocks/dean/groups/rollofdiscipline.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
						error(get_string('errorinsaverolls','block_dean').'(insert)', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0");
					}

					$roll =  get_record('dean_rolls', 'disciplineid', $did, 'academygroupid', $gid);
   				} else {
     				//update
					$roll->teacherid = $frm->teacher;
					$roll->number = $frm->number;
					$roll->timemodified = time();
                    $roll->datecreated = make_timestamp ($frm->year, $frm->month, $frm->day);
	        		if (!update_record('dean_rolls', $roll))	{
						error(get_string('errorinsaverolls','block_dean').'(update)', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0");
					}
				}

				foreach ($frm as $key => $value) {
					// echo "$key => $value<br>";
					$sym = substr($key, 0, 1);
					if ($sym == 'z')   {
						$nums = explode ('_', $key);
						$userid = $nums[1];
						// $disid = $nums[2];
						// $numpair = $nums[3];
						$markone = get_record_sql("SELECT *
											 FROM  {$CFG->prefix}dean_roll_marks
	 									 	 WHERE rollid={$roll->id} AND studentid=$userid");

						if ($markone)	{
							if ($markone->mark != $value)	{
								$markone->mark = $value;
			           			if (!update_record('dean_roll_marks', $markone))	{
									error(get_string('errorinsavemark','block_dean').'(update)', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0");
								}
							}
						} else {
							$rec->rollid = $roll->id;
							$rec->studentid = $userid;
							$rec->mark = $value;
	             			if (!insert_record('dean_roll_marks', $rec))	{
								error(get_string('errorinsavemark','block_dean').'(insert)', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0");
							}
						}
					} // if z
				} // foreach
				redirect($CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0");
			}
		} // if frm



			$discipline = get_record('dean_discipline', 'id', $did);
			$agroup = get_record('dean_academygroups', 'id', $gid);

		    if (!$frm) {
  		        $dateofexamen = time();
  		        $teachersmenuselect = 0;
  		        $number1 = '';
			} else {
		        $dateofexamen =  make_timestamp($frm->year, $frm->month, $frm->day);
		        $teachersmenuselect = $frm->teacher;
   		        $number1 = $frm->number;
			}

            if ($rid !=0 )  {
				$roll =  get_record('dean_rolls', 'id', $rid);
		        $teachersmenuselect = $roll->teacherid;
		        $dateofexamen = $roll->datecreated;
		        $number1 = $roll->number;
		    } else {
			    $number1 = 0;
		    }


			if ($discipline->controltype == 'zaschet')  {
				$strheader = get_string('rollzaschet', 'block_dean');
			} else {
				$strheader = get_string('rollexamination', 'block_dean');
			}
			echo  '<form name="roll" action="rollofdiscipline.php" method="post">';
			$strheader .= '<input type="text" id="number" name="number" size="5" value="'.$number1.'" />';
			if (isset($err['number'])) {
   			     $strheader .= '<font color="#ff0000">&nbsp; '. $err['number'] .'</font>';
   			}
			print_heading($strheader, 'center', 4);

         	$teachersmenu = array();
			$teachersmenu[] = get_string('selectateacher', 'block_dean'). ' ...';

			$teachersql = get_records_sql ("SELECT DISTINCT userid FROM {$CFG->prefix}role_assignments
							WHERE roleid=3");

  	        foreach ($teachersql as $qq){
     	       $allteachers = get_records_sql("SELECT  id, lastname, firstname FROM {$CFG->prefix}user
									WHERE id={$qq->userid}");

    	    	foreach ($allteachers as $teacher) {
			     $teachersmenu[$teacher->id] = fullname($teacher);
				}
            }
   		    print_dean_box_start("center");
?>
			<tr valign="top">
			    <td align="right"><b><?php  print_string('ffaculty','block_dean') ?>:</b></td>
			    <td align="left"> <?php p($faculty->name) ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  print_string('sspeciality','block_dean') ?>:</b></td>
			    <td align="left"> <?php echo $speciality->number.'&nbsp;'.$speciality->name ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  print_string('formlearning','block_dean') ?>:</b></td>
			    <td align="left"> <?php print_string($curriculum->formlearning,'block_dean') ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  print_string('term','block_dean') ?>:</b></td>
			    <td align="left"> <?php p($term) ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  print_string('group') ?>:</b></td>
			    <td align="left"> <?php p($agroup->name) ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  print_string("discipline","block_dean") ?>:</b></td>
			    <td align="left"> <?php p($discipline->name) ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  print_string('dateofexamen','block_dean') ?>:</b></td>
			    <td align="left">  <?php print_date_selector("day", "month", "year", $dateofexamen); ?> </td>
			</tr>
			<tr valign="top">
			    <td align="right"><b><?php  echo $SITE->teacher; ?>:</b></td>
			    <td align="left">  <?php  choose_from_menu ($teachersmenu, 'teacher', $teachersmenuselect, "", "", "", false); ?>
								  <?php if (isset($err["teacher"])) formerr($err["teacher"]); ?>
			    </td>
			</tr>

<?php
		    print_dean_box_end();

		    $table->head  = array (get_string('fio', 'block_dean'), get_string('mark','block_dean'));
			$table->align = array ("center", "center");

			$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
								  u.lastaccess, m.academygroupid
			               FROM {$CFG->prefix}user u
						   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
			$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
			$studentsql .= 'ORDER BY u.lastname';

			$students = get_records_sql($studentsql);
			//print_r($students);
			if(!empty($students)) {

	 			$roll =  get_record('dean_rolls', 'disciplineid', $did, 'academygroupid', $gid);
				// print_r($roll);
				// echo '<br><br>';

				for ($i=0; $i<=7; $i++)		{
					$menumark[$i] = get_string('o_'.$i, 'block_dean');
				}

				foreach ($students as $student) {
						// $tabledata[0] = array (print_user_picture($student->id, 1, $student->picture, false, true);
					$tabledata[0] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
					if ($roll)	{
						$markonestudent = get_record('dean_roll_marks', 'rollid', $roll->id, 'studentid', $student->id);
						if ($markonestudent)	{
							$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id, $markonestudent->mark, '', '', '0', true);
						} else  {
							$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id, '0', '', '', '0', true);
						}

					} else {
						$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id, '0', '', '', '0', true);
					}
					//print_r($tabledata);
					$table->data[] = $tabledata;
					unset($tabledata);
				}

				echo  '<input type="hidden" name="mode" value="'.$mode.'" />';
				echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
				echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
				echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
				echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
				echo  '<input type="hidden" name="did" value="'.$did.'" />';
				echo  '<input type="hidden" name="rid" value="'.$rid.'" />';
				echo  '<input type="hidden" name="term" value="'.$term.'" />';
				echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';

				print_table($table, 0, true);

				echo  '<div align="center"><input type="submit" value="'.get_string('savechanges').'" />';
				echo  '</form>';
			} else	 {
				notify(get_string('errorstudentsnotinthisgroup', 'block_dean'));
			}
	}
 ?>
 <table align="center">
	<tr>
	<td>
  <form name="addfac" method="post" action="roll2.php">
		<input type="hidden" name="mode" value="<?php echo '99' ?>" />
		<input type="hidden" name="fid" value="<?php echo $fid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="cid" value="<?php echo $cid ?>" />
		<input type="hidden" name="gid" value="<?php echo $gid ?>" />
		<input type="hidden" name="did" value="<?php echo $did ?>" />
		<input type="hidden" name="rid" value="<?php echo $rid ?>" />
		<input type="hidden" name="tabroll" value="retake" />
		<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />

	    <div align="center">
		<input type="submit" name="addfaculty" value="<?php print_string('openretakes','block_dean')?>">
		 </div>
  </form>
  	</td>
	<td>
     		<form name="download" method="post" action="to_excel.php?action=excel">

		<input type="hidden" name="fid" value="<?php echo $fid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="cid" value="<?php echo $cid ?>" />
		<input type="hidden" name="gid" value="<?php echo $gid ?>" />
		<input type="hidden" name="did" value="<?php echo $did ?>" />
		<input type="hidden" name="rid" value="<?php echo $rid ?>" />
		<input type="hidden" name="term" value="<?php echo $term ?>" />
		<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />
		<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">

     	</form>
	</td>
	</tr>
  </table>



 <?php
    print_footer();

/// FUNCTIONS ////////////////////
function find_form_rolls_errors(&$frm, &$err) {

    if (empty($frm->number) || $frm->number == 0)  {
        $err["number"] = get_string("missingname");
	}

    if (empty($frm->teacher)) {
        $err["teacher"] = get_string("missingname");
	}

    return count($err);
}

?>

