<?php // $Id: examprint.php,v 1.2 2011/02/08 11:26:30 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';
    
    require_login();
    
    $idc = optional_param('idc', 0, PARAM_INT);    // id course
    $gid = optional_param('gid', 0, PARAM_INT);    // Group id 

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
		
	if (!$course = get_record('course', 'id', $idc)) {
		print_error('nocourseid', 'error', "../index.php");
	}
	
    /*
	$agroup = get_record_select('dean_academygroups', "id = $gid", 'id, name');
	if ($mgroup = get_record_select('groups', "courseid=$idc and name = $agroup->name", 'id, name'))	{
		$groupid = $mgroup->id;
	} else {
		notice ("Group not found", "../index.php");
	}
    */	
    $groupid = $gid;

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
?>