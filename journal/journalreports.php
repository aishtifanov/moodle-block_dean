<?php // $Id: journalreports.php,v 1.3 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    $gid = required_param('gid', PARAM_INT);          // Group id
	$term = optional_param('term', 1, PARAM_INT);		// # semestra
    $fid = optional_param('fid', 0, PARAM_INT);      // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);      // Speciality id
    $cid = optional_param('cid', 0, PARAM_INT);		// Curriculum id

    $frm = data_submitted(); /// load up any submitted data
	// print_r($frm);

	$action   = optional_param('action', 'report');
    if ($action == 'report1') {
        journalreport1_download($frm);
        exit();
	} else if ($action == 'report2') {
        journalreport2_download($frm);
        exit();
	}

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

		// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
	    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_faculty("jouirnalreports.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("jouirnalreports.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("jouirnalreports.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("jouirnalreports.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
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


    $currenttabjournal ='journalreports';
	include('tabsjournal.php');

	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/journal/journalreports.php?gid=$gid".$strurl.'&amp;term=');

	echo  '<form name="journalstats" action="journalreports.php" method="post">';
	echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
	echo  '<input type="hidden" name="term" value="'.$term.'" />';
	echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
	if ($strurl != '') 	{
		echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
		echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
		echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
	}

	if ( !empty($frm->month_start) ) {
		$date_start	=	mktime(12, 12, 0, $frm->month_start, $frm->day_start, $frm->year_start);
		$date_end	=	mktime(12, 12, 0, $frm->month_end, $frm->day_end, $frm->year_end);
		$nowdate_1 = $date_start;
		$nowdate_2 = $date_end;
	} else {
		$startdate = get_record_sql ("SELECT * FROM {$CFG->prefix}dean_journal_startdate
									 WHERE groupid=$gid AND term=$term");
		$nowdate_2 = time();
		$nowdate_1 = date($startdate->datestart);
	}

	$strperiodwith = get_string('periodwith','block_dean'). "&nbsp&nbsp&nbsp&nbsp&nbsp";
	$strperiodon = "&nbsp&nbsp&nbsp&nbsp&nbsp". get_string('periodon','block_dean'). "&nbsp&nbsp&nbsp&nbsp&nbsp";

	echo '<div align=center>';
	print "$strperiodwith";
	print_date_selector("day_start", "month_start", "year_start", $nowdate_1);
	print "$strperiodon";
	print_date_selector("day_end", "month_end", "year_end", $nowdate_2);
	echo '<br><br>';

	echo '<input type="submit" name="report1" value="'.get_string('journalattendancereports','block_dean').'" />';
	echo '<input type="submit" name="report2" value="'.get_string('journaltotalreports','block_dean').'" />';
	echo '</div><br><br>';
	echo '</form>';

	if ($frm)	{
		if (isset($frm->report1)) 	{
				echo '<table border="1" cellspacing="2" cellpadding="5" align="center" class="generalbox">';
				echo '<tr>';
				echo '<th rowspan="2" valign="top" class="header">' . get_string('group'). '</th>';
				echo '<th rowspan="2" valign="top" class="header">' . get_string('journalkontingent', 'block_dean'). '</th>';
				echo '<th rowspan="2" valign="top" class="header">' . get_string('journaldogovorosnova', 'block_dean') . '</th>';
			    echo '<th colspan="4" valign="top" class="header">' . get_string('journaltotal', 'block_dean') . '</th>';
		   		echo '<th colspan="4" valign="top" class="header">' . get_string('journaluvpr', 'block_dean') . '</th>';
				echo '</tr>';
				echo '<tr>';
			    echo '<th valign="top" class="header">' . get_string('journalkolstudent', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovrnikov', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalkolhear', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovrniki', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalkolstudent', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovrnikov', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalkolhear', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovrniki', 'block_dean') . '</th>';
				echo '</tr>';

				$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, m.academygroupid
									FROM {$CFG->prefix}user u
							   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
				$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
				$studentsql .= 'ORDER BY u.lastname';

				 // print_r($studentsql);
				$students = get_records_sql($studentsql);
				$rep = array();
				for ($i=1; $i<=10; $i++) $rep[$i]=0;

				if(!empty($students))   {
					$rep[1] = count($students);
					// $cc = $dd = $ee = $ff = $gg = $hh = $ii = $jj = $kk = $ll = 0;
					//   1		2	3		4	5		6	7		8	9	10
					foreach ($students as $student) {

						$card = get_record('dean_student_studycard', 'userid', $student->id);
						if ($card)	{
							if ($card->financialbasis == 'fd')	$rep[2]++; // $dd++;
						}

						$strSQL = "SELECT j.id, j.userid, j.dateday, j.mark
										   FROM {$CFG->prefix}dean_journal_marks j
										   WHERE j.userid={$student->id} AND j.dateday>={$nowdate_1} AND j.dateday<={$nowdate_2};";
						$marks = get_records_sql($strSQL);

						$hours_n = $hours_u = 0;
						if ($marks)	{
							foreach ($marks as $mark)	{
								switch ($mark->mark)	{
									case 4: $hours_n += 2; break;
									case 3: $hours_n += 1; break;
									case 2: $hours_u += 2; break;
									case 1: $hours_u += 1; break;
								}
							}
                        }

                        if ($hours_n > 0)  {
                        	$rep[3]++; // $ee++;
                        	$rep[5] += $hours_n;
							if ($card)	{
								if ($card->financialbasis == 'fd')	{
									$rep[4]++;	// $ff++;
		                        	$rep[6] += $hours_n;
								}
							}
                        }

                        if ($hours_u > 0)  {
                        	$rep[7]++; // $ii++;
                        	$rep[9] += $hours_u;
							if ($card)	{
								if ($card->financialbasis == 'fd')	{
									$rep[8]++; // $jj++;
		                        	$rep[10] += $hours_u;
								}
							}
                        }

					}
				}

				echo '<tr>';
			    echo "<td align=\"center\">$academygroup->name</td>";
			    $stralldata = '';
			    for ($i=1; $i<=10; $i++)  {
			    	echo "<td align=\"center\">$rep[$i]</td>";
			    	$stralldata .= $rep[$i].';';
			    }
			    /*
			    echo "<td align=\"center\">$cc</td>";
			    echo "<td align=\"center\">$dd</td>";
			    echo "<td align=\"center\">$ee</td>";
			    echo "<td align=\"center\">$ff</td>";
			    echo "<td align=\"center\">$gg</td>";
			    echo "<td align=\"center\">$hh</td>";
			    echo "<td align=\"center\">$ii</td>";
			    echo "<td align=\"center\">$jj</td>";
			    echo "<td align=\"center\">$kk</td>";
			    echo "<td align=\"center\">$ll</td>";
			    */
				echo '</tr>';
				echo '</table>';

				$options = array();
			    $options['cid'] = $cid;
			    $options['gid'] = $gid;
			   	$options['sesskey'] = $USER->sesskey;
			    $options['action'] = 'report1';
			    $options['strarray'] = $stralldata;
   			    $options['period'] = userdate ($nowdate_1, '%x').' - '.userdate ($nowdate_2, '%x');
				echo '<table align="center"><tr>';
			    echo '<td align="center">';
			    print_single_button("journalreports.php", $options, get_string("downloadexcel"), 'post');
			    echo '</td></tr>';
			    echo '</table>';
		}

		if (isset($frm->report2)) 	{

				echo '<table border="1" cellspacing="2" cellpadding="5" align="center" class="generalbox">';
				echo '<tr>';
				echo '<th rowspan="2" valign="top" class="header">' . get_string('ffaculty', 'block_dean'). '</th>';
				echo '<th rowspan="2" valign="top" class="header">' . get_string('journalkontingent', 'block_dean'). '</th>';
				echo '<th rowspan="2" valign="top" class="header">' . get_string('journaldogovorosnova', 'block_dean') . '</th>';
			    echo '<th colspan="7" valign="top" class="header">' . get_string('journaltotal', 'block_dean') . '</th>';
		   		echo '<th colspan="6" valign="top" class="header">' . get_string('journaluvpr', 'block_dean') . '</th>';
				echo '</tr>';
				echo '<tr>';
			    echo '<th valign="top" class="header">' . get_string('journalkolstudent', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalattendancepercent', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalattendancepercentuvpr', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovorosnova', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalattendancepercent', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalkolhear', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovorosnova', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalkolstudent', 'block_dean') . '</th>';
   			    echo '<th valign="top" class="header">' . get_string('journalattendancepercent', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journaldogovorosnova', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalattendancedogovor', 'block_dean') . '</th>';
			    echo '<th valign="top" class="header">' . get_string('journalkolhear', 'block_dean') . '</th>';
   			    echo '<th valign="top" class="header">' . get_string('journaldogovorosnova', 'block_dean') . '</th>';
				echo '</tr>';

				// print_r($academygroup);
				$facultyI = get_record('dean_faculty', 'id', $academygroup->facultyid);
				$curriculums_daylearn = get_records('dean_curriculum', 'facultyid', $academygroup->facultyid);
                // print_r($curriculums_daylearn);
				for ($i=1; $i<=15; $i++) 	{
					$itogo[$i]=0;
				}
				$namegroups = array();
				$allgroups = array();

				if ($curriculums_daylearn) foreach ($curriculums_daylearn as $curr)	{
					if ($curr->formlearning == 'daytimeformtraining') {
						$agroups = get_records('dean_academygroups', 'curriculumid', $curr->id);
						// print_r($agroup);
						if ($agroups) foreach ($agroups as $agroup)	{
						    if (substr($agroup->name, 0, 2) != ($facultyI->number-100)) continue;
							$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, m.academygroupid
											FROM {$CFG->prefix}user u
									   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
							$studentsql .= 'WHERE academygroupid = '.$agroup->id.' AND u.deleted = 0 AND u.confirmed = 1 ';
							$studentsql .= 'ORDER BY u.lastname';

							 // print_r($studentsql);
							$students = get_records_sql($studentsql);
							$rep = array();
							for ($i=1; $i<=15; $i++) 	{
								$rep[$i]=0;
							}

							if(!empty($students))   {
								$rep[1] = count($students);
								// $cc = $dd = $ee = $ff = $gg = $hh = $ii = $jj = $kk = $ll = 0;
								//   1		2	3		4	5		6	7		8	9	10
								foreach ($students as $student) {

									$card = get_record('dean_student_studycard', 'userid', $student->id);
									if ($card)	{
										if ($card->financialbasis == 'fd')	$rep[2]++; // $dd++;
									}

									$strSQL = "SELECT j.id, j.userid, j.dateday, j.mark
													   FROM {$CFG->prefix}dean_journal_marks j
													   WHERE j.userid={$student->id} AND j.dateday>={$nowdate_1} AND j.dateday<={$nowdate_2};";
									$marks = get_records_sql($strSQL);

									$hours_n = $hours_u = 0;
									if ($marks)	{
										foreach ($marks as $mark)	{
											switch ($mark->mark)	{
												case 4: $hours_n += 2; break;
												case 3: $hours_n += 1; break;
												case 2: $hours_u += 2; break;
												case 1: $hours_u += 1; break;
											}
										}
			                        }

			                        if ($hours_n > 0)  {
			                        	$rep[3]++; // $ee++;
			                        	$rep[8] += $hours_n;
										if ($card)	{
											if ($card->financialbasis == 'fd')	{
												$rep[6]++;	// $hh
					                        	$rep[9] += $hours_n;
											}
										}
			                        }

			                        if ($hours_u > 0)  {
			                        	$rep[10]++;
			                        	$rep[14] += $hours_u;
										if ($card)	{
											if ($card->financialbasis == 'fd')	{
												$rep[12]++; // $jj++;
					                        	$rep[15] += $hours_u;
											}
										}
			                        }
								}
								if ($rep[1] != 0)   {
									$rep[4] = number_format(100-$rep[3]*100/$rep[1], 1);
									$rep[5] = number_format(100-(($rep[3]-$rep[10])*100/$rep[1]), 1);
								    $rep[11] = number_format(100-$rep[10]*100/$rep[1], 1);
								}
								if ($rep[2] != 0) {
									$rep[7] = number_format(100-$rep[6]*100/$rep[2], 1);
           						}
           						if ($rep[9] !=0 ) {
								    $rep[13] = number_format(100-$rep[12]*100/$rep[9], 1);
								}
							}

						    $stralldata[$agroup->name] = '';
						    for ($i=1; $i<=15; $i++)  {
						    	$stralldata[$agroup->name] .= $rep[$i].';';
						    	$allgroups[$agroup->name][$i] = $rep[$i];
						    	$itogo[$i] = $itogo[$i] + $rep[$i];
						    }
						    $namegroups[] = $agroup->name;
						    // print_r($itogo);

						}
					}
          		}
				if ($itogo[1] != 0)   {
					$itogo[4] = number_format(100-$itogo[3]*100/$itogo[1], 1);
					$itogo[5] = number_format(100-(($itogo[3]-$itogo[10])*100/$itogo[1]), 1);
				    $itogo[11] = number_format(100-$itogo[10]*100/$itogo[1], 1);
				}
				if ($itogo[2] != 0) {
					$itogo[7] = number_format(100-$itogo[6]*100/$itogo[2], 1);
				}
    			if ($itogo[9] !=0 ) {
				    $itogo[13] = number_format(100-$itogo[12]*100/$itogo[9], 1);
				}

                natsort($namegroups);
                // print_r($allgroups);
                $strallgroup = '';
                foreach($namegroups as $namegr)	{
					echo '<tr>';
				    echo "<td align=\"center\">$namegr</td>";
				    for ($i=1; $i<=15; $i++)  {
					    $aa = $allgroups[$namegr][$i];
    	                echo "<td align=\"center\">$aa</td>";
				    }
	                echo '</tr>';
	                $strallgroup .= $namegr.';';
                }

				echo '<tr>';
			    echo '<td align="center"><b>'.get_string('ffaculty', 'block_dean').'</b></td>';
			    $stritogodata = '';
			    for ($i=1; $i<=15; $i++)  {
			    	echo "<td align=\"center\"><b>$itogo[$i]</b></td>";
			    	$stritogodata .= $itogo[$i].';';
			    }
                echo '</tr>';
				echo '</table>';

				$options = array();
			    $options['cid'] = $cid;
			    $options['gid'] = $gid;
			   	$options['sesskey'] = $USER->sesskey;
			    $options['action'] = 'report2';
			    $options['namegroup'] = $strallgroup;
                foreach($namegroups as $namegr)	{
                	 $options[$namegr] = $stralldata[$namegr];
			    }
			    $options['itogodata'] = $stritogodata;
			    $options['period'] = userdate ($nowdate_1, '%x').' - '.userdate ($nowdate_2, '%x');

				echo '<table align="center"><tr>';
			    echo '<td align="center">';
			    print_single_button("journalreports.php", $options, get_string("downloadexcel"), 'post');
			    echo '</td></tr>';
			    echo '</table>';
		}

	}

    print_footer();

function journalreport1_download($frm)
{
    global $CFG;


        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");

		$agroup = get_record('dean_academygroups', 'id', $frm->gid);
		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = clean_filename("reportgroup_".$agroup->name);
        header("Content-Disposition: attachment; filename=\"$downloadfilename.xls\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

/// Creating a workbook
        $workbook = new Workbook("-");
        $myxls =& $workbook->add_worksheet($agroup->name);

/// Print names of all the fields
		$formath1 =& $workbook->add_format();
		$formath2 =& $workbook->add_format();
		$formatp =& $workbook->add_format();

		$formath1->set_size(11);
	    $formath1->set_align('center');
	    $formath1->set_align('vcenter');
		$formath1->set_color('black');
		$formath1->set_bold(1);
		$formath1->set_italic();
		// $formath1->set_border(2);

		$formath2->set_size(9);
	    $formath2->set_align('center');
	    $formath2->set_align('vcenter');
		$formath2->set_color('black');
		$formath2->set_bold(1);
		//$formath2->set_italic();
		$formath2->set_border(1);
		$formath2->set_text_wrap();

		$formatp->set_size(10);
	    $formatp->set_align('center');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

        for ($i=0; $i<11; $i++)	{
			$myxls->set_column($i,$i,10);
		}

        for ($i=0; $i<4; $i++)	{
  	        for ($j=0; $j<11; $j++)	{
				$myxls->write_blank($i,$j,$formatp);
      		}
        }


		$myxls->set_row(0, 30);
        $myxls->write_string(0,0, get_string('journalreport1title', 'block_dean', $agroup->name). ' '. $frm->period, $formath1);
		$myxls->merge_cells(0, 0, 0, 10);

		$myxls->write_string(1, 0, get_string('group'), $formath2);
		$myxls->merge_cells(1, 0, 2, 0);
		$myxls->write_string(1, 1, get_string('journalkontingent', 'block_dean'),$formath2);
		$myxls->merge_cells(1, 1, 2, 1);
		$myxls->write_string(1, 2, get_string('journaldogovorosnova', 'block_dean'),$formath2);
		$myxls->merge_cells(1, 2, 2, 2);
	    $myxls->write_string(1, 3, get_string('journaltotal', 'block_dean'),$formath2);
   		$myxls->merge_cells(1, 3, 1, 6);
   		$myxls->write_string(1, 7, get_string('journaluvpr', 'block_dean'),$formath2);
   		$myxls->merge_cells(1, 7, 1, 10);
	    $myxls->write_string(2, 3, get_string('journalkolstudent', 'block_dean'),$formath2);
	    $myxls->write_string(2, 4, get_string('journaldogovrnikov', 'block_dean') ,$formath2);
	    $myxls->write_string(2, 5, get_string('journalkolhear', 'block_dean') ,$formath2);
	    $myxls->write_string(2, 6, get_string('journaldogovrniki', 'block_dean') ,$formath2);
	    $myxls->write_string(2, 7, get_string('journalkolstudent', 'block_dean') ,$formath2);
	    $myxls->write_string(2, 8, get_string('journaldogovrnikov', 'block_dean') ,$formath2);
	    $myxls->write_string(2, 9, get_string('journalkolhear', 'block_dean') ,$formath2);
	    $myxls->write_string(2, 10, get_string('journaldogovrniki', 'block_dean') ,$formath2);

       	$rep = explode(";", $frm->strarray);

		$myxls->write_string(3,0,$agroup->name, $formathp);
	    for ($i=1; $i<=10; $i++)  {
	    	$myxls->write_number(3, $i, $rep[$i-1], $formatp);
	    }

       $workbook->close();
       exit;
}


function journalreport2_download($frm)
{
    global $CFG;
    $NUMCOLUMN = 15;

    require_once("$CFG->libdir/excel/Worksheet.php");
    require_once("$CFG->libdir/excel/Workbook.php");

	// HTTP headers
    header("Content-type: application/vnd.ms-excel");
    $downloadfilename = clean_filename("reportfacultet_".$frm->gid);
    header("Content-Disposition: attachment; filename=\"$downloadfilename.xls\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

	/// Creating a workbook
    $workbook = new Workbook("-");
    $myxls =& $workbook->add_worksheet($agroup->name);

	/// Print names of all the fields
	$formath1 =& $workbook->add_format();
	$formath2 =& $workbook->add_format();
	$formatp =& $workbook->add_format();

	$formath1->set_size(12);
    $formath1->set_align('center');
    $formath1->set_align('vcenter');
	$formath1->set_color('black');
	$formath1->set_bold(1);
	$formath1->set_italic();
	// $formath1->set_border(2);

	$formath2->set_size(9);
    $formath2->set_align('center');
    $formath2->set_align('vcenter');
	$formath2->set_color('black');
	$formath2->set_bold(1);
	//$formath2->set_italic();
	$formath2->set_border(1);
	$formath2->set_text_wrap();

	$formatp->set_size(10);
    $formatp->set_align('center');
    $formatp->set_align('vcenter');
	$formatp->set_color('black');
	$formatp->set_bold(0);
	$formatp->set_border(1);
	$formatp->set_text_wrap();

    for ($i=0; $i<=$NUMCOLUMN; $i++)	{
		$myxls->set_column($i,$i,10);
	}

	$agroup = get_record('dean_academygroups', 'id', $frm->gid);
	$facultyI = get_record('dean_faculty', 'id', $agroup->facultyid);
	$namesgroups = explode(";", $frm->namegroup);
	$counts =  count($namesgroups) + 3;

    for ($i=0; $i<$counts; $i++)	{
        for ($j=0; $j<=$NUMCOLUMN; $j++)	{
			$myxls->write_blank($i,$j,$formatp);
   		}
    }

	$myxls->set_row(0, 30);
    $myxls->write_string(0,0,get_string('journalsvedenija', 'block_dean').' '.$frm->period, $formath1);
	$myxls->merge_cells(0, 0, 0, $NUMCOLUMN);

	$myxls->write_string(1, 0, get_string('groups'), $formath2);
	$myxls->merge_cells(1, 0, 2, 0);
	$myxls->write_string(1, 1, get_string('journalkontingent', 'block_dean'),$formath2);
	$myxls->merge_cells(1, 1, 2, 1);
	$myxls->write_string(1, 2, get_string('journaldogovorosnova', 'block_dean'),$formath2);
	$myxls->merge_cells(1, 2, 2, 2);
    $myxls->write_string(1, 3, get_string('journaltotal', 'block_dean'),$formath2);
	$myxls->merge_cells(1, 3, 1, 9);
	$myxls->write_string(1, 10, get_string('journaluvpr', 'block_dean'),$formath2);
	$myxls->merge_cells(1, 10, 1, 15);

    $myxls->write_string(2, 3, get_string('journalkolstudent', 'block_dean'),$formath2);
    $myxls->write_string(2, 4, get_string('journalattendancepercent', 'block_dean') ,$formath2);
    $myxls->write_string(2, 5, get_string('journalattendancepercentuvpr', 'block_dean') ,$formath2);
    $myxls->write_string(2, 6, get_string('journaldogovorosnova', 'block_dean') ,$formath2);

    $myxls->write_string(2, 7, get_string('journalattendancepercent', 'block_dean') ,$formath2);
    $myxls->write_string(2, 8, get_string('journalkolhear', 'block_dean') ,$formath2);
    $myxls->write_string(2, 9, get_string('journaldogovorosnova', 'block_dean') ,$formath2);
    $myxls->write_string(2, 10, get_string('journalkolstudent', 'block_dean') ,$formath2);

    $myxls->write_string(2, 11, get_string('journalattendancepercent', 'block_dean') ,$formath2);
    $myxls->write_string(2, 12, get_string('journaldogovorosnova', 'block_dean') ,$formath2);
    $myxls->write_string(2, 13, get_string('journalattendancepercent', 'block_dean') ,$formath2);
    $myxls->write_string(2, 14, get_string('journalkolhear', 'block_dean') ,$formath2);
    $myxls->write_string(2, 15, get_string('journaldogovorosnova', 'block_dean') ,$formath2);


    // foreach($namesgroups as $namegr)	{
    $stroka = 3;
    foreach($frm as $key => $value)	{
    	if (in_array($key, $namesgroups))	{
    		$rep = explode(";", $value);
			$myxls->write_string($stroka,0,$key, $formathp);

		    for ($i=1; $i<=$NUMCOLUMN; $i++)  {
  			  	$myxls->write_number($stroka, $i, $rep[$i-1], $formatp);
		    }
			$stroka++;
  		}
  	}

   	$rep = explode(";", $frm->itogodata);

	$myxls->write_string($stroka,0,get_string('ffaculty', 'block_dean').' '.$facultyI->name, $formath2);
    for ($i=1; $i<=$NUMCOLUMN; $i++)  {
    	$myxls->write_number($stroka, $i, $rep[$i-1], $format2);
    }

    $workbook->close();
    exit;

}


?>

