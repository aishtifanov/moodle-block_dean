<?php // $Id: synchr_gid.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

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

    $currenttab = 'synchr_gid';
    include('tabsynchr.php');

    $csv_delimiter = ";";

	/// If a file has been uploaded, then process it
	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);
	if ($um->preprocess_files()) {

		$filename = $um->files['userfile']['tmp_name'];
       	$fp = fopen($filename, "r");
	   	// $arr = array((),());
		$tbGrupp = array();
		$tbGrupp  = xfgetcsv ($fp, 1000, $csv_delimiter);
		if ($tbGrupp[0] == 'idPlan' && $tbGrupp[1] == 'idSem' &&
			$tbGrupp[2] == 'DepCode' && $tbGrupp[3] == 'GodPost' && $tbGrupp[4] == 'Gruppa')  {
			while (!feof ($fp)) {
			   if ($tbGrupp = xfgetcsv ($fp, 1000, $csv_delimiter))  {
			   		// Find academygroup
					if ($agroup = get_record('dean_academygroups', 'name', $tbGrupp[4]))	 {
			   			// Find curriculum
				   		if ($curriculum = get_record('dean_curriculum', 'code', $tbGrupp[0]))	{
							// Update group
							$agroup->facultyid 	  = $curriculum->facultyid;
							$agroup->specialityid = $curriculum->specialityid;
							$agroup->curriculumid = $curriculum->id;
							$agroup->startyear = $tbGrupp[3];
							$agroup->term = $tbGrupp[1];
							$agroup->timemodified = time();

							if (update_record('dean_academygroups', $agroup))	{
								 notify(get_string('groupupdate','block_dean').': '.$agroup->name);
							} else  {
								 error(get_string('errorinupdatinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
							}
						}
						else	{
							notify(get_string('curriculumnotfound','block_dean').':'.$tbGrupp[0]);
						}
					} else {
			   			// Find curriculum
				   		if ($curriculum = get_record('dean_curriculum', 'code', $tbGrupp[0]))	{
							// Add group
							$rec->facultyid    = $curriculum->facultyid;
							$rec->specialityid = $curriculum->specialityid;
							$rec->curriculumid = $curriculum->id;
							$rec->name = $tbGrupp[4];
							$rec->startyear = $tbGrupp[3];
							$rec->term = $tbGrupp[1];
							$rec->timemodified = time();
							if (insert_record('dean_academygroups', $rec))	{
							   notify(get_string('groupadded','block_dean').': '.$rec->name);
							} else  {
								 error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
							}
						}
						else	{
							notify(get_string('curriculumnotfound','block_dean').':'.$tbGrupp[0]);
						}
				    }
    			}
			}
		} else {
			error ('Incorect file format');
		}
	    notice(get_string('synchrcomplete','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");

	}

/// Print the form
    $struploadrup = get_string("synchr_gid", "block_dean");
    print_heading_with_help($struploadrup, 'synchr_gid');

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose");
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="synchr_gid.php">'.
         $strchoose.':<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
         '</form></br>';
    echo '</center>';

    print_footer($course);

?>

