<?PHP // $Id: delspeciality.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

//  Lists all the sessions for a course
    require_once("../../../config.php");

    $fid     = required_param('fid', PARAM_INT);                 // Faculty id
    $sid     = required_param('sid', PARAM_INT);                 // Speciality id
	$confirm = optional_param('confirm');

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
    $strdelspeciality = get_string('deletingspeciality','block_dean');


    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"speciality.php?id=0\">$strspeciality</a> -> $strdelspeciality";
    print_header("$site->shortname: $strdelspeciality", $site->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$spec = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), 'speciality.php?id=0');
	}

	if (isset($confirm)) {
		$countgroup = count_records('dean_academygroups', 'specialityid', $sid);
		if ($countgroup == 0)  {
			delete_records('dean_speciality', 'id', $sid);
			add_to_log(1, 'dean', 'Speciality deleted', 'delspeciality.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorindelspec','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
		}
		redirect("speciality.php?id=$fid", get_string('specialitydeleted','block_dean'), 3);
	}


	print_heading($strdelspeciality .' :: ' .$spec->name);

	notice_yesno(get_string('deletecheckfull', '', $spec->name),
               "delspeciality.php?fid=$fid&amp;sid=$sid&amp;confirm=1", "speciality.php?id=$fid");

	print_footer();
?>
