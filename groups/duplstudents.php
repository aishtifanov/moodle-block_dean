<?PHP // $Id: duplstudents.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

	$numcurr = optional_param('numcurr', 0, PARAM_INT);	// # KOL_CURRICULUM
	$cid = optional_param('cid', 0, PARAM_INT);	// # CURRICULUM ID

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('duplstudents', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$site->shortname: $strsearchgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


	$strsql = "SELECT mdl_dean_academygroups_members.userid, Count(mdl_dean_academygroups_members.userid) Countuserid
			   FROM mdl_dean_academygroups_members
			   GROUP BY mdl_dean_academygroups_members.userid
			   HAVING (((Count(mdl_dean_academygroups_members.userid))>1))";

	if ($duplausers = get_records_sql($strsql))   {
	    foreach ($duplausers as $duplauser)		{	    	$agrids = get_records_sql("SELECT id, academygroupid FROM mdl_dean_academygroups_members WHERE userid={$duplauser->userid}");
    		$user   = get_record_sql("SELECT id, lastname, firstname FROM mdl_user WHERE id={$duplauser->userid}");
    		$fn = fullname($user);

	    	foreach ($agrids as $agrid) 	{				$agroup = get_record_sql("SELECT id, facultyid, specialityid, curriculumid, name FROM mdl_dean_academygroups WHERE id={$agrid->academygroupid}");
   				$table->data[] = array ($fn, "<strong><a href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>");
			}	    }
	}

/*

	$strsql = "SELECT mdl_dean_academygroups_members.userid, mdl_dean_academygroups_members.academygroupid, mdl_dean_academygroups_members.id
			   FROM mdl_dean_academygroups_members
			   WHERE (((mdl_dean_academygroups_members.userid) In (SELECT userid FROM mdl_dean_academygroups_members As Tmp GROUP BY userid HAVING Count(*)>1)))
			   ORDER BY mdl_dean_academygroups_members.userid";
	if ($duplagroups = get_records_sql($strsql))   {

		$table->head  = array (get_string('fullname'), get_string('academygroup','block_dean'), '', '');
		$table->align = array ("left", "left", "center", "center");

    	foreach($duplagroups as $duplagroup)	{
    		$agroup = get_record_sql("SELECT id, facultyid, specialityid, curriculumid, name FROM mdl_dean_academygroups WHERE id={$duplagroup->academygroupid}");
    		$user   = get_record_sql("SELECT id, lastname, firstname FROM mdl_user WHERE id={$duplagroup->userid}");
   			$table->data[] = array (fullname($user),
   									"<strong><a href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>");
		}
   	}
*/
   	print_table($table);
    print_footer();

?>