<?php // $Id: importrup.php,v 1.3 2011/10/13 11:25:28 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$cid = required_param('cid', PARAM_INT);		  // Curriculum id

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0 && $sid != 0)  {
       error(get_string("errorselect","block_dean"), "curriculum.php?mode=1&amp;fid=0&amp;sid=0");
	}

	if ($fid == 0 && $cid != 0)  {
       error(get_string("errorselect","block_dean"), "curriculum.php?mode=1&amp;fid=0&amp;sid=0");
	}

	if ($sid == 0 && $cid != 0)  {
       error(get_string("errorselectspec","block_dean"), "curriculum.php?mode=1&amp;fid=$fid&amp;sid=0");
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

	if ($cid == 0)  {
	   $curriculum = get_record_sql("SELECT * FROM {$CFG->prefix}dean_curriculum ", true);
	}
	elseif (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
    }

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

    $strfaculty = get_string('faculty', 'block_dean');
    $strspeciality = get_string('speciality', 'block_dean');
	$strcurriculums = get_string('curriculums','block_dean');
	$strterm		= get_string('term', 'block_dean');
    $struser = get_string("user");
	$strdisciplines = get_string("disciplines","block_dean");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $strdisciplines ";

    print_header("$site->shortname: $strdisciplines", "$site->fullname", $breadcrumbs);

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

    $currenttab = 'importrup';
    include('tabsemestr.php');

    $csv_delimiter = ";";

	/// If a file has been uploaded, then process it
	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);
	if ($um->preprocess_files()) {
		$filename = $um->files['userfile']['tmp_name'];
       	$fp = fopen($filename, "r");
       	
		$textlib = textlib_get_instance();

		$shapka = fgets($fp,1024);
		$shapka = $textlib->convert($shapka, 'win1251');

        list($curriculumkeyword, $curriculumname) = split($csv_delimiter, $shapka);
		$curriculumname = trim($curriculumname);
		if ($curriculumkeyword != get_string('curriculumkeyword', 'block_dean'))  {
           error(get_string('unknownlabel', 'block_dean', $curriculumkeyword), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}
		if (empty($curriculumname))	{
           error(get_string('unknowncurriculumname', 'block_dean'), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}

		if ($curriculumname !=  $curriculum->name)  {
		   echo $curriculumname . '<br>';
		   echo '<pre>'; print_r($curriculum); echo '</pre>'; 
           error(get_string('curriculumismatch', 'block_dean'), "importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
		}

		$shapka = fgets($fp,1024);

		$RUProw = array();
	    $i = 0;
		while (!feof ($fp)) {
		   if ($RUProw = xfgetcsv ($fp, 1000, $csv_delimiter))  {
		   		foreach ($RUProw as $indx => $rup)	{
					$RUProw[$indx] = $textlib->convert($RUProw[$indx], 'win1251');		   			
		   		}
		      // print_r($RUProw); echo '<hr>';
		   	  $recs[$i]->curriculumid = $cid;
		  	  $recs[$i]->term = trim($RUProw[0]);
		  	  $recs[$i]->name = trim($RUProw[2]);
			  if (trim($RUProw[1]) == '-') {
			  	  $recs[$i]->courseid = 1;
			  }	else  {
			      // print_r($RUProw);
			      $sncourse = trim($RUProw[1]);
     			  if ($crs = get_record('course', 'shortname', $sncourse))	{
                  	  $recs[$i]->courseid = $crs->id;
     			  } else {
			          error(get_string('errorinfindshortname', 'block_dean', $sncourse), "importrup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid");
     			  }
			  }
		 	  $recs[$i]->cipher = 'unknown';
			  $recs[$i]->auditoriumhours = 0;
			  $recs[$i]->selfinstructionhours = 0;
			  $recs[$i]->termpaper = 0;
			  if (trim($RUProw[3]) == get_string('zaschet', 'block_dean')) {
				  $recs[$i]->controltype = 'zaschet';
			  } else if (trim($RUProw[3]) == get_string('examination', 'block_dean')) {
				  $recs[$i]->controltype = 'examination';
			  }	else {
				  $recs[$i]->controltype = 'notis';
			  }
              $i++;
		   }
	    }

		foreach ($recs as $rec)  {
			// print_r($rec);
			// echo '<hr>';
			if ($rec->courseid == 1 || !record_exists_select('dean_discipline', "curriculumid=$cid AND courseid={$rec->courseid} AND term={$rec->term}"))	{
				if (insert_record('dean_discipline', $rec))	{
					 // add_to_log(1, 'dean', 'one curriculum added', "blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid", $USER->lastname.' '.$USER->firstname);
			 		 notify(get_string('addincurriculum','block_dean', $rec->name), 'green', 'left');
				}
				else {
					error(get_string('errorinaddingcurr','block_dean').$rec->name,
	 			      "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");
				}
			}  else {
					notify("Дисциплина \"$rec->name\" уже есть в учебном плане!", 'black');
			}	
	    }

        notice(get_string('curriculumadded','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");

	}
/// Print the form
    $struploadrup = get_string("uploadrup", "block_dean");
    print_heading_with_help($struploadrup, 'importrup');

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose");
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importrup.php">'.
         $strchoose.':<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
		 '<input type="hidden" name="fid" value="'. $fid.'" />'.
 		 '<input type="hidden" name="sid" value="'. $sid. '" />'.
		 '<input type="hidden" name="cid" value="'. $cid. '" />'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.$struploadrup.'">'.
         '</form></br>';
    echo '</center>';
?>
<p><strong>Пример файла импорта в формате CSV (кодировка Windows-1251):</strong> </p>
<p><font size="1" face="Courier New, Courier, mono"></font>
Рабочий учебный план;з/о 030501.65 Юриспруденция Д/О;;<br />
Номер семестра;Короткое имя курса;Наименование дисциплины;Экзамен или зачет<br />
3;Философия_694;Философия;экзамен<br />
1;ОснИсполДОТ_739;Основы использования ДОТ студентами вуза ;-<br />
1;English_1;Иностранный язык. Английский язык. Базовый курс;-<br />
1;English for_02;Иностранный язык. Английский язык. Обязательный модуль для юристов;-<br />
1;Немецкийяз_074;Иностранный язык. Немецкий язык. Базовый курс;-<br />
1;Немецкий яз_076;Иностранный язык. Немецкий язык. Обязательный модуль для юристов;-<br />
1;Французский_523;Иностранный язык. Французский язык. Базовый курс;-<br />
2;English_1;Иностранный язык. Английский язык. Базовый курс;зачет<br />
2;English for_02;Иностранный язык. Английский язык. Обязательный модуль для юристов;зачет<br />
2;Немецкийяз_074;Иностранный язык. Немецкий язык. Базовый курс;зачет<br />
2;Немецкий яз_076;Иностранный язык. Немецкий язык. Обязательный модуль для юристов;зачет<br />
2;Французский_523;Иностранный язык. Французский язык. Базовый курс;зачет<br />
1;Логика_575;Логика;экзамен<br />
</font></p>
<?php

    print_footer();

?>
