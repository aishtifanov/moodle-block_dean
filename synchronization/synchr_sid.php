<?php // $Id: synchr_sid.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

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

    $currenttab = 'synchr_sid';
    include('tabsynchr.php');

    $csv_delimiter = ";";

	/// If a file has been uploaded, then process it
	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);
	if ($um->preprocess_files()) {

		$filename = $um->files['userfile']['tmp_name'];
       	$fp = fopen($filename, "r");
	   	// $arr = array((),());
		$tbFS = array();
		$tbFS  = xfgetcsv ($fp, 1000, $csv_delimiter);
		if ($tbFS[0] == 'DepCode' && $tbFS[1] == 'Facultet' &&
			$tbFS[2] == 'code' && $tbFS[3] == 'SpecName')  {
			while (!feof ($fp)) {
			   if ($tbFS  = xfgetcsv ($fp, 1000, $csv_delimiter))  {
         			if ($faculty = get_record('dean_faculty', 'number', $tbFS[0])) {
						if ($speciality = get_record('dean_speciality', 'facultyid', $faculty->id, 'number', $tbFS[2])) {
							$speciality->name = $tbFS[3];
							if (update_record('dean_speciality', $speciality))	{
								notify(get_string('updatespeciality', 'block_dean').':'.$speciality->name);
							} else  {
								error(get_string('errorinupdatingspec','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
							}
						} else { //Add speciality
							$rec->facultyid = $faculty->id;
							$rec->number = $tbFS[2];
							$rec->name = $tbFS[3];
							$rec->qualification = "";
							$rec->timemodified = time();
							if (insert_record('dean_speciality', $rec))	{
								notify(get_string('specialityadded','block_dean').':'.$rec->name);
							} else {
								error(get_string('errorinaddingspec','block_dean'), "$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$fid");
							}
						 }
					} else {
						notify(get_string('facultynotfound','block_dean').':'.$tbFS[1]);
					}
				}
    		}
		} else {
			error ('Incorect file format');
		}
	 notice(get_string('synchrcomplete','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");

	}

/// Print the form
    $struploadrup = get_string("synchr_sid", "block_dean");
    print_heading_with_help($struploadrup, 'synchr_sid');

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose");
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="synchr_sid.php">'.
         $strchoose.':<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
         '</form></br>';
    echo '</center>';

    print_footer($course);

?>

