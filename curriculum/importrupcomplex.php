<?php // $Id: importrupcomplex.php,v 1.2 2009/09/02 12:08:15 Shtifanov Exp $

    require_once("../../../config.php");

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

	$breadcrumbs  = "<a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= "-> $strdisciplines ";

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
	// if ($um->preprocess_files()) {
	if (false) {

			$filename = $um->files['userfile']['tmp_name'];
           	$fp = fopen($filename, "r");
		   	// $arr = array((),());
			$RUPtable = array();
			$RUProw = array();
		    $i = -1;
			while (!feof ($fp)) {
			   if ($RUProw = xfgetcsv ($fp, 1000, $csv_delimiter))  {
			       $RUPtable[++$i] = $RUProw;
			   }
		    }
						$fullname_ = 1;
						$cipher_ = 0;
						$auditoriumhours_ = 3;
						$selfinstructionhours_ = 2;   // םמלונא סעמכבצמג ג פאיכו ÐÓÏ
						$term_ = 6;
						$controltype_ = 5;
						$termpaper_ = 4;
						$sovpadenie_ = 7;
						$predmet_ = 8;

			/*
			foreach ($RUPtable as $data)  {
				$cnt = count($data);
				for ($i=0; $i<$cnt; $i++)  {
					echo ' '.$data[$i].'#';
				}
				echo '<br>';
			}
			*/


			fclose($fp);

    	    $filename = $CFG->dataroot .'/1/sootvetstvie.csv';
           	$fp = fopen($filename, "r");
			$fullnamestable = array();
			$fullnamesrow = array();
		    $i = -1;
			while (!feof ($fp)) {
			   if ($fullnamesrow = xfgetcsv ($fp, 1000, $csv_delimiter))  {
			       $fullnamestable[++$i] = $fullnamesrow;
			   }
		    }

			fclose($fp);

/*
			foreach ($fullnamestable as $fullnamesrow)  {
				$cnt = count($fullnamesrow);
				for ($i=0; $i<$cnt; $i++)  {
					echo $fullnamesrow[$i].'$';
				}
				echo '<br>';
			}


			foreach ($RUPtable as $RUProw)  {
				$cnt = count($RUProw);
				for ($i=0; $i<$cnt; $i++)  {
					echo $RUProw[$i].'#';
				}
				echo '<br>';
			}
*/

			$number = array();
			$n = 0;
			$m = 0;
			$p = 0;
			foreach ($RUPtable as $iN => $RUProw)  {
				$dN = $RUProw[$fullname_];
				$numb = 0;
				$j = 0;
				foreach ($fullnamestable as $iS => $fullnamesrow)  {
					/*echo $dN.'-=-'.$fullnamesrow[1];
					echo '<br>';*/
					if ($dN == $fullnamesrow[1])  {
						 $number[$numb] = $j;
						 $numb++;
						/*echo '==============================================================<br>';*/
						}
						$j++;
				}
				if ($numb == 1) {
						$RUPtable[$n][$fullname_] = $fullnamestable[$number[0]][0];
						$RUPtable[$n][$sovpadenie_] = 1;
						$n++;
			    		}
					else {

					   	for ($i=0;$i<$numb;$i++,$m++)  {
						$RUPtable_many[$m][$fullname_] = $fullnamestable[$number[$i]][0];
						$RUPtable_many[$m][$sovpadenie_] = 0;
						$RUPtable_many[$m][$predmet_] = $p;
						$RUPtable_many[$m][$cipher_] =  $RUPtable[$n][$cipher_];
						$RUPtable_many[$m][$auditoriumhours_] = $RUPtable[$n][$auditoriumhours_];
						$RUPtable_many[$m][$selfinstructionhours_] = $RUPtable[$n][$selfinstructionhours_];
						$RUPtable_many[$m][$term_] = $RUPtable[$n][$term_];
						$RUPtable_many[$m][$controltype_] = $RUPtable[$n][$controltype_];
						$RUPtable_many[$m][$termpaper_] = $RUPtable[$n][$termpaper_];

						}
						$n++;
						$p++;
					}
 		    }

			/*
			for ($i=0; $i<20; $i++)
				{
				for ($j=0; $j<10; $j++)
				echo $RUPtable[$i][$j].'#';
				echo '</br>';
				}
			*/
		$ind = 1;
		$allcourses = get_records("course", '', '', "fullname");
//		$alldisciplines = get_records('dean_discipline', 'curriculumid', $cid, 'id');
		/*
		foreach ($alldisciplines as $iS => $data_C)  {
				echo $data_C->fullname.'#'.$data_C->id;
				echo '</br>';
	 	}
			*/



		foreach ($RUPtable as $RUProw)  {
			/* echo $RUProw[fullname_].'#****'.$RUProw[fullname_];
			echo '</br>'; */
		/*	if ($RUProw[sovpadenie_] == 0)  {
				notify(get_string('manycourses','block_dean', $RUProw[fullname_]));
	 	    } */

		   foreach ($allcourses as $course)   {
			  if (($course->fullname == $RUProw[$fullname_]) && ($RUProw[$sovpadenie_] == 1))  {
				  $isexistinbase = get_record('dean_discipline', 'curriculumid', $cid, 'courseid', $course->id,'term', $RUProw[$term_]);
				  if (!$isexistinbase)  {
						$rec->curriculumid = $cid;
						$rec->courseid = $course->id;
						$rec->cipher = $RUProw[$cipher_];
						$rec->auditoriumhours = $RUProw[$auditoriumhours_] ;
						$rec->selfinstructionhours = $RUProw[$selfinstructionhours_];
						$rec->term = $RUProw[$term_];
						$rec->controltype = $RUProw[$controltype_];
						$rec->termpaper = $RUProw[$termpaper_];
						/*echo "--------$i------------<br>";
	 					print_r($rec);*/
						if (insert_record('dean_discipline', $rec))	{
							 add_to_log(1, 'dean', 'one curriculum added', "blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid", $USER->lastname.' '.$USER->firstname);
					 		 notify(get_string('addincurriculum','block_dean', $course->fullname));
						}
						else {
							error(get_string('errorinaddingcurr','block_dean').$course->fullname,
							      "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");
						}
				  }
				  else {
					notify(get_string('courseexistincurriculum','block_dean', $course->fullname));
				  }
			  }
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

    print_footer($course);


?>
