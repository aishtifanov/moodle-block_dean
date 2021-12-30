<?php // $Id: journalsetting.php,v 1.3 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');    

    $gid = required_param('gid', PARAM_INT);          // Group id
	$vkladka = optional_param('d', 1, PARAM_INT);		// vkladka start dates
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
		listbox_faculty("journalsetting.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("journalsetting.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("journalsetting.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("journalsetting.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
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

    $currenttabjournal ='journalsetting';
	include('tabsjournal.php');


	$strshortname = get_string('shortnamejournal', 'block_dean');
	$strstartdate = get_string('startdatejournal', 'block_dean');

		// print tabs settings
    $toprow3[] = new tabobject($strstartdate, $CFG->wwwroot."/blocks/dean/journal/journalsetting.php?gid=$gid&amp;term=$term&amp;d=1$strurl", $strstartdate);
    $toprow3[] = new tabobject($strshortname, $CFG->wwwroot."/blocks/dean/journal/journalsetting.php?gid=$gid&amp;term=$term&amp;d=0$strurl", $strshortname);
	if ($vkladka == 1)   $currenttab = $strstartdate;
	else $currenttab = $strshortname;

    $tabs3 = array($toprow3);

 	// print_heading(get_string('terms','block_dean'), 'center', 4);
	print_tabs($tabs3, $currenttab, NULL, NULL);

	if ($vkladka == 0) 	{ // work with short discipline name

		print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka".$strurl.'&amp;term=');

		$currcourse =  get_records_sql ("SELECT *
									  FROM  {$CFG->prefix}dean_discipline
									  WHERE curriculumid=$cid AND term=$term
									  ORDER BY courseid");
			if ($currcourse)	{
				// save short discipline name
				if ($prefs )	{
					// print_r($prefs);
		            // $rawquestions = $_POST;
					foreach ($prefs as $key => $value) {
						if (is_numeric ($key)) {
							$db_set = get_record('dean_journal_shortdisname', 'groupid', $gid, 'term', $term, 'disciplineid', $key);
					        if ( $db_set )  {
								if ($db_set->shortdisname != $value)  	{
									$db_set->shortdisname  = $value;
			             			if (!update_record('dean_journal_shortdisname', $db_set))	{
										error(get_string('errorinsavesettings','block_dean').'(update)', "$CFG->wwwroot/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka&amp;term=$term$strurl");
									}
								}
					        } else {
								$rec->groupid = $gid;
								$rec->term = $term;
								$rec->disciplineid = $key;
								$rec->shortdisname = $value;
								// print_r($rec);
	             				if (!insert_record('dean_journal_shortdisname', $rec))	{
									error(get_string('errorinsavesettings','block_dean').'(insert)', "$CFG->wwwroot/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka&amp;term=$term$strurl");
								}
							}
						}
			        }
				    add_to_log(1, 'dean', 'shortdisname_update', "/blocks/dean/journal/journalsetting.php?gid=$gid$strurl", $USER->lastname.' '.$USER->firstname);
					notice(get_string('savesettings','block_dean'), "$CFG->wwwroot/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka&amp;term=$term$strurl");

				} else 	{  // show short discipline name
				    $table->head  = array ('#', get_string('disciplinename', 'block_dean'),
											  get_string('abbreviation', 'block_dean'));
		 			$table->align = array ('left', 'left', 'left');

					echo  '<form name="datesetting" action="journalsetting.php" method="post">';
					echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
					echo  '<input type="hidden" name="d" value="'.$vkladka.'" />';
					echo  '<input type="hidden" name="term" value="'.$term.'" />';
					echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
					if ($strurl != '') 	{
						echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
						echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
						echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
					}

					$i = 1;
					foreach ($currcourse as $discipline) {
						$jshortdisname = get_record('dean_journal_shortdisname', 'groupid', $gid, 'term', $term, 'disciplineid', $discipline->id);
						if ($jshortdisname)	{
							$strshortdisname = $jshortdisname->shortdisname;
						} else {
							// $strshortdisname = abbreviation ($discipline->name);
                            $strshortdisname = '';
						}
						$strinput = '<input type="text" size="5" maxlength="5" name="'.$discipline->id.'" value="'.$strshortdisname .'" />';
						$table->data[] = array ($i, $discipline->name, $strinput);
						$i++;
					}

					print_table($table);
					echo  '<div align="center"><input type="submit" value="'.get_string('savechanges').'" />';
					echo  '</form>';
				}
			}

		}	else  { // $vkladka = 1 // work with short discipline name
			// save start date
		    if ($prefs )	{
				// print_r($prefs);
		        // $rawquestions = $_POST;
				$settings = array();
        		foreach ($prefs as $key => $value) {    // Parse input for question ids
					$sym = substr($key, 0, 1);
					$term = substr($key,1);
					if (is_numeric ($term)) {
						switch ($sym) {
			        	    case "d": $settings[$term]->day = $value; break;
				            case "m": $settings[$term]->month = $value; break;
				            case "y": $settings[$term]->year = $value; break;
				            case "n": $settings[$term]->denominator = $value; break;
				            case "l": $settings[$term]->duration = $value; break;
		               }
				   }
    		    }

				for ($term=1; $term<=12; $term++)		{
					$db_set = get_record('dean_journal_startdate', 'groupid', $gid, 'term', $term);
					$ch_date = make_timestamp($settings[$term]->year, $settings[$term]->month, $settings[$term]->day, 12);
			        if ( $db_set )  {
						if (($db_set->datestart != $ch_date) || ($db_set->denominator != $settings[$term]->denominator)
  						     || ($db_set->duration != $settings[$term]->duration)) 	{
							$db_set->datestart = $ch_date;
							$db_set->denominator = $settings[$term]->denominator;
							$db_set->duration  = $settings[$term]->duration;
	             			if (!update_record('dean_journal_startdate', $db_set))	{
								error(get_string('errorinsavesettings','block_dean').'(update)', "$CFG->wwwroot/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka&amp;term=$term$strurl");
							}
			            }
			        } else {
							$rec->groupid = $gid;
							$rec->term = $term;
							$rec->datestart = $ch_date;
							$rec->denominator = $settings[$term]->denominator;
							$rec->duration  = $settings[$term]->duration;
							// print_r($rec);
	             			if (!insert_record('dean_journal_startdate', $rec))	{
								error(get_string('errorinsavesettings','block_dean').'(insert)', "$CFG->wwwroot/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka&amp;term=$term$strurl");
							}
			        }
				}
			    add_to_log(1, 'dean', 'startdate_update', "/blocks/dean/journal/journalsetting.php?gid=$gid", $USER->lastname.' '.$USER->firstname);
				notice(get_string('savesettings','block_dean'), "$CFG->wwwroot/blocks/dean/journal/journalsetting.php?gid=$gid&amp;d=$vkladka&amp;term=$term$strurl");
			// show start date
			} else {
       			$curriculum = get_record('dean_curriculum', 'id', $cid);

				echo  '<table border="0" cellspacing="2" cellpadding="5" align="center" class="generalbox">';
	    	    echo  '<tr>
			    		<th valign="top" nowrap="nowrap" class="header">'.get_string('term', 'block_dean').'</th>
						<th valign="top" nowrap="nowrap" class="header">'.get_string('onestartdate', 'block_dean').'</th>
						<th valign="top" nowrap="nowrap" class="header">'.get_string('numer_denom', 'block_dean').'</th>
						<th valign="top" nowrap="nowrap" class="header">'.get_string('duration', 'block_dean').'</th>
			 		  </tr>';

		       	echo  '<form name="datesetting" action="journalsetting.php" method="post">';
		        echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
		        echo  '<input type="hidden" name="d" value="'.$vkladka.'" />';
		        echo  '<input type="hidden" name="term" value="'.$term.'" />';
	    	    echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
				if ($strurl != '') 	{
					echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
					echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
					echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
				}

				//$nowdate = time();
				$menudenominator[0] = get_string('numerator', 'block_dean');
				$menudenominator[1] = get_string('denominator', 'block_dean');

				$startdates = get_records ('dean_journal_startdate', 'groupid', $gid, 'term');
				if ($startdates )	{
					// print_r($startdates);
					foreach ($startdates as $sd)	{
						echo  '<tr><td align="center" class="generalboxcontent">'.$sd->term.'</td>';
						echo  '<td align="center" class="generalboxcontent">';
						print_date_selector("d".$sd->term, "m".$sd->term, "y".$sd->term, $sd->datestart);
						echo  '</td><td align="center" class="generalboxcontent">';
						// print_r($sd->denominat);
						choose_from_menu($menudenominator, 'n'.$sd->term,  $sd->denominator, '');
						echo  '</td><td align="center" class="generalboxcontent"><input type="text" size="3" name="l'.$sd->term. '" value="'. $sd->duration .'" /></td></tr>';
					}
					echo  '</table>';
					echo  '<div align="center"><input type="submit" value="'.get_string('savechanges').'" />';
					echo  '</form>';
				} else {

					for ($numterm=1; $numterm<=12; $numterm++)	 {
						$startdate = get_startdateweek($curriculum->enrolyear, $numterm, 1, false);
						echo  '<tr><td align="center" class="generalboxcontent">'.$numterm.'</td>';
						echo  '<td align="center" class="generalboxcontent">';
						print_date_selector("d".$numterm, "m".$numterm, "y".$numterm, $startdate);
						echo  '</td><td align="center" class="generalboxcontent">';
						choose_from_menu($menudenominator, 'n'.$numterm, 0, '');
						echo  '</td><td align="center" class="generalboxcontent"><input type="text" size="3" name="l'.$numterm. '" value="18" /></td></tr>';
					}
					echo  '</table>';
					echo  '<div align="center"><input type="submit" value="'.get_string('savechanges').'" />';
					echo  '</form>';
				}
			}
		}

    print_footer();

?>
