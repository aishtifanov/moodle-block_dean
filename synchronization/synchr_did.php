<?php // $Id: synchr_did.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$strsynchronization = get_string('synchronization','block_dean');

	print_header("$site->shortname: $strsynchronization", "$site->fullname", "$strsynchronization");

    $currenttab = 'synchr_did';
    include('tabsynchr.php');

    $csv_delimiter = ";";

	/// If a file has been uploaded, then process it
	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);
	if ($um->preprocess_files()) {

		// $fullnamestable = array();
		$filename = $CFG->dataroot .'/1/tbCourseDiscipl.csv';
		if($fp = fopen($filename, "r"))  {
			$fullnamesrow = array();
			$fullnamesrow = xfgetcsv ($fp, 500, $csv_delimiter);
			$fullnamestable[] = $fullnamesrow;
			while (!feof ($fp)) {
			  if ($fullnamesrow = xfgetcsv ($fp, 500, $csv_delimiter))  {
				 $fullnamestable[] = $fullnamesrow;
				// echo $fullnamesrow[0].'-'.$fullnamesrow[1].'-'.$fullnamesrow[2].'<br>';
	 	        }
	    	}
		fclose($fp);
		} else {
		    error('File tbCourseDiscipl.csv not found', "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
		}

		$filename = $um->files['userfile']['tmp_name'];
		$fp = fopen($filename, "r");
	   	// $arr = array((),());
		$tbDis = array();
		$tbDis = xfgetcsv ($fp, 1000, $csv_delimiter);

		if ($tbDis[0] == 'idDiscipline' && $tbDis[1] == 'idPlan' &&
			$tbDis[2] == 'idSemestr' && $tbDis[3] == 'DisName' && $tbDis[4] == 'PlName')  {
			while (!feof ($fp)) {
			   if ($tbDis = xfgetcsv ($fp, 1000, $csv_delimiter))  {
					// Find curriculum
			   		if ($curriculum = get_record('dean_curriculum', 'code', $tbDis[1]))	{
						// Find discipline
			   			if ($discipline = get_record('dean_discipline', 'curriculumid', $curriculum->id, 'name', $tbDis[3])) {
							// Semestr equal?
							if ($discipline->term == $tbDis[2])  {
								// Update discipline
								$discipline->cipher = $tbDis[0];
								$discipline->term = $tbDis[2];
								$discipline->auditoriumhours = $tbDis[5];
								$discipline->selfinstructionhours = $tbDis[6];
								$discipline->termpaperhours = $tbDis[7];
								$discipline->timemodified = time();
								// Find courseid <==> discipline
								$discipline->courseid = findcompatibility();

								if (update_record('dean_discipline', $discipline))	{
								 notify(get_string('disciplineupdate','block_dean').':'.$discipline->name);
								} else  {
									error(get_string('errorinupdatingdisc','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
								}
							}
							else	{
								add_discipline();
							}
						} else {
							add_discipline();
	 				    }
					} else {
						notify(get_string('curriculumnotfound','block_dean').':'.$tbDis[4]);
					}
				}
    		}
		} else {
			error ('Incorect file format');
		}
	    notice(get_string('synchrcomplete','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
	}

/// Print the form
    $struploadrup = get_string("synchr_did", "block_dean");
    print_heading_with_help($struploadrup, 'synchr_did');

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose");
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="synchr_did.php">'.
         $strchoose.':<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
         '</form></br>';
    echo '</center>';

    print_footer($course);

/// FUNCTIONS
// Return $course->id
function findcompatibility()
{
  global $fullnamestable, $tbDis;

  foreach ($fullnamestable as $fullnamesrow)  {
	if ($tbDis[3] == $fullnamesrow[1])  {
		if($course = get_record('course', 'shortname', $fullnamesrow[2]))	{
			return $course->id;
		}
	}
  }
  return 1;
}

function add_discipline()
{
	 global $tbDis, $CFG, $curriculum;

	//Add discipline
	$rec->curriculumid = $curriculum->id;
	// Find courseid <==> discipline
	$rec->courseid = findcompatibility();
	$rec->cipher = $tbDis[0];
	$rec->term = $tbDis[2];
	$rec->name = $tbDis[3];
	$rec->auditoriumhours = $tbDis[5];
	$rec->selfinstructionhours = $tbDis[6];
	$rec->termpaperhours = $tbDis[7];
	$rec->controltype = 'zaschet';
	$rec->timemodified = time();

	if (insert_record('dean_discipline', $rec))	{
		notify(get_string('disciplineadded','block_dean').':'.$rec->name);
	} else {
		// echo $CFG->wwwroot.'<br><br><br>';
		// print_r($rec);
		error(get_string('errorinaddingdisc','block_dean').':'.$rec->name, "{$CFG->wwwroot}/blocks/dean/synchronization/synchr_did.php");
	}
}

?>

