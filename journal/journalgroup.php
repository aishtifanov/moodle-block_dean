<?php // $Id: journalgroup.php,v 1.4 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

	$gid = optional_param('gid', 0, PARAM_INT);
	$term = optional_param('term', 1, PARAM_INT);		// # semestra
    $fid = optional_param('fid', 0, PARAM_INT);      // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);      // Speciality id
    $cid = optional_param('cid', 0, PARAM_INT);		// Curriculum id

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
		$teacher_is = isteacherinanycourse();
  	    $methodist_is = ismethodist();

	    if (!$admin_is && !$creator_is && !$methodist_is) {
	        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
	    }

		// $faculty = get_record('dean_faculty', 'id', $fid);
		// $speciality = get_record('dean_speciality', 'id', $sid);

		// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
	    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_faculty("journalgroup.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("journalgroup.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("journalgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("journalgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid);
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
  	    $methodist_is = ismethodist();

 	   if ($admin_is || $creator_is || $methodist_is) {
	    	listbox_all_group('journalgroup.php?gid=', $gid);
			if ($gid == 0) exit();
	   }

		$curator_is = iscurator();

 	   if (!$curator_is && !$admin_is  && !$creator_is && !$methodist_is) {
  	      error(get_string('curatoraccess', 'block_dean'), '..\index.php');
   	   }

	    if (!$admin_is && !$creator_is && !$methodist_is) {
			if ($manygroup=get_records('dean_curators', 'userid', $USER->id))	 {
				if (count($manygroup) > 1)	{
					$groupmenu = array();
					$groupmenu[0] = get_string('selectagroup', 'block_dean') . ' ...';
					foreach ($manygroup as $mgr) {
						$gr = get_record('dean_academygroups', 'id', $mgr->academygroupid);
						$groupmenu[$gr->id] =$gr->name;
					}
					echo '<div align=center>'.$strgroup.':';
					popup_form('journalgroup.php?gid=', $groupmenu, 'switchgroup', $gid, '', '', '', false);
					echo '</div><br><br>';
					if ($gid == 0) exit();
				} else {
					$curator = get_record('dean_curators', 'userid', $USER->id);
			  		$gid = 	$curator->academygroupid;
				}
			}
		}

		print_heading($strjornalgroup.' '.$academygroup->name, 'center', 2);

		$strurl = '';
	}

    $currenttabjournal ='pagesjournal';
	include('tabsjournal.php');

	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/journal/journalgroup.php?gid=$gid".$strurl.'&amp;term=');

	$table->head  = array (get_string('numweek', 'block_dean'),
						   get_string('datestartweek','block_dean'),
						   get_string('numer_denom', 'block_dean'),
						   // get_string('pagesjournal', 'block_dean'),
						   get_string('action', 'block_dean'));
	$table->align = array ('left', 'left', 'center', 'left'); // , 'left');

	//$strnumerat = get_string('numerator', 'block_dean');
	//$strdenomin = get_string('denominator', 'block_dean');
	$menudenominator[0] = get_string('numerator', 'block_dean');
	$menudenominator[1] = get_string('denominator', 'block_dean');

	$curriculum = get_record('dean_curriculum', 'id', $academygroup->curriculumid);
	$cid = $curriculum->id;

	$journal_setting = get_record('dean_journal_startdate', 'groupid', $gid, 'term', $term);
	if ($journal_setting)	{
		$allweek = $journal_setting->duration;
		$startdate = $journal_setting->datestart;
		// echo 	"1. $startdate <br>";
	} else {
		$allweek = 18;
		notice(get_string('errornotsetting', 'block_dean'), $CFG->wwwroot."/blocks/dean/journal/journalsetting.php?gid=$gid");
		// redirect($CFG->wwwroot."/blocks/dean/journal/journalsetting.php?gid=$gid");
	}

	for ($numweek=1; $numweek<=$allweek; $numweek++)	 {
			if ($numweek == 1) 	{
				if ($journal_setting)	{
					$numdayinweek = date('w', $startdate);
					if ($numdayinweek == 0) 	{
						$startdate = $journal_setting->datestart + DAYSECS;
					}
					$cz = $journal_setting->denominator;
					$datestart = $startdate;
				} else {
					$datestart = get_startdateweek($curriculum->enrolyear, $term, $numweek, false);
					$cz = 1;
				}
			} else 	{
				if ($journal_setting)	{
					$datestart  = $startdate + (($numweek-1)*7*DAYSECS);
					if ($numweek%2 == 0) $cz = 1 - $journal_setting->denominator;
					else $cz = $journal_setting->denominator;
				} else {
					$datestart = get_startdateweek($curriculum->enrolyear, $term, $numweek, false);
					if ($numweek%2 == 0) $cz = 0;
					else $cz = 1;
				}
			}
			$monthstring = get_string('lm_'.date('n',$datestart),'block_dean');
	 	    $strstartdate =  str_replace(' 0', '', gmstrftime(' %d', $datestart))." ".$monthstring." ".date('Y', $datestart);

			$jpage =  get_record('dean_journal_pages', 'groupid', $gid, 'term', $term, 'numweek', $numweek);
			if ($jpage) 	{
			    $strpageis = $jpage->name;
				$strlinkupdate = "<a href=\"journalpage.php?gid=$gid&amp;term=$term&amp;nw=$numweek&amp;ds=$datestart&amp;d=$cz{$strurl}\">";
 				$strlinkupdate = $strlinkupdate . get_string('fillpage', 'block_dean'). "</a>";
			}
			else 	{
				$strpageis = get_string('pageabsence', 'block_dean');
				$strlinkupdate = "<a href=\"journalpage.php?gid=$gid&amp;term=$term&amp;nw=$numweek&amp;ds=$datestart&amp;d=$cz{$strurl}\">";
				$strlinkupdate = $strlinkupdate . get_string('createpage', 'block_dean'). "</a>";
			}
			// $table->data[] = array ($numweek, $startdate, $menudenominator[$cz], $strpageis, $strlinkupdate);
			$table->data[] = array ($numweek, $strstartdate, $menudenominator[$cz], $strlinkupdate);

			if ($numweek == 1) 	{
				if ($journal_setting && $numdayinweek > 1) 	{
					$startdate = $startdate - ($numdayinweek-1)*DAYSECS;
				}
			}
	}

	print_table($table);

    print_footer();

?>


