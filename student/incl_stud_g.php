<?php // $Id: incl_stud_g.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
    require_once('../lib.php');

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
  	$strcurriculums = get_string('curriculums','block_dean');
  	$strgroups = get_string('groups');
  	$strstudent = get_string("student","block_dean");

    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strstudent";

    print_header("$site->shortname: $strstudent", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$teacher_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("student_g.php?mode=1&amp;gid=$gid&amp;cid=$cid&amp;sid=$sid&amp;fid=", $fid);
    listbox_speciality("student_g.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("student_g.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    //listbox_group("student_g.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid);
  //  listbox_student("student_g.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid=", $fid, $sid, $cid, $gid, $uid);
	echo '</table>';





?>


