<?php // $Id: curriculum.php,v 1.5 2011/10/06 09:01:47 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$currenttab = optional_param('fl', 'correspondenceformtraining');

	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        lstgroupmember_download('xls', $sid,$fid,$currenttab,$mode,'');
        exit();
	}

	$guest_is = isguest();

    if ($guest_is) {
        error(get_string('notguestaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0 && $sid != 0)  {
       error(get_string("errorselect","block_dean"), "curriculum.php?mode=1&amp;fid=0&amp;sid=0");
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

    $strfaculty = get_string('faculty','block_dean');
    $strffaculty = get_string("ffaculty","block_dean");
    $strspeciality = get_string("speciality","block_dean");
    $strsspeciality = get_string("sspeciality","block_dean");
//    $strinformation = get_string("information","block_dean");
    $strcurriculums = get_string("curriculums","block_dean");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> $strcurriculums";
    print_header("$SITE->shortname: $strcurriculums", "$SITE->fullname", $breadcrumbs);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("curriculum.php?mode=1&amp;sid=$sid&amp;fid=", $fid);
    listbox_speciality("curriculum.php?mode=2&amp;fid=$fid&amp;sid=", $fid, $sid);
	echo '</table>';

	if ($fid != 0 && $sid != 0 && $mode > 1)  {

		$admin_is = isadmin();
		$creator_is = iscreator();
		$teacher_is = isteacherinanycourse();

		echo "<hr />";
		// print_heading(get_string('curriculums', 'block_dean'), 'center');

		$curscheck1 = array ('correspondenceformtraining', 'daytimeformtraining', 'eveningformtraining');
        foreach ($curscheck1 as $curscheck)    {
			$curricul = get_records_sql ("SELECT id, name, code, enrolyear, formlearning, description
										  FROM {$CFG->prefix}dean_curriculum
										  WHERE facultyid=$fid AND specialityid=$sid AND formlearning='$curscheck'
										  ORDER BY enrolyear");

		    // $strcodecurriculum = get_string("codecurriculum","block_dean");
		    $strnamecurriculum = get_string("namecurriculum","block_dean");
			$strenrolyear =  get_string("enrolyear","block_dean");
		    $strformlearning = get_string("formlearning","block_dean");

        	// $table->align = array ("left", "left", "left");

			if ($curricul)	{
     			// print_heading($strformlearning.': '. get_string($curscheck, 'block_dean'), 'center', 4);
                print_heading(get_string($curscheck, 'block_dean') . ' форма обучения', 'center', 3);


    			$table->head  = array ($strnamecurriculum, $strenrolyear, get_string('kurs','block_dean'),
    									get_string('countdis', 'block_dean'), get_string('countgroup', 'block_dean') . ' (очников)',
                                        get_string('countgroup', 'block_dean') . ' <br>(заочников и др.)',
    									get_string('countstud', 'block_dean'), get_string('action', 'block_dean'));
       		    $table->align = array ("left", "center", "center", "center", "center", "center","center","center");

				foreach ($curricul as $curr) {
					if 	($admin_is || $creator_is)	 {
						$title = get_string('editcurriculum','block_dean');
						$strlinkupdate = "<a title=\"$title\" href=\"addcurriculum.php?mode=edit&amp;fid=$fid&amp;sid=$sid&amp;cid={$curr->id}\">";
						$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
						$title = get_string('deletecurriculum','block_dean');
					    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delcurriculum.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}\">";
						$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
						$title = get_string('groups');
					    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid={$curr->id}\">";
						$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/groupv.gif\" alt=\"$title\" /></a>&nbsp;";
						$title = get_string('disciplines','block_dean');
						$strdiscipline = "<strong><a title=\"$title\" href=\"disciplines.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}\">$curr->name</a></strong>";
					}
					else	{
						$strlinkupdate = '-';
						$strdiscipline = $curr->name;
					}
					if ($teacher_is) 	{
						$title = get_string('disciplines','block_dean');
						$strdiscipline = "<strong><a title=\"$title\" href=\"disciplines.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}\">$curr->name</a></strong>";
					}
					$countdis = count_records('dean_discipline', 'curriculumid', $curr->id);
					$ocountgroup = $zcountgroup = $countstud = 0;
					if ($agroups = get_records_select('dean_academygroups', "curriculumid = $curr->id", '', 'id, name, idotdelenie'))	{
						// $countgroup = count($agroups);
						foreach ($agroups as $agroup)	{
						    if ($agroup->idotdelenie == 2)    {
                                $ocountgroup++;
						    } else {
						        $zcountgroup++;
						    }
							$countstud += count_records('dean_academygroups_members', 'academygroupid', $agroup->id);
						}
					} else {
						$countgroup = 0;
					}

                    $strenrol = "<B><a href=\"$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid={$curr->id}\">" . $curr->enrolyear . '</a></B>';
					$table->data[] = array ($strdiscipline, $strenrol, get_kurs($curr->enrolyear),
					                        $countdis, $ocountgroup, $zcountgroup, $countstud, $strlinkupdate);
				}
	 			 print_table($table);
	 			 unset($table);
	 			 unset($curricul);
			}
			else {
				// notify(get_string('errorcurriculums', 'block_dean'));
			}
		}

		if 	($admin_is || $creator_is )	 {
			?>
			<p></p>
            <hr />
            <p></p>
            <table align="center">
				<tr>
				<td>
			  <form name="addcurr" method="post" action="<?php echo "addcurriculum.php?mode=new&amp;fid=$fid&amp;sid=$sid"; ?>">
				    <div align="center">
					<input type="submit" name="addcurriculum" value="<?php print_string('addcurriculum','block_dean')?>">
				    </div>
			  </form>
			  </td>
				<td>
				<form name="download" method="post" action="<?php echo "curriculum.php?action=excel&amp;mode=$mode&amp;fid=$fid&amp;curscheck=$curscheck&amp;sid=$sid" ?>">
				    <div align="center">
					<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
				    </div>
			  </form>
				</td>
				</tr>
			  </table>
			<?php
		}

		if 	($admin_is)	 {
			// delete all
		}

	}

    print_footer();

function lstgroupmember_download($download,$sid,$fid,$currenttab,$mode,$curscheck)
{
    global $CFG;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");


		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "curriculum";
        header("Content-Disposition: attachment; filename=\"$downloadfilename.xls\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

/// Creating a workbook
        $workbook = new Workbook("-");
        $myxls =& $workbook->add_worksheet($downloadfilename);

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

		$formath2->set_size(11);
	    $formath2->set_align('center');
	    $formath2->set_align('vcenter');
		$formath2->set_color('black');
		$formath2->set_bold(1);
		//$formath2->set_italic();
		$formath2->set_border(2);
		$formath2->set_text_wrap();

		$formatp->set_size(11);
	    $formatp->set_align('left');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

        $txtl = new textlib();

		if ($curscheck == 'correspondenceformtraining')  {
			$curricul = get_records_sql ("SELECT id, name, code, enrolyear, formlearning, description
										  FROM {$CFG->prefix}dean_curriculum
										  WHERE facultyid=$fid AND specialityid=$sid AND formlearning='correspondenceformtraining'
										  ORDER BY enrolyear");
		}
		else if ($curscheck == 'daytimeformtraining') 	{
			$curricul = get_records_sql ("SELECT id, name, code, enrolyear, formlearning, description
										  FROM {$CFG->prefix}dean_curriculum
										  WHERE facultyid=$fid AND specialityid=$sid AND formlearning='daytimeformtraining'
										  ORDER BY enrolyear");
		}
		else if  ($curscheck == 'eveningformtraining') 	{
			$curricul = get_records_sql ("SELECT id, name, code, enrolyear, formlearning, description
										  FROM {$CFG->prefix}dean_curriculum
										  WHERE facultyid=$fid AND specialityid=$sid AND formlearning='eveningformtraining'
										  ORDER BY enrolyear");
		}

		$faculty = get_record('dean_faculty', 'id', $fid);

		$myxls->set_column(0,0,4);
		$myxls->set_column(1,1,30);
		$myxls->set_column(2,2,10);
		$myxls->set_column(3,3,15);
		$myxls->set_column(4,4,15);
		$myxls->set_column(5,5,15);
		$myxls->set_column(6,6,15);
		$myxls->set_row(0, 30);
		$strwin1251 =  $txtl->convert($faculty->name, 'utf-8', 'windows-1251');
        $myxls->write_string(0,0,$strwin1251,$formath1);
		$myxls->merge_cells(0, 0, 0, 6);

        $myxls->write_string(1,0, 'N' ,$formath2);

        $namecurriculum = get_string('namecurriculum','block_dean');
        $strwin1251 =  $txtl->convert($namecurriculum, 'utf-8', 'windows-1251');
        $myxls->write_string(1,1,$strwin1251 ,$formath2);

        $codecurriculum = get_string('codecurriculum','block_dean');
        $strwin1251 =  $txtl->convert($codecurriculum, 'utf-8', 'windows-1251');
        $myxls->write_string(1,2,$strwin1251,$formath2);

        $enrolyear = get_string('enrolyear','block_dean');
        $strwin1251 =  $txtl->convert($enrolyear, 'utf-8', 'windows-1251');
        $myxls->write_string(1,3,$strwin1251,$formath2);

        $formlearning = get_string('formlearning','block_dean');
        $strwin1251 =  $txtl->convert($formlearning, 'utf-8', 'windows-1251');
		$myxls->write_string(1,4,$strwin1251,$formath2);

		$countdis = strip_tags(get_string('countdis','block_dean'));
		$strwin1251 =  $txtl->convert($countdis, 'utf-8', 'windows-1251');
		$myxls->write_string(1,5,$strwin1251,$formath2);

		$countgroup = strip_tags(get_string('countgroup','block_dean'));
		$strwin1251 =  $txtl->convert($countgroup, 'utf-8', 'windows-1251');
		$myxls->write_string(1,6,$strwin1251,$formath2);

  		$curricul = get_records_sql ("SELECT id, name, code, enrolyear, formlearning, description
										  FROM {$CFG->prefix}dean_curriculum
										  WHERE facultyid=$fid AND specialityid=$sid AND formlearning='$curscheck'
										  ORDER BY enrolyear");

		if ($curricul)	{
			$i = 1;
			foreach ($curricul as $curr) {
				$i++;
				$countdis = count_records('dean_discipline', 'curriculumid', $curr->id);
				$countgroup = count_records('dean_academygroups', 'curriculumid', $curr->id);
				$myxls->write_string($i,0,($i-1).'.',$formatp);

				$strwin1251 =  $txtl->convert($curr->name, 'utf-8', 'windows-1251');
    	       	$myxls->write_string($i,1,$strwin1251,$formatp);

        	    $myxls->write_string($i,2,$curr->code,$formatp);
           	   	$myxls->write_string($i,3,$curr->enrolyear,$formatp);

           	   	$c = get_string($curr->formlearning,'block_dean');
           	   	$strwin1251 =  $txtl->convert($c, 'utf-8', 'windows-1251');
				$myxls->write_string($i,4,$strwin1251,$formatp);
				$myxls->write_string($i,5,$countdis,$formatp);
           	   	$myxls->write_string($i,6,$countgroup,$formatp);
				}
				$i++;

			 $vsego = get_string('vsego','block_dean');
  	   		 $strwin1251 =  $txtl->convert($spec->name, 'utf-8', 'windows-1251');
  	   		 $myxls->write_string($i,2,$strwin1251,$formath1);
       		 $myxls->write_formula($i, 3, "=COUNTA(D3:D$i)", $formath1);
			}

       $workbook->close();
       exit;
	}
}


?>


