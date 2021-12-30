<?php // $Id: examtoexec.php,v 1.4 2011/02/08 11:26:30 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';

    require_login();
        
	$action = optional_param('action', '');
    $fid = optional_param('fid', 0, PARAM_INT);    // Faculty id
    $gid = optional_param('gid', 0, PARAM_INT);    // Group id
    $did = optional_param('did', 0, PARAM_INT); // Discipline id

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($action == 'excel')	{
		
		$discipline = get_record_select('dean_discipline', "id = $did", 'id, courseid');
		if (!$course = get_record('course', 'id', $discipline->courseid)) {
			print_error('nocourseid', 'error', "../index.php");
		}
		
		$agroup = get_record_select('dean_academygroups', "id = $gid", 'id, name');
		if ($mgroup = get_record_select('groups', "courseid=$course->id and name = $agroup->name", 'id, name'))	{
			$groupid = $mgroup->id;
		} else {
			notice ("Group not found", "../index.php");
		}	

		$arrayids = array();
		if ($gradeitems = get_records_select ('grade_items', "courseid=$course->id and (itemname like '%экзам%' or itemname like '%итог%')", '', 'id, courseid, itemname, iteminstance')) {
			foreach ($gradeitems as $gradeitem)	{
				$arrayids[] = $gradeitem->id;
			}
		}		
		$itemids = implode(',', $arrayids);

		$export_feedback = 0;
		$updatedgradesonly = 0;
		$displaytype = 1;
		$decimalpoints = 2;
		
		// print all the exported data here
		$export = new grade_export_xls($course, $groupid, $itemids, $export_feedback, $updatedgradesonly, $displaytype, $decimalpoints);
		// print_r($export);
		$export->print_grades();
		exit;
	}	

	$strtitle = get_string('examtoexec','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examtoexec';
    include('tabs.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("examtoexec.php?gid=$gid&amp;did=$did&amp;fid=", $fid);
    listbox_group_allfaculty("examtoexec.php?fid=$fid&amp;did=$did&amp;gid=", $fid, $gid, false);
	listbox_discipline("examtoexec.php?fid=$fid&amp;gid=$gid&amp;did=", $fid, 0, 0, $gid, $did);
	echo '</table>';

    if ($fid != 0 && $gid != 0 && $did != 0) {

		print_dean_box_start('center');
		
		$discipline = get_record_select('dean_discipline', "id = $did", 'id, courseid');
		if (!$course = get_record('course', 'id', $discipline->courseid)) {
			print_error('nocourseid', 'error', "../index.php");
		}

		if ($gradeitems = get_records_select ('grade_items', "courseid=$course->id and (itemname like '%экзам%' or itemname like '%итог%')", '', 'id, courseid, itemname, iteminstance')) {
			foreach ($gradeitems as $gradeitem)	{
				notify($gradeitem->itemname, 'green');
			}
			$count = count($gradeitems);
			if ($count > 1)	{
				notify('Внимание! В дисциплине найдено более одного экзаменационного или итогового теста!');
			}	
			
			echo  '<form name="allgroups" method="post" action="examtoexec.php">';
			echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
			echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
			echo  '<input type="hidden" name="did" value="'.$did.'" />';
			echo  '<input type="hidden" name="action" value="excel" />';
			echo  '<div align="center">';
			echo  '<input type="submit" name="mark" value="';
	  		print_string('downloadexcel');
			echo '"></div></form>';
			
		} else {
			notify('В дисциплине не найден экзаменационный или итоговый тест!');
		}	
		
        print_dean_box_end();
        
   }

   print_footer();


/*
$array = array(1, 2, 5, 7, 4);
echo max($array); // 7
echo max_key($array); // 3
*/

?>