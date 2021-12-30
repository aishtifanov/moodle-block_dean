<?PHP // $Id: searchgroup.php,v 1.4 2011/12/19 12:35:43 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_search.php');    

    $namegroup = optional_param('namegroup', '');		// Group name (number)
    $gid = optional_param('gid', 0, PARAM_INT);
   	$action = optional_param('action', '');    
    
	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('searchgroup', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$SITE->shortname: $strsearchgroup", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    if ($action == 'restore' && $gid > 0)   {
        restore_dean_group($gid);
    }

    $searchtext = $namegroup;

    if (isset($namegroup) && !empty($namegroup)) 	{
        $table = table_groups($namegroup);
        print_table($table);
	}

	print_heading($strsearchgroup, 'center', 2);

    print_dean_box_start('center', '50%');
	echo '<div align=center><form name="studentform" id="studentform" method="post" action="searchgroup.php">'.
		 get_string('numgroup', 'block_dean'). '&nbsp&nbsp'.
		 '<input type="text" name="namegroup" size="10" value="' . $searchtext. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" /><br>'.
		 '</form></div>';
   	print_dean_box_end();
    echo '<p></p>';
    
    print_footer();


function table_groups($namegroup)
{
    global $CFG, $admin_is, $creator_is, $namegroup;
    
	$table->head  = array (get_string('group'), get_string("numofstudents","block_dean"), get_string("kurs","block_dean"), get_string("action","block_dean"));
	$table->align = array ("center", "center", "center", "center");
	$table->size = array ('20%', '10%', '10%', '10%');
	$table->width = '50%';

    $strsql = "SELECT id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
               FROM {$CFG->prefix}dean_academygroups
               WHERE name LIKE '$namegroup%'
    		   ORDER BY name";
	if ($ugroups1 = get_records_sql($strsql))	{

		foreach ($ugroups1 as $ugroup) {
			if 	($admin_is || $creator_is)	 {
				$title = get_string('editgroup','block_dean');
				$strlinkupdate = "<a title=\"$title\" href=\"addgroup.php?mode=edit&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deletegroup','block_dean');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delgroup.php?fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
			}
			else	{
				$strlinkupdate = '-';
			}

			$title = get_string('students','block_dean');
			$countsudents = count_records('dean_academygroups_members', 'academygroupid',  $ugroup->id);
			$table->data[] = array ("<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">$ugroup->name</a></strong>",
								$countsudents, get_kurs($ugroup->startyear), $strlinkupdate);
		}
	}
	else {
		notify(get_string('groupnotfound','block_dean'));
		echo '<hr>';
	}

    $strsql = "SELECT id, facultyid, specialityid, curriculumid, name, startyear, description
               FROM {$CFG->prefix}dean_academygroups_g
               WHERE name LIKE '$namegroup%'
    		   ORDER BY name";
	if ($ugroups1 = get_records_sql($strsql))	{
        $table->data[] = array ('<hr>', '<hr>', '<hr>', '<hr>');
		foreach ($ugroups1 as $ugroup) {
	        $titl = 'Восстановить';
    		$strlinkupdate = "<a title=\"$titl\" href=\"searchgroup.php?action=restore&gid=$ugroup->id&namegroup=$namegroup\">";
	        $strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/dean/i/btn_move.png\" alt=\"$titl\" /></a>&nbsp;";

			$title = get_string('students','block_dean');
			$countsudents = count_records('dean_academygroups_members_g', 'academygroupid',  $ugroup->id);
			$table->data[] = array ("<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember_g.php?mode=4&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">$ugroup->name</a></strong>",
								$countsudents, 'выбывшая', $strlinkupdate);
		}
	}

    return $table;
}


function restore_dean_group($gid_g)
{
    if (!$agroup_g = get_record_select ('dean_academygroups_g', "id = $gid_g"))  {
        notify ("Группа с id = $gid_g не найдена в списке выбывших групп.");
        return false;
    } 
    
    
    if ($agroup = get_record_select ('dean_academygroups', "name = '$agroup_g->name'", 'id, name'))  {
        notify ("Группа $agroup_g->name уже существует в системе.");
    }  else {

        if ($newid = insert_record('dean_academygroups', $agroup_g))    {
            notify ("Создана учетная запись группы $agroup_g->name.", 'green');
            $agroup = get_record_select ('dean_academygroups', "id = $newid", 'id, name');
        } else {
            notify ("Учетная запись группы $agroup_g->name не создана. Группа не восстановлена.");
            return false;
        }
    }  

    if ($amembers = get_records_select('dean_academygroups_members_g', "academygroupid=$gid_g"))    {
        foreach ($amembers as $amember) {
            // add_student_to_dean_group($amember->userid, $agroup);
            if ($userid = restore_user_account($amember->id)) {
                restore_dean_student($userid, $agroup);
            }    
        }
    }
    
    delete_records_select ('dean_academygroups_g', "id = $gid_g");
    delete_records_select('dean_academygroups_members_g', "academygroupid=$gid_g");
    
}
?>