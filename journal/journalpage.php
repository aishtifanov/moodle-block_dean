<?php // $Id: journalpage.php,v 1.3 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
   // require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    $gid = required_param('gid', PARAM_INT);          // Group id
	$term = required_param('term', PARAM_INT);		// # semestra
	$numweek = required_param('nw', PARAM_INT);		// # week
	$datestart = required_param('ds', PARAM_INT);		// date start
	$den = required_param('d', PARAM_INT);	// Denominator
	$dayinweek	= optional_param('dw', 0, PARAM_INT);
    $fid = optional_param('fid', 0, PARAM_INT);      // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);      // Speciality id
    $cid = optional_param('cid', 0, PARAM_INT);		// Curriculum id

	$prefs = data_submitted();

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
	$strjornalgroup = get_string('journalgroup','block_dean');

	$academygroup = get_record('dean_academygroups', 'id', $gid);

	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0)  {

		$strfaculty = get_string('faculty','block_dean');
	    $strspeciality = get_string('speciality','block_dean');
		$strcurriculums = get_string('curriculums','block_dean');

	    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
		$breadcrumbs .= " -> $strjornalgroup";

	    print_header("$site->shortname: $strjornalgroup", "$site->fullname", $breadcrumbs);

		$admin_is = isadmin();
		$creator_is = iscreator();
  	    $methodist_is = ismethodist();
		// $teacher_is = isteacherinanycourse();

	    if (!$admin_is && !$creator_is && !$methodist_is) {
	        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
	    }

		// $faculty = get_record('dean_faculty', 'id', $fid);
		// $speciality = get_record('dean_speciality', 'id', $sid);

		// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
	    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_faculty("journalpage.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("journalpage.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("journalpage.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("journalpage.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
		echo '</table>';

	    $currenttab = 'juornalgroup';
	    include('../gruppa/tabsonegroup.php');

	    $strurl = "&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid";

	} else  {

	    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
		$breadcrumbs .= " -> $strjornalgroup";

	    print_header("$site->shortname: $strjornalgroup", "$site->fullname", $breadcrumbs);

		$admin_is = isadmin();
		$creator_is = iscreator();
		$curator_is = iscurator();
  	    $methodist_is = ismethodist();

	    if (!$curator_is && !$admin_is  && !$creator_is && !$methodist_is) {
	        error(get_string('curatoraccess', 'block_dean'), '..\faculty\faculty.php');
	    }

	    if ($curator_is) {
   			$curator = get_record('dean_curators', 'userid', $USER->id, 'academygroupid', $gid);
			$gid = 	$curator->academygroupid;
		}

		print_heading($strjornalgroup.' '.$academygroup->name, 'center', 2);

		$strurl = '';
	}


	$curriculum = get_record('dean_curriculum', 'id', $academygroup->curriculumid);
	$cid = $curriculum->id;

    $currenttabjournal ='pagesjournal';
	include('tabsjournal.php');

	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/journal/journalgroup.php?gid=$gid".$strurl.'&amp;term=');

		$jrnl_setting = get_record('dean_journal_startdate', 'groupid', $gid, 'term', $term);

		if ($jrnl_setting)	{
			$totalweeks = $jrnl_setting->duration;
			$startdate = $jrnl_setting->datestart;
			$denominator = $jrnl_setting->denominator;
		} else {
			$totalweeks = 18;
			$curriculum = get_record('dean_curriculum', 'id', $cid);
			$startdate = get_startdateweek($curriculum->enrolyear, $term, $numweek, false);
			$denominator = 1;
		}


		print_tabs_weeks($numweek, $totalweeks, $startdate, $denominator, $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;dw=$dayinweek&amp;gid=$gid".$strurl.'&amp;nw=');

		print_tabs_dayinweek($numweek, $dayinweek, $datestart, $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;ds=$datestart;&amp;d=$den&amp;gid=$gid&amp;nw=$numweek".$strurl.'&amp;dw=');

		if ( $prefs )	{
			$jpage =  get_record('dean_journal_pages', 'groupid', $gid, 'term', $term, 'numweek', $numweek);
			if (!$jpage) 	{
				$rec->groupid = $gid;
				$rec->term = $term;
				$rec->numweek = $numweek;
				$rec->datestart = $datestart;
				$rec->denominator = $denominator;
				$rec->name = '';
/*
						$monthstring = get_string('lm_'.date('n',$datestart), 'block_dean');
						$datestring_ =  str_replace(' 0', '', gmstrftime(' %d', $datestart))." ".$monthstring." ".date('Y', $datestart);
						$rec->datestartstr = $datestring_;
*/
        		if (!insert_record('dean_journal_pages', $rec))	{
					error(get_string('errorinsavepages','block_dean').'(insert)', $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;dw=$dayinweek&amp;gid=$gid&amp;nw=$numweek");
				}
				$jpage =  get_record('dean_journal_pages', 'groupid', $gid, 'term', $term, 'numweek', $numweek);
			}
			foreach ($prefs as $key => $value) {
				// echo "$key => $value<br>";
				$sym = substr($key, 0, 1);
				if ($sym == 'z')   {
					$nums = explode ('_', $key);
					$userid = $nums[1];
					$disid = $nums[2];
					$numpair = $nums[3];
					$markone = get_record_sql("SELECT *
										 FROM  {$CFG->prefix}dean_journal_marks
 									 	 WHERE journalpageid={$jpage->id} AND numday=$dayinweek AND numpair=$numpair AND disciplineid=$disid AND userid=$userid");

					if ($markone)	{
						if ($markone->mark != $value)	{
							$markone->mark = $value;
		           			if (!update_record('dean_journal_marks', $markone))	{
								error(get_string('errorinsavemark','block_dean').'(update)', $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;dw=$dayinweek&amp;gid=$gid&amp;nw=$numweek$strurl");
							}
						}
					} else {
						$rec->journalpageid = $jpage->id;
						$rec->numday = $dayinweek;
						$rec->numpair = $numpair;
						$rec->disciplineid = $disid;
						$rec->userid = $userid;
						$rec->mark = $value;
						if ($jpage->numweek == 1)	{
							$numdayinweek_ = date('w',$jpage->datestart);
							$rec->dateday = $jpage->datestart + (($dayinweek-$numdayinweek_)*DAYSECS);
						} else 	{
							$rec->dateday = $jpage->datestart + (($dayinweek-1)*DAYSECS);
						}
/*
						$monthstring = get_string('lm_'.date('n',$rec->dateday), 'block_dean');
						$datestring_ =  str_replace(' 0', '', gmstrftime(' %d', $rec->dateday))." ".$monthstring." ".date('Y', $rec->dateday);
						$rec->datedaystr = $datestring_;
*/
             			if (!insert_record('dean_journal_marks', $rec))	{
							error(get_string('errorinsavemark','block_dean').'(insert)', $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;dw=$dayinweek&amp;gid=$gid&amp;nw=$numweek$strurl");
						}
					}
				} // if z
			} // foreach

			// add_to_log(1, 'dean', 'savepages_update', $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;dw=$dayinweek&amp;gid=$gid&amp;nw=$numweek", $USER->lastname.' '.$USER->firstname);
			notice(get_string('savedayinweek','block_dean'), $CFG->wwwroot."/blocks/dean/journal/journalpage.php?term=$term&amp;dw=$dayinweek&amp;gid=$gid&amp;nw=$numweek{$strurl}");
		}  else	{
			$shedoneday = get_records_sql("SELECT * FROM  {$CFG->prefix}dean_journal_shedule
									   WHERE groupid=$gid AND term=$term AND denominator=$den AND numday=$dayinweek");
			//print_r($shedoneday);
			if ($shedoneday)	{
				$table->head[0] = get_string('fio', 'block_dean');
				$table->align[0] = 'left';
				foreach ($shedoneday as $son)	{
					if ($son->disciplineid != 0)	{
						$discipline = get_record('dean_discipline', 'id', $son->disciplineid);
						$table->head[] = $discipline->name;
						$table->align[] = 'center';
					}
				}
				$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
									  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
									  u.lastaccess, m.academygroupid
									FROM {$CFG->prefix}user u
							   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
				$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
				$studentsql .= 'ORDER BY u.lastname';

				 // print_r($studentsql);
				$students = get_records_sql($studentsql);
				if(!empty($students)) {

					$jpage =  get_record('dean_journal_pages', 'groupid', $gid, 'term', $term, 'numweek', $numweek);
					// print_r($jpage);
					// echo '<br><br>';

					for ($i=0; $i<=8; $i++)		{
						$menumark[$i] = get_string('m_'.$i, 'block_dean');
					}
					$marksarray = array();

					foreach ($students as $student) {
						// $tabledata[0] = array (print_user_picture($student->id, 1, $student->picture, false, true);
						if ($jpage)	{
							$markonestudent = get_records_sql("SELECT *
							    	FROM  {$CFG->prefix}dean_journal_marks
									WHERE  journalpageid = {$jpage->id} AND numday=$dayinweek AND userid=$student->id
									ORDER BY numpair");
							if ($markonestudent)	{
								foreach($markonestudent as $mos)	{
									$marksarray[] = $mos->mark;
								}
							}
							// print_r($markonestudent);
							// print_r($marksarray);
							// echo '<br><br>';
						}

						$tabledata[0] = '<div align=left><strong>'.fullname($student).'</strong></div>';
						$numpair = 1;
						foreach ($shedoneday as $son)	{
							if ($son->disciplineid != 0)	{
								if ($markonestudent)	{
									$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id.'_'.$son->disciplineid.'_'.$numpair, $marksarray[$numpair-1], '', '', '0', true);
								} else {
									$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id.'_'.$son->disciplineid.'_'.$numpair, '0', '', '', '0', true);
								}
								$numpair++;
							}
						}
						$table->data[] = $tabledata;
						unset($tabledata);
						unset($marksarray);

					}

					echo  '<form name="journalpage" action="journalpage.php" method="post">';
					echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
					echo  '<input type="hidden" name="term" value="'.$term.'" />';
					echo  '<input type="hidden" name="nw" value="'.$numweek.'" />';
					echo  '<input type="hidden" name="ds" value="'.$datestart.'" />';
					echo  '<input type="hidden" name="d" value="'.$den.'" />';
					echo  '<input type="hidden" name="dw" value="'.$dayinweek.'" />';
					echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
					if ($strurl != '') 	{
						echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
						echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
						echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
						echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
					}

					print_table($table, 1, true);

					echo  '<div align="center"><input type="submit" value="'.get_string('savechanges').'" />';
					echo  '</form>';
				} else	 {
					notify(get_string('errorstudentsnotinthisgroup', 'block_dean'));
				}
			} else {
				if ($dayinweek == 0)	{
					notify(get_string('choosedayinweek', 'block_dean'));
				} else {
					$menudenominator[0] = get_string('numeratora', 'block_dean');
					$menudenominator[1] = get_string('denominatora', 'block_dean');
					error(get_string('errornotschedule', 'block_dean', $menudenominator[$den]), $CFG->wwwroot."/blocks/dean/journal/journalschedule.php?gid=$gid&amp;d=$den{$strurl}");
				}
			}
		}


    print_footer();

?>


