<?PHP // $Id: addcurriculum.php,v 1.2 2009/09/02 12:08:29 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);          // Faculty id
	$sid = required_param('sid', PARAM_INT);			// Speciality id
	$cid = optional_param('cid', 0, PARAM_INT);			// Curriculum id

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

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


    $strfaculty = get_string('faculty','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');

    if ($mode === "new" || $mode === "add" ) $straddcurr = get_string('addcurriculum','block_dean');
	else $straddcurr = get_string('updatecurriculum','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $straddcurr";
    print_header("$site->shortname: $straddcurr", "$site->fullname", $breadcrumbs);

	$rec->facultyid = $fid;
	$rec->specialityid = $sid;
	$rec->code = 0;
	$rec->name = "";
	$rec->enrolyear = "";
	$rec->formlearning = "";
	$rec->description = "";

	if ($mode === 'add')  {
		$rec->code = required_param('code');
		$rec->name = required_param('name');
		$rec->enrolyear =  required_param('enrolyear');
		$rec->formlearning = required_param('formlearning');
		$rec->description = required_param('description');

		if (find_form_curr_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if (insert_record('dean_curriculum', $rec))	{
				 // add_to_log(1, 'dean', 'one curriculum added', "blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('curriculumadded','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");
			} else
				error(get_string('errorinaddingcurr','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($cid > 0) 	{
			$curr = get_record('dean_curriculum', 'id', $cid);
			$rec->id = $curr->id;
			$rec->code = $curr->code;
			$rec->name = $curr->name;
			$rec->enrolyear = $curr->enrolyear;
			$rec->formlearning = $curr->formlearning;
			$rec->description = $curr->description;
		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('currid', PARAM_INT);
		$rec->code = required_param('code');
		$rec->name = required_param('name');
		$rec->enrolyear =  required_param('enrolyear');
		$rec->formlearning = required_param('formlearning');
		$rec->description = required_param('description');

		if (find_form_curr_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if (update_record('dean_curriculum', $rec))	{
				 add_to_log(1, 'dean', 'curriculum update', "blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('curriculumupdate','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid");
			} else
				error(get_string('errorinupdatingcurr','block_dean'), "$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$fid");
		}
	}

	// print_heading($faculty->name);
//	print_heading($speciality->name);
	print_heading($straddcurr, "center", 3);

    print_dean_box_start("center");
?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addcurriculum.php?mode=add&amp;fid=$fid&amp;sid=$sid";
												else  echo "addcurriculum.php?mode=update&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid";?>">
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
    <td align="right"><b><?php  print_string("codecurriculum","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="code" name="code" size="10" value="<?php p($rec->code) ?>" readonly />
		<?php if (isset($err["code"])) formerr($err["code"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("name") ?>:</b></td>
    <td align="left">
		<input name="name" type="text" id="name" value="<?php p($rec->name) ?>" size="70" />
		<?php if (isset($err["name"])) formerr($err["name"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("enrolyear","block_dean") ?>:</b></td>
    <td align="left">  <?php    unset($choices);
					$curryear = date("Y")+1;
					for ($i=1; $i<=10; $i++)  {
						$j = ($curryear-1).'/'.$curryear;
					    $choices[$j] = $j;
						$curryear--;
					}
					$choices['n_oformtraining'] = get_string('n_oformtraining', 'block_dean');
				    choose_from_menu ($choices, "enrolyear", $rec->enrolyear, "");
				    if (isset($err["enrolyear"])) formerr($err["enrolyear"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("formlearning","block_dean") ?>:</b></td>
    <td align="left">  <?php    unset($choices);
				    $choices["daytimeformtraining"] = get_string("daytimeformtraining", "block_dean");
				    $choices["eveningformtraining"] = get_string("eveningformtraining", "block_dean");
				    $choices["correspondenceformtraining"] = get_string("correspondenceformtraining", "block_dean");
					$choices["acceleratedformtraining"] = get_string("acceleratedformtraining", "block_dean");
					$choices["poformtraining"] = get_string("poformtraining", "block_dean");
					$choices["n_oformtraining"] = get_string("n_oformtraining", "block_dean");
				    choose_from_menu ($choices, "formlearning", $rec->formlearning, "");
				    if (isset($err["formlearning"])) formerr($err["formlearning"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("description") ?>:</b></td>
    <td align="left">
		<input type="text" id="description" name="description" size="70" value="<?php p($rec->description) ?>" />
		<?php if (isset($err["qualification"])) formerr($err["qualification"]); ?>
    </td>
</tr>
</table>
   <div align="center">
     <input type="hidden" name="currid" value="<?php p($cid)?>">
 	 <input type="submit" name="addcurr" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_dean_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_curr_errors(&$rec, &$err, $mode='add') {

		/*
        if (empty($rec->code)) {
            $err["code"] = get_string("missingname");
		}

		else if (!ctype_digit($rec->code))  {
            $err["code"] = get_string("errordigit",'block_dean');
		}
		*/
        if (empty($rec->name))	{
		    $err["name"] = get_string("missingname");
		}

    return count($err);
}

?>