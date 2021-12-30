<?PHP // $Id: listgraduategroup.php,v 1.2 2009/10/19 12:23:04 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $namegroup = optional_param('namegroup', '');		// Group name (number)

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strgraduategroups = get_string('graduategroups', 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strgraduategroups";
    print_header("$site->shortname: $strgraduategroups", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
    $methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
       error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

//    if (isset($namegroup) && !empty($namegroup)) 	{
	    							// WHERE name LIKE '$namegroup%'
	    $ugroups1 = get_records_sql("SELECT * FROM {$CFG->prefix}dean_academygroups_g
	    							ORDER BY name");
        $totalstudent = 0;

		if ($ugroups1)	{

			$table->head  = array (get_string('group'), get_string("numofstudents","block_dean"), get_string("kurs","block_dean"), get_string("action","block_dean"));
			$table->align = array ("center", "center", "center", "center");

			foreach ($ugroups1 as $ugroup) {
				if 	($admin_is)	 {
/*
					$title = get_string('editgroup','block_dean');
					$strlinkupdate = "<a title=\"$title\" href=\"addgroup.php?mode=edit&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
*/
					$title = get_string('deletegroup','block_dean');
				    $strlinkupdate = "<a title=\"$title\" href=\"delgroup.php?fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
				}
				else	{
					$strlinkupdate = '-';
				}

				$title = get_string('students','block_dean');
				$countsudents = count_records('dean_academygroups_members', 'academygroupid',  $ugroup->id);
				$totalstudent += $countsudents;
				$table->data[] = array ("<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">$ugroup->name</a></strong>",
									$countsudents, get_kurs($ugroup->startyear), $strlinkupdate);
			}

			print_heading(get_string('countgraduategroups','block_dean').' '.count($ugroups1), 'left', 4);
			print_heading(get_string('countgraduatestudents','block_dean').' '.$totalstudent, 'left', 4);
			print_table($table);
		}
		else {
			notify(get_string('groupnotfound','block_dean'));
			echo '<hr>';
		}
//	}

   print_footer();

?>