<?PHP // $Id: delfaculty.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

//  Lists all the sessions for a course
    require_once("../../../config.php");

    $fid     = required_param('fid', PARAM_INT);                 // Faculty id
	$confirm = optional_param('confirm');

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty', 'block_dean');
    $strdelfaculty = get_string('deletingfaculty','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"faculty.php\">$strfaculty</a> -> $strdelfaculty";
    print_header("$site->shortname: $strfaculty", $site->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), 'faculty.php');
    }

	if (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), 'faculty.php');
	}

	if (isset($confirm)) {
		$countspec = count_records('dean_speciality', 'facultyid', $fid);
		if ($countspec == 0)  {
			delete_records('dean_faculty', 'id', $fid);
			add_to_log(1, 'dean', 'Faculty deleted', 'delfaculty.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorindelfaculty','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
		}
		redirect('faculty.php', get_string('facultydeleted','block_dean'), 3);
	}


	print_heading($strdelfaculty.' :: ' .$faculty->name);

	notice_yesno(get_string('deletecheckfull', '', $faculty->name),
               "delfaculty.php?fid=$fid&amp;confirm=1", "faculty.php");

	print_footer();
?>
