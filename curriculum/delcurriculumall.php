<?PHP // $Id: delcurriculum.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once("../../../config.php");


    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
    $strcurriculums = get_string("curriculums","block_dean");
    $strdelcurriculum = get_string('deletingcurriculum','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> $strdelcurriculum";

    print_header("$SITE->shortname: $strdelcurriculum", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();

    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$currall = get_records('dean_curriculum', 'formlearning', 'daytimeformtraining')) {
		error(get_string('errorcurriculum', 'block_dean'), '..\faculty\faculty.php');
	}

	foreach ($currall as $curr)	{
		$cid = $curr->id;
		delete_records('dean_discipline', 'curriculumid', $cid);
		delete_records('dean_academygroups', 'curriculumid', $cid);
		delete_records('dean_curriculum', 'id', $cid);
		echo get_string('deletecheckfull', '', $curr->name) . $curr->formlearning . ' YES!<hr>';
	}		

	print_footer();
?>
