<?php // $Id: journalschedule.php,v 1.2 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    $gid = required_param('gid', PARAM_INT);          // Group id
	$den = optional_param('d', 0, PARAM_INT);		// Denominator
	$term = optional_param('term', 1, PARAM_INT);		// # semestra
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
//		$teacher_is = isteacherinanycourse();

	    if (!$admin_is && !$creator_is && !$methodist_is) {
	        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
	    }

		// $faculty = get_record('dean_faculty', 'id', $fid);
		// $speciality = get_record('dean_speciality', 'id', $sid);

		// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
	    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_faculty("jouirnalschedule.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("jouirnalschedule.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("jouirnalschedule.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("jouirnalschedule.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
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

    $currenttabjournal ='journalschedule';
	include('tabsjournal.php');

	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/journal/journalschedule.php?gid=$gid&amp;d=$den".$strurl.'&amp;term=');


		$strnumerat = get_string('numerator', 'block_dean');
		$strdenomin = get_string('denominator', 'block_dean');

		// print tabs semestr
	    $toprow3[] = new tabobject($strnumerat, $CFG->wwwroot."/blocks/dean/journal/journalschedule.php?gid=$gid&amp;term=$term&amp;d=0$strurl", $strnumerat);
	    $toprow3[] = new tabobject($strdenomin, $CFG->wwwroot."/blocks/dean/journal/journalschedule.php?gid=$gid&amp;term=$term&amp;d=1$strurl", $strdenomin);
		if ($den == 1)   $currenttab = $strdenomin;
		else $currenttab = $strnumerat;
        $tabs3 = array($toprow3);

		// print_heading(get_string('terms','block_dean'), 'center', 4);
 		print_tabs($tabs3, $currenttab, NULL, NULL);

		$currcourse =  get_records_sql ("SELECT id, name, term
										 FROM  {$CFG->prefix}dean_discipline
										  WHERE curriculumid=$cid AND term=$term
										  ORDER BY courseid");
		if ($currcourse)	{
  			if ($prefs )	{
				// print_r($prefs);
		        // $rawquestions = $_POST;
				foreach ($prefs as $key => $value) {
					$sym = substr($key, 0, 1);
					if ($sym == 'z')   {
						$nums = explode ('_', $key);
						$numday = $nums[1];
						$numpair = $nums[2];
						$shedone = get_record_sql("SELECT *
										 FROM  {$CFG->prefix}dean_journal_shedule
 									 	 WHERE groupid=$gid AND term=$term AND denominator=$den AND numday=$numday AND numpair=$numpair");
						if ($shedone)	{
							if ($shedone->disciplineid != $value)	{
								$shedone->disciplineid = $value;
			           			if (!update_record('dean_journal_shedule', $shedone))	{
									error(get_string('errorinsaveshedule','block_dean').'(update)', "$CFG->wwwroot/blocks/dean/journal/journalschedule.php?gid=$gid&amp;d=$den&amp;term=$term{$strurl}");
								}
							}
 						} else {
							$rec->groupid = $gid;
							$rec->term = $term;
							$rec->denominator = $den;
							$rec->numday = $numday;
							$rec->numpair = $numpair;
							$rec->disciplineid = $value;
	             			if (!insert_record('dean_journal_shedule', $rec))	{
								error(get_string('errorinsaveshedule','block_dean').'(insert)', "$CFG->wwwroot/blocks/dean/journal/journalschedule.php?gid=$gid&amp;d=$den&amp;term=$term{$strurl}");
							}
						}
					} // if $sym
				}	// foreach
				add_to_log(1, 'dean', 'saveschedule_update', "/blocks/dean/journal/journalschedule.php?gid=$gid&amp;d=$den&amp;term=$term{$strurl}", $USER->lastname.' '.$USER->firstname);
				notice(get_string('saveschedule','block_dean'), "$CFG->wwwroot/blocks/dean/journal/journalschedule.php?gid=$gid&amp;d=$den&amp;term=$term{$strurl}");
			}  // if ($prefs )
			else {
				echo  '<table border="0" cellspacing="2" cellpadding="5" align="center" class="generalbox">';
				echo  '<tr>
					<th valign="top" nowrap="nowrap" class="header">'.get_string('dayweek', 'block_dean').'</th>
					<th valign="top" nowrap="nowrap" class="header">'.get_string('numpair', 'block_dean').'</th>
					<th valign="top" nowrap="nowrap" class="header">'.get_string('discipline', 'block_dean').'</th>
					</tr>';
														     //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				echo  '<form name="journalschedule" action="journalschedule.php" method="post">';
				echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
				echo  '<input type="hidden" name="d" value="'.$den.'" />';
				echo  '<input type="hidden" name="term" value="'.$term.'" />';
				echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';

				if ($strurl != '') 	{
					echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
					echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
					echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
				}

				$menudiscipline[0] = get_string('nopair', 'block_dean');
				foreach ($currcourse as $discipline) {
					$menudiscipline[$discipline->id] = $discipline->name;
				}

				for ($numday=1; $numday<=6; $numday++)	 {
					echo  '<tr><td align="center" class="generalboxcontent" rowspan=5>'.get_string('wd_'.$numday,'block_dean').'</td>';
					echo  '<td align="center" class="generalboxcontent">1</td>';
					echo  '<td align="left" class="generalboxcontent">';
					$shedone = get_record_sql("SELECT *
											   FROM  {$CFG->prefix}dean_journal_shedule
 									 	 	   WHERE groupid=$gid AND term=$term AND denominator=$den AND numday=$numday AND numpair=1");
					if ($shedone) $numdiscpln = $shedone->disciplineid;
					else $numdiscpln = 0;
					choose_from_menu($menudiscipline, 'z_'.$numday.'_1',  $numdiscpln, '');
					echo '</td>';
					for ($p=2; $p<=5; $p++) 	{
						echo  '<tr><td align="center" class="generalboxcontent">'.$p.'</td>';
						echo  '<td align="left" class="generalboxcontent">';
						$shedone = get_record_sql("SELECT *
											   FROM  {$CFG->prefix}dean_journal_shedule
 									 	 	   WHERE groupid=$gid AND term=$term AND denominator=$den AND numday=$numday AND numpair=$p");
						if ($shedone) $numdiscpln = $shedone->disciplineid;
						else $numdiscpln = 0;
						choose_from_menu($menudiscipline, 'z_'.$numday.'_'.$p,  $numdiscpln, '');

						echo '</td>';
					}
				}
				echo  '</table>';
				echo  '<div align="center"><input type="submit" value="'.get_string('savechanges').'" />';
				echo  '</form>';
			} // else
		}  	// if ($currcourse)	{
		else {
			notify (get_string('noablecreatesheduler', 'block_dean'));
		}


    print_footer();

?>


