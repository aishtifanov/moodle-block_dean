<?PHP // $Id: dialogue.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once("../../../config.php");

    $fid    = required_param('fid', PARAM_INT);         // Faculty id
    $sid    = required_param('sid', PARAM_INT);         // Speciality id
	$cid 	= required_param('cid', PARAM_INT);			// Curriculum id
	$confirm = optional_param('confirm');

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
    $strcurriculums = get_string("curriculums","block_dean");
    $strdelcurriculum = get_string('copiingcurriculum','block_dean');


    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $strdelcurriculum";

    print_header("$site->shortname: $strdelcurriculum", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$curr = get_record('dean_curriculum', 'id', $cid)) {
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
	}

	if (isset($confirm)) {
		$countdiscipline = count_records('dean_discipline', 'curriculumid', $cid);
		$countgroups 	 = count_records('dean_academygroups', 'curriculumid', $cid);

		if ($countdiscipline == 0 && $countgroups == 0)  {
			delete_records('dean_curriculum', 'id', $cid);
			add_to_log(1, 'dean', 'Curriculum deleted', 'delcurriculum.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorindelcurr','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");
		}
		redirect("$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid", get_string('curriculumdeleted','block_dean'), 3);
	}


	print_heading($strdelcurriculum .' :: ' .$curr->name);

	notice_yesno(get_string('copycheckfull','block_dean', $curr->name),
               "clonecurriculum.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;confirm=1", "curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");

	print_footer();
?>
