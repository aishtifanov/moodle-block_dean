<?PHP // $Id: deldiscipline.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once("../../../config.php");

    $fid 	= required_param('fid', PARAM_INT);         // Faculty id
    $sid 	= required_param('sid', PARAM_INT);         // Speciality id
	$cid 	= required_param('cid', PARAM_INT);			// Curriculum id
	$did 	= required_param('did', PARAM_INT);			// Discipline id
	$kid 	= required_param('kid', PARAM_INT);			// Course id
	$confirm = optional_param('confirm');

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
    $strdisciplines = get_string("disciplines","block_dean");
    $strdeldiscipline = get_string('deletingdiscipline','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= "-> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= "-> <a href=\"disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid\"> $strdisciplines </a>";
	$breadcrumbs .= "-> $strdeldiscipline";

    print_header("$site->shortname: $strdeldiscipline", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$curr = get_record('dean_discipline', 'id', $did)) {
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
	}

	if (isset($confirm)) {
		delete_records('dean_discipline', 'id', $did);
		add_to_log(1, 'dean', 'Discipline deleted', 'deldiscipline.php', $USER->lastname.' '.$USER->firstname);
		redirect("$CFG->wwwroot/blocks/dean/curriculum/disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid", get_string('disciplinedeleted','block_dean'), 3);
	}

	$acourse = get_record("course", "id", $kid);

	print_heading(get_string('deleteddiscipline','block_dean') .' :: ' .$acourse->fullname);

	notice_yesno(get_string('deletecheckfull', '', $acourse->fullname),
               "deldiscipline.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;did=$did&amp;kid=$kid&amp;confirm=1", "disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid");

	print_footer();
?>
