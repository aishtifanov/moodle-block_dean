<?PHP // $Id: certificate.php,v 1.2 2013/10/09 08:47:49 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');    

    $mode = required_param('mode', PARAM_INT);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);        // Faculty id
	$sid = required_param('sid', PARAM_INT);		// Speciality id
	$cid = required_param('cid', PARAM_INT);		// Curriculum id
	$gid = required_param('gid', PARAM_INT);		// Academygroup ID
	$action   = optional_param('action', '');


    if ($action == 'excel') {
        report_sheet_download($fid, $sid, $cid, $gid);
        exit();
	}

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strchangelist = get_string('changeliststudents', 'block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	// $strstudents = get_string("students","block_dean");
    $strstudents   = get_string("students");
    $strsearch        = get_string("search");
    $strsearchresults  = get_string("searchresults");
    $strshowall = get_string("showall");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strchangelist";
    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
 	}

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Group not found!");
 	}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("certificate.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("certificate.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("certificate.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    listbox_group_pegas("certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode == 4)  {

	    $currenttab = 'certificate';
	    include('tabsonegroup.php');

		$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                              u.lastaccess, m.academygroupid
	                            FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";

		$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
		$studentsql .= 'ORDER BY u.lastname';
	    if ($students = get_records_sql($studentsql))	{

			$arr_discipline = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_discipline
			 								  WHERE curriculumid={$curriculum->id}
											  ORDER BY name");

			if ($arr_discipline) 	{
				foreach ($arr_discipline as $ds) {
					$disciplinemenu[$ds->id] =$ds->name;
				}
			}


			$table->head  = array (get_string('fullname'),	get_string('discipline', 'block_dean'),
									get_string('certificate', 'block_dean'),	get_string('hoursnumber', 'block_dean'),
									get_string('datecertificate', 'block_dean'),
									get_string('action', 'block_dean'));
		    $table->align = array ("left", "left", "center", "center", "center", "center");

            $num_cert = 0;
			foreach ($students as $student)  {
				if ($certs = get_records('dean_certificate', 'studentid', $student->id)) {
 				   $num_cert += count($certs);
				   foreach($certs as $cert)	{
			           $dsname = $disciplinemenu[$cert->disciplineid];
			           /*
					   $monthstring = get_string('lm_'.date('n',$cert->datecreated), 'block_dean');
					   $datestring =  str_replace(' 0', '', gmstrftime(' %d', $cert->datecreated))." ".$monthstring." ".date('Y', $cert->datecreated);
					   */
					   $datestring = get_rus_format_date($cert->datecreated, 'full');
			   		   $title = get_string('templatecertificate','block_dean');
		  	           $strlinkupdate = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/printcrt.php?certid={$cert->id}&amp;gid=$gid&amp;uid={$student->id}\">".'<img src="'.$CFG->pixpath."/f/excel.gif\" height=16 width=16 alt=\"$title\" />".'</a>';
			 		   $table->data[] = array (fullname($student), $dsname, $cert->number, $cert->hours, $datestring, $strlinkupdate);
			 	   }
				}
			}
		    print_table($table);
		}  else {
			print_heading(get_string('errorstudentsnotinthisgroup', 'block_dean'), 'center', 4);
		}

		if ($num_cert == 0)		{
			print_heading(get_string('certificatenotfound', 'block_dean', $academygroup->name), 'center', 4);
		}
    }

	echo  '<form name="certificate" action="certificate_add.php" method="post">';
	echo  '<input type="hidden" name="mode" value="'.$mode.'" />';
	echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
	echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
	echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
	echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
	echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
	echo '<div align=center>';
	echo '<br><br>';
	echo '<input type="submit" name="cert_all" value="'.get_string('createcertificateforall','block_dean').'" />';
	echo '<input type="submit" name="cert_one" value="'.get_string('createcertificateforone','block_dean').'" />';
    echo '</div>';
	echo '</form>';

?> <table align="center">
	<tr align="center">

	<tr align="center">
  	<td colspan=2 align="center">
		<form name="download" method="post" action="certificate.php?action=excel">
		<input type="hidden" name="mode" value="<?php echo $mode ?>" />
		<input type="hidden" name="fid" value="<?php echo $fid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="cid" value="<?php echo $cid ?>" />
		<input type="hidden" name="gid" value="<?php echo $gid ?>" />
		<input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />
			<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
     	</form>
	</td>
	</tr>
  </table>
 <?php

    print_footer();


function report_sheet_download($fid, $sid, $cid, $gid)
{
    global $CFG;

    	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        	error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
 		}

        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");

        $agroup = get_record('dean_academygroups', 'id', $gid);


		// HTTP headers
		$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                              u.lastaccess, m.academygroupid
	                            FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";

		$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
		$studentsql .= 'ORDER BY u.lastname';

		//print_r($discipline);
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "uchet_".$gid;
        header("Content-Disposition: attachment; filename=\"$downloadfilename.xls\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

/// Creating a workbook
        $workbook = new Workbook("-");
        $myxls =& $workbook->add_worksheet($downloadfilename);

	    $myxls->set_margin_left(0.8);
	    $myxls->set_margin_right(0.4);
	    $myxls->set_margin_top(0.7);
	    $myxls->set_margin_bottom(0.7);


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

		$myxls->set_column(0,0,3);
		$myxls->set_column(1,1,24);
		// $myxls->set_column(2,2,15);
		$myxls->set_column(2,2,20);
		$myxls->set_column(3,3,19);
		$myxls->set_column(4,4,15);
		$myxls->set_row(0, 60);
        //$myxls->write_string(0,0,$strretakes,$formath1);



        $myxls->write_string(1,0, '№' ,$formath2);
        $myxls->write_string(1,1,'Ф.И.О' ,$formath2);
        //$myxls->write_string(1,2,get_string('group','block_dean'),$formath2);
        // $myxls->write_string(1,2,get_string('disciplinee','block_dean'),$formath2);
        $myxls->write_string(1,2,get_string('regnum','block_dean'),$formath2);
        $myxls->write_string(1,3,get_string('dateoftake','block_dean'),$formath2);
		$myxls->write_string(1,4,get_string('signing','block_dean'),$formath2);
		//$myxls->write_string(1,5,get_string('mark','block_dean'),$formath2);


		if ($students = get_records_sql($studentsql))	{

			$discipline = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_discipline
			 								  WHERE curriculumid={$curriculum->id}");

			if ($discipline) 	{
				foreach ($discipline as $ds) {
					$disciplinemenu[$ds->id] =$ds->name;
				}
			}

            $i = 1;
            $num_cert = 0;
			foreach ($students as $student) 	{
				 $user = get_record('user', 'id', $student->id);
				 if ($certs = get_records('dean_certificate', 'studentid', $student->id)) {
				     $num_cert += count($certs);
                   foreach($certs as $cert)	{
			           $dsname = $disciplinemenu[$cert->disciplineid];
			           $datestring = get_rus_format_date($cert->datecreated, 'full');
              			$i++;
    	       			$myxls->write_string($i,0,($i-1).'.',$formatp);
    	       			$myxls->write_string($i,1,fullname($user),$formatp);
    	       			// myxls->write_string($i,2,$dsname,$formatp);
           	            $myxls->write_string($i,2,$cert->number,$formatp);
        	    		$myxls->write_string($i,3,$datestring . ' г.',$formatp);
        	    		$myxls->write_string($i,4,'',$formatp);
					}
                 } else {
                 	error('Не возможно скачать список сертификатов из-за их отсутствия у данной группы');
                 	}

     		}

             $titleText = ('Ведомость учета выдачи удостоверений слушателям группы'.'  '.$agroup->name.',прошедших курс'.'   '.$dsname);
			 $myxls->write_string(0,0,$titleText,$formath2);
			 $myxls->merge_cells(0, 0, 0, 4);
	  	     $i++;
  	   		 $myxls->write_string($i+2,1,'Директор ЦДО БелГУ',$formath1);
  	   		 $myxls->write_string($i+2,3,'Немцев А.Н.',$formath1);
       		// $myxls->write_formula($i, 3, "=COUNTA(D3:D$i)", $formath1);


		}

       $workbook->close();
}


?>