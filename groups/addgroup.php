<?PHP // $Id: addgroup.php,v 1.3 2011/09/14 07:40:59 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $mode = required_param('mode');    // new, add, edit, update  PARAM_ALPHA
    $fid = required_param('fid', PARAM_INT);        // Faculty id
	$sid = required_param('sid', PARAM_INT);		// Speciality id
	$cid = required_param('cid', PARAM_INT);		// Curriculum id
	$gid = optional_param('gid', 0, PARAM_INT);		// Academygroup ID


	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');

    if ($mode === "new" || $mode === "add" ) $straddgroup = get_string('addgroup','block_dean');
	else $straddgroup = get_string('updategroup','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid\">$strgroups</a>";
	$breadcrumbs .= " -> $straddgroup";

    print_header("$site->shortname: $straddgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }

	if (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
 	}

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
 	}

	$rec->facultyid = $fid;
	$rec->specialityid = $sid;
	$rec->curriculumid = $cid;
	$rec->name = "";
	$rec->startyear = 1;
	$rec->description = "";
    $rec->idotdelenie = 0;

	if ($mode === 'add')  {
	    $namegroup = required_param('name');
		$rec->name = trim($namegroup);
		$rec->startyear =  required_param('startyear');
		$rec->description = required_param('description');
        $rec->idotdelenie = required_param('idotdelenie', PARAM_INT);

		if (find_form_group_errors($rec, $err) == 0) {
		    // $rec->idotdelenie =  get_idotdelenie_group($rec->name);
			$rec->timemodified = time();
			if (insert_record('dean_academygroups', $rec))	{
				 add_to_log(1, 'dean', 'one academygroup added', "blocks/dean/groups/addgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('groupadded','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
			} else
				error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($gid > 0) 	{
			$group = get_record('dean_academygroups', 'id', $gid);
			$rec->id = $group->id;
			$rec->name = $group->name;
			$rec->startyear = $group->startyear;
			$rec->description = $group->description;
            $rec->idotdelenie = $group->idotdelenie;
		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('groupid', PARAM_INT);
		$rec->name = required_param('name');
		$rec->startyear=  required_param('startyear');
		$rec->description = required_param('description');
        $rec->idotdelenie = required_param('idotdelenie', PARAM_INT);

		if (find_form_group_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			$group = get_record('dean_academygroups', 'id', $rec->id);
			if (update_record('dean_academygroups', $rec))	{
			     if ($group->name != $rec->name)	 {
			         $strsql = 'UPDATE '. $CFG->prefix . 'groups SET name = \''. $rec->name .'\' WHERE name = \''. $group->name .'\'';
			         if (!($rs = $db->Execute($strsql))) {
					   error(get_string('errorinupdatingnamegroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
   				     }
			     }
				 add_to_log(1, 'dean', 'academygroup update', "blocks/dean/groups/addgroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('groupupdate','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
			} else
                $msg = get_string('errorinupdatinggroup','block_dean');
                if (record_exists_select('dean_academygroups', "name = '$rec->name'"))    {
                    $msg .= "! Группа с именем $rec->name уже существует в системе. Переименование невозможно."; 
                } 
				error($msg, "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
		}
	}

	print_heading($straddgroup, "center", 3);

    print_dean_box_start("center");
?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addgroup.php?mode=add&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid";
												else  echo "addgroup.php?mode=update&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid";?>">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string("ffaculty","block_dean") ?>:</b></td>
    <td align="left"> <?php p($faculty->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("sspeciality","block_dean") ?>:</b></td>
    <td align="left"> <?php p($speciality->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("curriculum","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("name") ?>:</b></td>
    <td align="left">
		<input type="text" id="name" name="name" size="70" value="<?php p($rec->name) ?>" />
		<?php if (isset($err["name"])) formerr($err["name"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string('enrolyear','block_dean') ?>:</b></td>
    <td align="left">  <?php    unset($choices);

					$curryear = date("Y");
					for ($i=1; $i<=10; $i++)  {
						$j = $curryear;
					    $choices[$j] = $j;
						$curryear--;
					}
				    choose_from_menu ($choices, "startyear", $rec->startyear, "");
	/*
					for ($i=1; $i<=6; $i++)  {
					    $choices[$i] = $i;
					}
				    choose_from_menu ($choices, "kurs", $rec->kurs, "");
				    if (isset($err["kurs"])) formerr($err["kurs"]);
	*/
				    ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string('otdelenie','block_dean') ?>:</b></td>
    <td align="left">  <?php    unset($choices);

                    $choices[0] = '-';
                    $otdelenies = get_records_select('dean_otdelenie', '', 'id, idotdelenie, name');
                    foreach ($otdelenies as $otdelenie) {
                        $choices[$otdelenie->idotdelenie] = $otdelenie->name;
                    }    

				    choose_from_menu ($choices, "idotdelenie", $rec->idotdelenie, "");
				    ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("description") ?>:</b></td>
    <td align="left">
		<input type="text" id="description" name="description" size="70" value="<?php p($rec->description) ?>" />
    </td>
</tr>
</table>
   <div align="center">
     <input type="hidden" name="groupid" value="<?php p($gid)?>">
 	 <input type="submit" name="addcurr" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_dean_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_group_errors(&$rec, &$err, $mode='add') {

    if (empty($rec->startyear)) {
        $err["startyear"] = get_string("missingname");
	}
    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}

    return count($err);
}

?>