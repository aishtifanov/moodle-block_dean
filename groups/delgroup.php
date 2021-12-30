<?PHP // $Id: delgroup.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once("../../../config.php");

    $fid 	= required_param('fid', PARAM_INT);         // Faculty id
    $sid 	= required_param('sid', PARAM_INT);         // Speciality id
	$cid 	= required_param('cid', PARAM_INT);			// Curriculum id
	$gid 	= required_param('gid', PARAM_INT);			// Discipline id
	$confirm = optional_param('confirm');

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');
	$strdelgroup = get_string('deletegroup','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid\">$strgroups</a>";
	$breadcrumbs .= " -> $strdelgroup";

    print_header("$site->shortname: $strdelgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Academy group not found!");
	}

	if (isset($confirm)) {
		$countsudents = count_records('dean_academygroups_members', 'academygroupid',  $academygroup->id);
		if ($countsudents == 0)		{
			delete_records('dean_academygroups', 'id', $gid);
			add_to_log(1, 'dean', 'academy group deleted', 'delgroup.php', $USER->lastname.' '.$USER->firstname);
		}
		else	{
			error(get_string('errorindelgroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
		}
		redirect("$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", get_string('groupdeleted','block_dean'), 3);
	}


	print_heading(get_string('deletinggroup','block_dean') .' :: ' .$academygroup->name);

	notice_yesno(get_string('deletecheckfull', '', $academygroup->name),
               "delgroup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;confirm=1", "academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");

	print_footer();
?>
