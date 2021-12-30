<?php // $Id: synchronization.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

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

    $currenttab = 'synchr_fid';
    include('tabsynchr.php');

    $csv_delimiter = ";";

	/// If a file has been uploaded, then process it
	require_once($CFG->dirroot.'/lib/uploadlib.php');
   	$um = new upload_manager('userfile',false,false,null,false,0);
	if ($um->preprocess_files()) {

		$filename = $um->files['userfile']['tmp_name'];
       	$fp = fopen($filename, "r");
	   	// $arr = array((),());
		$tbFacultet = array();
		$tbFacultet  = xfgetcsv ($fp, 1000, $csv_delimiter);
		if ($tbFacultet[0] == 'Facultet' && $tbFacultet[1] == 'Dekan' &&
			$tbFacultet[2] == 'idPerson' && $tbFacultet[3] == 'DepCode')  {
			while (!feof ($fp)) {
			   if ($tbFacultet  = xfgetcsv ($fp, 1000, $csv_delimiter))  {
         			if ($faculty = get_record('dean_faculty', 'number', $tbFacultet[3])) {
						$dean_user = get_record('user', 'id', $faculty->deanid);
		  				$dname = fullname($dean_user);
						if ($dname != $tbFacultet[1])  {
							notify(get_string('newdean', 'block_dean')."$dname: $tbFacultet[1]");
						}
					} else {
						$rec->number = $tbFacultet[3];
						$rec->name = $tbFacultet[0];
						$rec->deanphone1 = "";
						$rec->deanphone2 = "";
						$rec->deanaddress = "";
						$dean_split = explode(" ", $tbFacultet[1]);
						$lastname = $dean_split[0];
						$firstname = $dean_split[1] .' '.$dean_split[2];
						$dean_user = get_record('user', 'firstname', $firstname, 'lastname', $lastname);
						if ($dean_user)  {
						   $rec->deanid = $dean_user->id;
						}  else {
						    $rec->deanid = 0;
							notify(get_string('notfoundnewdean', 'block_dean').": $tbFacultet[1]");
						}
						if (insert_record('dean_faculty', $rec))	{
							notify(get_string('newdeansoffice', 'block_dean').": $tbFacultet[0] - $tbFacultet[3]");
						} else {
							error(get_string('errorinaddingfaculty','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
						}
					}
			   }
    		}
		}	else {
			error ('Incorect file format');
		}
	}

/// Print the form
    $struploadrup = get_string("synchr_fid", "block_dean");
    print_heading_with_help($struploadrup, 'synchr_fid');

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose");
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="synchronization.php">'.
         $strchoose.':<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
         '<input type="file" name="userfile" size="30">'.
         '<input type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
         '</form></br>';
    echo '</center>';

    print_footer($course);

?>

