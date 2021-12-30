<?php // $Id: academygroup.php,v 1.3 2013/09/03 14:01:07 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$cid = required_param('cid', PARAM_INT);		  // Curriculum id

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	if ($fid == 0 && $sid != 0)  {
       error(get_string("errorselect","block_dean"), "academygroup.php?mode=1&amp;fid=0&amp;sid=0&amp;cid=0");
	}

	if ($fid == 0 && $cid != 0)  {
       error(get_string("errorselect","block_dean"), "academygroup.php?mode=1&amp;fid=0&amp;sid=0&amp;cid=0");
	}

	if ($sid == 0 && $cid != 0)  {
       error(get_string("errorselectspec","block_dean"), "academygroup.php?mode=1&amp;fid=$fid&amp;sid=0&amp;cid=0");
	}

	if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }

	if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	}
	elseif (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
    }

	if ($cid == 0)  {
	   $curriculum = get_record_sql("SELECT * FROM {$CFG->prefix}dean_curriculum", true);
	}
	elseif (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
		error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
    }

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $strgroups";

    print_header("$site->shortname: $strgroups", "$site->fullname", $breadcrumbs);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("academygroup.php?mode=1&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("academygroup.php?mode=2&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("academygroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=", $fid, $sid, $cid);
	echo '</table>';

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();

    $choices[0] = '-';
    $otdelenies = get_records_select('dean_otdelenie', '', 'id, idotdelenie, name');
    foreach ($otdelenies as $otdelenie) {
        $choices[$otdelenie->idotdelenie] = $otdelenie->name;
    }    

	if ($fid != 0 && $sid != 0 && $cid != 0 && $mode > 2)  {

	    $currenttab = 'listgroups';
	    include('tabsgroups.php');

		$table->head  = array (get_string("course","block_dean"), get_string('group'), get_string("numofstudents","block_dean"), 
                               get_string('otdelenie','block_dean'), get_string('disciplines','block_dean'),
                                get_string("action","block_dean"));
		$table->align = array ("center", "center", "center", "center", "center","center");
        $table->size = array ("3%", "10%", "5%", "5%", "5%", "7%");
        $table->width = "50%"; 

		$ugroups1 = get_records_sql ("SELECT *
									  FROM {$CFG->prefix}dean_academygroups
									  WHERE facultyid=$fid AND specialityid=$sid AND curriculumid=$cid
									  ORDER BY name DESC");
		if ($ugroups1)
		foreach ($ugroups1 as $ugroup) {
			if 	($admin_is || $creator_is)	 {
				$title = get_string('editgroup','block_dean');
				$strlinkupdate = "<a title=\"$title\" href=\"addgroup.php?mode=edit&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deletegroup','block_dean');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delgroup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";

				$title = get_string('movegraduategroup','block_dean');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"movegraduategroup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/removeright.gif\" alt=\"$title\" /></a>&nbsp;";

			// $strgroup = "<strong><a href=$CFG->wwwroot/blocks/dean/disciplines.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}>$curr->name</a></strong>";
			}
			else	{
				$strlinkupdate = '-';
			}

            $countdiscipline = count_records("groups", "name", $ugroup->name);
            
            $strotdelenie = $choices[$ugroup->idotdelenie] . ' ф.о.';
			$title = get_string('templatecertificate','block_dean');
			$countsudents = count_records('dean_academygroups_members', 'academygroupid',  $ugroup->id);
			$table->data[] = array (get_kurs($ugroup->startyear), 
                                    "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">$ugroup->name</a></strong><br />($ugroup->oldname)" ,
									$countsudents,  $strotdelenie, $countdiscipline, $strlinkupdate);
		}

		print_table($table);
		if 	($admin_is || $creator_is)	 {

?>
			<table align="center">
				<tr align="center">
				<td>
				     <form name="form2" id="form2" method="post" action="<?php echo "addgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid"; ?>">
					   <input type="submit" name="newgroups" value="<?php print_string('addgroup','block_dean') ?>"/>
			         </form>
				</td>
				<td>
				  <form name="delallgroup" method="post" action="delallgroups.php">
					    <div align="center">
						<input type="hidden" name="fid" value="<?php echo $fid ?>"/>
				 		<input type="hidden" name="sid" value="<?php echo $sid ?>"/>
						<input type="hidden" name="cid" value="<?php echo $cid ?>"/>
						<input type="submit" name="delalldiscipline" value="<?php print_string('deletinggroups','block_dean')?>">
				  	    </div>
				  </form>
			    </td></tr>
			 </table>

<?php
		}
	}

    print_footer();

?>


