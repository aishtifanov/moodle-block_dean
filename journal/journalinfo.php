<?php // $Id: journalinfo.php,v 1.2 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    require_once('../lib_dean.php');    

    $gid = required_param('gid', PARAM_INT);          // Group id
    $fid = optional_param('fid', 0, PARAM_INT);      // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);      // Speciality id
    $cid = optional_param('cid', 0, PARAM_INT);		// Curriculum id

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
	$strjornalgroup = get_string('journalgroup','block_dean');

	$academygroup = get_record('dean_academygroups', 'id', $gid);

	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0)  {

	    $strfaculty = get_string('faculty','block_dean');
	    $strspeciality = get_string('speciality','block_dean');
		$strcurriculums = get_string('curriculums','block_dean');

	    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
		$breadcrumbs .= " -> $strjornalgroup";

	    print_header("$site->shortname: $strjornalgroup", "$site->fullname", $breadcrumbs);

		$admin_is = isadmin();
		$creator_is = iscreator();
  	    $methodist_is = ismethodist();

	    if (!$admin_is && !$creator_is && !$methodist_is) {
	        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
	    }

		// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
	    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_faculty("journalgroup.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("journalgroup.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("journalgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("journalgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
		echo '</table>';


	    $currenttab = 'juornalgroup';
	    include('../gruppa/tabsonegroup.php');

      	$curator = get_record('dean_curators', 'academygroupid', $gid);

      	$strurl = "&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid";

	} else  {

	    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
		$breadcrumbs .= " -> $strjornalgroup";

	    print_header("$site->shortname: $strjornalgroup", "$site->fullname", $breadcrumbs);

		$admin_is = isadmin();
		$creator_is = iscreator();
		$curator_is = iscurator();
  	    $methodist_is = ismethodist();

	    if (!$curator_is && !$admin_is  && !$creator_is && !$methodist_is) {
	        error(get_string('curatoraccess', 'block_dean'), '..\faculty\faculty.php');
	    }

    	$curator = get_record('dean_curators', 'userid', $USER->id, 'academygroupid', $gid);

	    if ($curator_is) {
			$gid = 	$curator->academygroupid;
		}

		print_heading($strjornalgroup.' '.$academygroup->name, 'center', 2);

		$strurl = '';
    }

    if ($curator) 	{
	  	$u = get_record('user', 'id', $curator->userid);
 	  	$curatorname = fullname($u);
 	} else {
 		$curatorname = '-';
 	}

	if (!$curriculum = get_record('dean_curriculum', 'id', $academygroup->curriculumid)) {
   		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
	}
	//$cid = $curriculum->id;

	if (!$speciality = get_record('dean_speciality', 'id', $academygroup->specialityid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
	}
	//$sid = $speciality->id;

	if (!$faculty = get_record('dean_faculty', 'id', $academygroup->facultyid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
    }

    $strfaculty     = get_string('faculty','block_dean');
	$strspeciality  = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
    $strdisciplines = get_string("disciplines","block_dean");

    $currenttabjournal ='journalinfo';
	include('tabsjournal.php');


    print_dean_box_start("center");
?>

<tr valign="top">
    <td align="right"><b><?php  print_string("ffaculty","block_dean") ?>:</b></td>
    <td align="left"> <?php p($faculty->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("sspeciality","block_dean") ?>:</b></td>
    <td align="left"> <?php echo $speciality->number.'&nbsp;'.$speciality->name ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("curriculum","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("enrolyear","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->enrolyear) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("formlearning","block_dean") ?>:</b></td>
    <td align="left"> <?php print_string("$curriculum->formlearning","block_dean")?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("qualification","block_dean") ?>:</b></td>
    <td align="left"> <?php p($speciality->qualification) ?> </td>
</tr>
<tr valign="top">
	<td colspan="2"><hr></td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string('curatorgroup','block_dean') ?>:</b></td>
    <td align="left"> <?php p($curatorname) ?> </td>
</tr>



<?php

    print_dean_box_end();

    print_footer();

?>