<?PHP // $Id: delretake.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

//  Lists all the sessions for a course
    require_once("../../../config.php");

    $rid     = required_param('rid', PARAM_INT);                 // Faculty id
	$confirm = optional_param('confirm');

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strretakes = get_string('retakes', 'block_dean');
    $strdelretake = get_string('deletingretake','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"faculty.php\">$strretakes</a> -> $strdelretake";
    print_header("$site->shortname: $strretakes", $site->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), 'faculty.php');
    }

	if (!$reroll = get_record('dean_reroll', 'id', $rid)) {
        error(get_string('errorfaculty', 'block_dean'), 'faculty.php');
	}

	if (isset($confirm)) {
		$countspec = count_records('dean_speciality', 'facultyid', $fid);
		if ($countspec == 0)  {
			delete_records('dean_reroll', 'id', $rid);
			add_to_log(1, 'dean', 'Faculty deleted', 'delfaculty.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorindelfaculty','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
		}
		redirect('faculty.php', get_string('facultydeleted','block_dean'), 3);
	}


	print_heading($strdelretake.' :: ' .$reroll->id);

	notice_yesno(get_string('deletecheckfull', '', $reroll->id),
               "delfaculty.php?fid=$fid&amp;confirm=1", "faculty.php");

	print_footer();
?>
