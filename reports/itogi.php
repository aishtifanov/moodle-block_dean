<?php // $Id: itogi.php,v 1.1.1.1 2009/10/29 08:23:06 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $cid = required_param('cid', PARAM_INT);
    $sid = required_param('sid', PARAM_INT);
	$tabroll = optional_param('tabroll', 'itogisessii', PARAM_ALPHA);



    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string('speciality','block_dean');
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
    $strrolls = get_string("rolls","block_dean");

    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	//$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	//$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=1&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strgroup";


    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	$methodist_is = ismethodist();

    if ($USER->id == 59682) {
        $admin_is = true;
    } 

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }


	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("reports.php?mode=1&amp;gid=$gid&amp;cid=$cid&amp;sid=$sid&amp;fid=", $fid);
    listbox_group_allfaculty("reports.php?mode=2&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $gid);
	echo '</table>';

    if ($fid != 0 && $gid != 0 && $mode >= 2) {

      //  $currenttab = 'roll';
   	   // include('../gruppa/tabsonegroup.php');

	    $toprow = array();
  	    $toprow[] = new tabobject('poseshaemost', $CFG->wwwroot."/blocks/dean/rolls/rollofdiscipline.php?mode=2&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;tabroll=zaschetexam",
                get_string('poseshaemost', 'block_dean'));
   		$toprow[] = new tabobject('tekushuspevaemost', $CFG->wwwroot."/blocks/dean/rolls/roll.php?mode=2&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;tabroll=retake",
       	        get_string('tekushuspevaemost', 'block_dean'));
   		$toprow[] = new tabobject('itogisessii', $CFG->wwwroot."/blocks/dean/reports/itogi.php?mode=2&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;tabroll=itogisessii",
       	        get_string('itogisessii', 'block_dean'));
        $tabs = array($toprow);
        print_tabs($tabs, $tabroll, NULL, NULL);




    }
    print_footer();


?>


