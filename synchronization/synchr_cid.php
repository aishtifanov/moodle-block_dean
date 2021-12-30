<?php // $Id: synchr_cid.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

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

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$site->shortname: $strsynchronization", $site->fullname, $breadcrumbs);

    $currenttab = 'synchr_cid';
    include('tabsynchr.php');

    $csv_delimiter = ";";

	/// If a file has been uploaded, then process it
	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);
	if ($um->preprocess_files()) {

		$filename = $um->files['userfile']['tmp_name'];
       	$fp = fopen($filename, "r");
	   	// $arr = array((),());
		$tbPlan = array();
		$tbPlan  = xfgetcsv ($fp, 1000, $csv_delimiter);
		if ($tbPlan[0] == 'idSpec' && $tbPlan[1] == 'idPlan' &&
			$tbPlan[2] == 'code' && $tbPlan[3] == 'SpecName' && $tbPlan[4] == 'PlName')  {
			while (!feof ($fp)) {
			   if ($tbPlan = xfgetcsv ($fp, 1000, $csv_delimiter))  {
         			if ($speciality = get_record('dean_speciality', 'number', $tbPlan[2])) {
						if ($curriculum = get_record('dean_curriculum', 'specialityid', $speciality->id, 'code', $tbPlan[1]))	{

							$curriculum->name = $tbPlan[4];

							$choices['daytimeformtraining'] = get_string('daytimeformtraining', 'block_dean');
						    $choices['eveningformtraining'] = get_string('eveningformtraining', 'block_dean');
						    $choices['correspondenceformtraining'] = get_string('correspondenceformtraining', 'block_dean');
							$choices['acceleratedformtraining'] = get_string('acceleratedformtraining', 'block_dean');
							$choices['poformtraining'] = get_string('poformtraining', 'block_dean');
							$choices['n_oformtraining'] = get_string('n_oformtraining', 'block_dean');
							if (!$key = array_search($tbPlan[5], $choices)) $key='n_oformtraining';
							$curriculum->formlearning = $key;

							if ($tbPlan[6] != $choices['n_oformtraining'])  {
								$ey = explode ('/', $tbPlan[6]);
								$rec->enrolyear = '20'.$ey[0].'/20'.$ey[1];
							} else {
								$rec->enrolyear = $choices['n_oformtraining'];
							}

							$curriculum->thisyear = $tbPlan[7];
							$curriculum->timemodified = time();
							if (update_record('dean_curriculum', $curriculum))	{
								notify(get_string('curriculumupdate', 'block_dean').':'.$curriculum->name);
							} else  {
								error(get_string('errorinupdatingcurr','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
							}

						} else { //Add curriculum
							$rec->facultyid = $speciality->facultyid;
							$rec->specialityid = $speciality->id;
							$rec->code = $tbPlan[1];
							$rec->name = $tbPlan[4];

							$choices['daytimeformtraining'] = get_string('daytimeformtraining', 'block_dean');
						    $choices['eveningformtraining'] = get_string('eveningformtraining', 'block_dean');
						    $choices['correspondenceformtraining'] = get_string('correspondenceformtraining', 'block_dean');
							$choices['acceleratedformtraining'] = get_string('acceleratedformtraining', 'block_dean');
							$choices['poformtraining'] = get_string('poformtraining', 'block_dean');
							$choices['n_oformtraining'] = get_string('n_oformtraining', 'block_dean');
							if (!$key = array_search($tbPlan[5], $choices)) $key='n_oformtraining';
							$rec->formlearning = $key;
							if ($tbPlan[6] != $choices['n_oformtraining'])  {
								$ey = explode ('/', $tbPlan[6]);
								$rec->enrolyear = '20'.$ey[0].'/20'.$ey[1];
							} else {
								$rec->enrolyear = $choices['n_oformtraining'];
							}
							$rec->thisyear = $tbPlan[7];
							$rec->timemodified = time();

							if (insert_record('dean_curriculum', $rec))	{
								notify(get_string('curriculumadded','block_dean').':'.$rec->name);
							} else {
								error(get_string('errorinaddingcurr','block_dean'), "$CFG->wwwroot/blocks/dean/synchronization/synchr_cid.php");
							}
						 }

					} else {
						notify('!!!!!'.get_string('specialitynotfound','block_dean').':'.$tbPlan[2].';'.$tbPlan[3]);
					}
				}
    		}
		} else {
			error ('Incorect file format');
		}
	 notice(get_string('synchrcomplete','block_dean'), "$CFG->wwwroot/blocks/dean/synchronization/synchr_cid.php");

	}

/// Print the form
    $struploadrup = get_string("synchr_cid", "block_dean");
    print_heading_with_help($struploadrup, 'synchr_cid');

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose");
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="synchr_cid.php">'.
         $strchoose.':<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
         '</form></br>';
    echo '</center>';

    print_footer($course);

?>

