<?PHP // $Id: disciplines.php,v 1.4 2012/10/30 06:56:30 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $fid = required_param('fid', PARAM_INT);        // Faculty id
	$sid = required_param('sid', PARAM_INT);		// Speciality id
	$cid = required_param('cid', PARAM_INT);		// Curriculum id
	$term = optional_param('term', 1, PARAM_INT);	// # semestra

	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        // disciplines_download('xls', $sid, $fid, $cid, $term);
         download_disciplines_to_excel('xls', $sid, $fid, $cid);
        exit();
	}

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$guest_is = isguest();

    if ($guest_is) {
        error(get_string('notguestaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
	}

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
   		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
	}

    $strfaculty     = get_string('faculty','block_dean');
	$strspeciality  = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
    $strdisciplines = get_string("disciplines","block_dean");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $strdisciplines";
    print_header("$site->shortname: $strdisciplines", "$site->fullname", $breadcrumbs);

/*
	print_heading(get_string("ffaculty","block_dean").":&nbsp;".$faculty->name, "center", 1);
	print_heading(get_string("sspeciality", "block_dean").":&nbsp;".$speciality->name, "center", 2);
	print_heading($curriculum->name, "center", 3);
*/
    print_dean_box_start("center");
?>

<tr valign="top">
    <td align="right"><b><?php  print_string("ffaculty","block_dean") ?>:</b></td>
    <td align="left"> <?php p($faculty->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("sspeciality","block_dean") ?>:</b></td>
    <td align="left"> <?php echo $speciality->number.'&nbsp;'.$speciality->name ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("curriculum","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("enrolyear","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->enrolyear) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("formlearning","block_dean") ?>:</b></td>
    <td align="left"> <?php print_string("$curriculum->formlearning","block_dean")?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("qualification","block_dean") ?>:</b></td>
    <td align="left"> <?php p($speciality->qualification) ?> </td>
</tr>


<?php

    print_dean_box_end();

	$strhoursnumber = get_string("hoursnumber","block_dean");
	$strterm		= get_string("term","block_dean");
	$strauditorium	= get_string("auditoriumhours","block_dean");
	$strselfinstruction = get_string("selfinstructionhours","block_dean");

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();

    $table->head  = array ('#', // get_string("cipher","block_dean"),
						   get_string("disciplinename","block_dean").' /<br>'.get_string("course"),
						   get_string("allhours","block_dean"),
						   $strhoursnumber.'<br>'.$strauditorium,
						   $strhoursnumber.'<br>'.$strselfinstruction,
						   get_string("termpaper","block_dean"),
						   get_string("controltype","block_dean"),
						   get_string("action","block_dean"));
    $table->align = array ("left",  "left", "center", "center", "center", "center", "center", "center");

//	$currcourse = get_records ('dean_discipline', 'curriculumid', $cid);
	if ($term > 0 )	{
		$currcourse =  get_records_sql ("SELECT * FROM  {$CFG->prefix}dean_discipline
									  WHERE curriculumid=$cid AND term=$term ORDER BY name");
	} else {
		$currcourse =  get_records_sql ("SELECT * FROM  {$CFG->prefix}dean_discipline
									  WHERE curriculumid=$cid ORDER BY name");
	}

	$i = 0;
	if ($currcourse)
	foreach ($currcourse as $discipline) {
		

		if 	($admin_is || $creator_is)	 {
			$title = get_string('editdiscipline','block_dean');
			$strlinkupdate = "<a title=\"$title\" href=\"addiscipline.php?mode=edit&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;did={$discipline->id}\">";
			$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
			$title = get_string('deleteddiscipline','block_dean');
		    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"deldiscipline.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;did={$discipline->id}&amp;kid={$discipline->courseid}\">";
			$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";

			if ($discipline->courseid == 1) {
				$strdiscipline = $discipline->name . ' /<br><b>' . get_string('notcoursediscipline','block_dean').'</b>';
			} else {
                if ($acourse = get_record_select("course", "id = $discipline->courseid" , 'id, visible, fullname')) {
                    $linkcss = $acourse->visible ? "" : ' class="dimmed" ';			 
		            $strdiscipline = $discipline->name . ' /<br>' . "<strong><a $linkcss href=\"{$CFG->wwwroot}/course/view.php?id={$acourse->id}\">$acourse->fullname</a></strong>";
                } else {
                    $strdiscipline = $discipline->name . ' /<br>' . "?";
                }    
			}
		}
		else	{
			$strlinkupdate = '-';
			if ($discipline->courseid == 1) {
				$strdiscipline = $discipline->name . ' /<br><b>' . get_string('notcoursediscipline','block_dean').'</b>';
			} else {
                if ($acourse = get_record_select("course", "id = $discipline->courseid" , 'id, visible, fullname')) {
		          $strdiscipline = $discipline->name . ' /<br>' . $acourse->fullname;
                } else {
                  $strdiscipline = $discipline->name . ' /<br>?';  
                }  
			}
		}

		$i++;
		$table->data[] = array ($i, // "$discipline->cipher",
								$strdiscipline,
								$discipline->auditoriumhours+$discipline->selfinstructionhours,
								$discipline->auditoriumhours,
								$discipline->selfinstructionhours,
								$discipline->termpaperhours,
								get_string($discipline->controltype, 'block_dean'),
								$strlinkupdate);
	}


	echo "<hr />";
	// print_heading($strdisciplines, "center", 4);
	print_heading(get_string("disciplinesterm","block_dean"), "center", 4);

    $currenttab = $strterm.$term;
    include('tabsemestr.php');

    print_table($table);

	if 	($admin_is || $creator_is )	 {
			?>
			<table align="center">
				<tr>
				<td>
			  <form name="adddiscipl" method="post" action="addiscipline.php">
				    <div align="center">
					<input type="submit" name="adddiscipline" value="<?php print_string('addiscipline','block_dean')?>"/>
			        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>"/>
			        <input type="hidden" name="mode" value="new" />
					<input type="hidden" name="fid" value="<?php echo $fid ?>"/>
			 		<input type="hidden" name="sid" value="<?php echo $sid ?>"/>
					<input type="hidden" name="cid" value="<?php echo $cid ?>"/>
					<input type="hidden" name="term" value="<?php echo $term ?>"/>
			  	    </div>
			  </form>
			  </td>
				<td>
			  <form name="adddiscipl" method="post" action="addfromcategory.php">
				    <div align="center">
					<input type="submit" name="addfromcategory" value="Добавить дисциплины из категории"/>
			        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>"/>
			        <input type="hidden" name="mode" value="new" />
					<input type="hidden" name="fid" value="<?php echo $fid ?>"/>
			 		<input type="hidden" name="sid" value="<?php echo $sid ?>"/>
					<input type="hidden" name="cid" value="<?php echo $cid ?>"/>
					<input type="hidden" name="term" value="<?php echo $term ?>"/>
			  	    </div>
			  </form>
			  </td>
				<td>
				<form name="download" method="post" action="<?php echo "disciplines.php?action=excel&amp;fid=$fid&amp;cid=$cid&amp;term=$term&amp;sid=$sid" ?>">
				    <div align="center">
					<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>"/>
				    </div>
			  </form>
				</td>
				</tr>
			  </table>
			<?php
	}

	if 	($admin_is)	 {
			// delete all discipline
			?>
			<table align="center">
				<tr><td>
				  <form name="delalldisc" method="post" action="delalldiscipline.php">
					    <div align="center">
						<input type="submit" name="delalldiscipline" value="<?php print_string('delalldiscipline','block_dean')?>">
						<input type="hidden" name="fid" value="<?php echo $fid ?>"/>
				 		<input type="hidden" name="sid" value="<?php echo $sid ?>"/>
						<input type="hidden" name="cid" value="<?php echo $cid ?>"/>
				  	    </div>
				  </form>
			    </td></tr>
			 </table>
			<?php
	}
    print_footer();


function disciplines_download($download, $sid, $fid, $cid, $term)
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
	    $formatp->set_align('center');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

		$faculty = get_record('dean_faculty', 'id', $fid);

		$myxls->set_column(0,0,4);
		$myxls->set_column(1,1,30);
		$myxls->set_column(2,2,10);
		$myxls->set_column(3,3,15);
		$myxls->set_column(4,4,15);
		$myxls->set_column(5,5,15);
		$myxls->set_column(6,6,15);
		$myxls->set_row(0, 30);
        $myxls->write_string(0,0,$faculty->name,$formath1);
		$myxls->merge_cells(0, 0, 0, 6);

        $myxls->write_string(1,0, 'N' ,$formath2);
        $myxls->write_string(1,1,get_string('disciplinename','block_dean') ,$formath2);
        $myxls->write_string(1,2,strip_tags(get_string('allhours','block_dean')),$formath2);
        $myxls->write_string(1,3,get_string('hoursnumber_a','block_dean'),$formath2);
		$myxls->write_string(1,4,get_string('hoursnumber_s','block_dean'),$formath2);
		$myxls->write_string(1,5,strip_tags(get_string('termpaper','block_dean')),$formath2);
		$myxls->write_string(1,6,strip_tags(get_string('controltype','block_dean')),$formath2);

	$currcourse =  get_records_sql ("SELECT *
									  FROM  {$CFG->prefix}dean_discipline
									  WHERE curriculumid=$cid AND term=$term
									  ORDER BY courseid");

	if ($currcourse)	{
    $i = 1;
	foreach ($currcourse as $discipline) {
		$i++;
		$acourse = get_record("course", "id", $discipline->courseid);
		$myxls->write_string($i,0,($i-1).'.',$formatp);
    	$myxls->write_string($i,1,$discipline->name,$formatp);
        $myxls->write_string($i,2,$discipline->auditoriumhours+$discipline->selfinstructionhours,$formatp);
        $myxls->write_string($i,3,$discipline->auditoriumhours,$formatp);
		$myxls->write_string($i,4,$discipline->selfinstructionhours,$formatp);
		$myxls->write_string($i,5,$discipline->termpaperhours,$formatp);
        $myxls->write_string($i,6,get_string($discipline->controltype, 'block_dean'),$formatp);
		}
		$i++;
  	   	$myxls->write_string($i,2,get_string('vsego','block_dean'),$formath1);
       	$myxls->write_formula($i, 3, "=COUNTA(D3:D$i)", $formath1);
	}

       $workbook->close();
       exit;
	}
}


function download_disciplines_to_excel($download, $sid, $fid, $cid)
{
    global $CFG;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");


		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "curriculum_".$fid.'_'.$cid;
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

		$faculty = get_record('dean_faculty', 'id', $fid);
    	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
       		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
    	}

        $txtl = new textlib();
        
		$myxls->set_column(0,0,25);
		$myxls->set_column(1,1,30);
		$myxls->set_column(2,2,40);
		$myxls->set_column(3,3,15);
		/*
        $myxls->set_column(4,4,15);
		$myxls->set_column(5,5,15);
		$myxls->set_column(6,6,15);
		$myxls->set_row(0, 30);
        $myxls->write_string(0,0,$faculty->name,$formath1);
		$myxls->merge_cells(0, 0, 0, 6);
        */

        $strwin1251 =  $txtl->convert('Рабочий учебный план', 'utf-8', 'windows-1251');
        $myxls->write_string(0,0,$strwin1251,$formath2);
        $strwin1251 =  $txtl->convert($curriculum->name, 'utf-8', 'windows-1251');
        $myxls->write_string(0,1, $strwin1251,$formath2);
        $strwin1251 =  $txtl->convert('Номер семестра', 'utf-8', 'windows-1251');
        $myxls->write_string(1,0, $strwin1251,$formath2);        
        $strwin1251 =  $txtl->convert('Короткое имя курса', 'utf-8', 'windows-1251');
        $myxls->write_string(1,1, $strwin1251,$formath2);        
        $strwin1251 =  $txtl->convert('Наименование дисциплины', 'utf-8', 'windows-1251');
        $myxls->write_string(1,2, $strwin1251,$formath2);        
        $strwin1251 =  $txtl->convert('Экзамен или зачет', 'utf-8', 'windows-1251');
        $myxls->write_string(1,3, $strwin1251,$formath2);        

	$currcourse =  get_records_sql ("SELECT id, courseid, term, controltype, name
									  FROM  {$CFG->prefix}dean_discipline
									  WHERE curriculumid=$cid 
									  ORDER BY term");

	if ($currcourse)	{
    $i = 1;
	foreach ($currcourse as $discipline) {
		$i++;
 	    $myxls->write($i,0,$discipline->term,$formatp);
		if ($acourse = get_record_select("course", "id = $discipline->courseid", 'id, shortname'))    {
            $strwin1251 =  $txtl->convert($acourse->shortname, 'utf-8', 'windows-1251');
            $myxls->write_string($i,1, $strwin1251,$formatp);
		} else {
		   $myxls->write_string($i,1, '-',$formatp);
		}
        $strwin1251 =  $txtl->convert($discipline->name, 'utf-8', 'windows-1251');
        $myxls->write_string($i,2, $strwin1251,$formatp);  
        $strctrltype = get_string($discipline->controltype, 'block_dean');
        $strwin1251 =  $txtl->convert($strctrltype, 'utf-8', 'windows-1251');
        $myxls->write_string($i,3, $strwin1251,$formatp);  
        
		}
    }

       $workbook->close();
       exit;
	}
}
?>