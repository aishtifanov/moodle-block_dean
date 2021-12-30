<?PHP // $Id: addspeciality.php,v 1.3 2009/10/19 12:23:04 Shtifanov Exp $

    require_once('../../../config.php');
	require_once('../lib.php');    

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);          // Faculty id
	$sid = optional_param('sid', 0, PARAM_INT);			// Speciality id

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    if ($mode === "new" || $mode === "add" )
         $straddspec = get_string('addspeciality','block_dean');
	else $straddspec = get_string('updatespeciality','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
    $strfaculty = get_string('faculty','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"speciality.php?id=0\">$strspeciality</a> -> $straddspec";
    print_header("$site->shortname: $straddspec", $site->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    if (!$faculty = get_record('dean_faculty', 'id', $fid)) {
	    error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
	}

	$rec->facultyid = $fid;
	$rec->number = "";
	$rec->name = "";
	$rec->qualification = "";

	if ($mode === 'add')  {
		$rec->number = required_param('numf');
		$rec->name = required_param('name');
		$rec->qualification = required_param('qualification');

		if (find_form_spec_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if (insert_record('dean_speciality', $rec))	{
				 add_to_log(1, 'dean', 'one speciality added', "blocks/dean/speciality/speciality.php?id=$fid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('specialityadded','block_dean'), "$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$fid");
			} else  {
				error(get_string('errorinaddingspec','block_dean'), "$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$fid");
			}
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($sid > 0) 	{
			$spec = get_record('dean_speciality', 'id', $sid);
			$rec->id = $spec->id;
			$rec->number = $spec->number;
			$rec->name = $spec->name;
			$rec->qualification = $spec->qualification;
		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('specid', PARAM_INT);
		$rec->number = required_param('numf');
		$rec->name = required_param('name');
		$rec->qualification = required_param('qualification');

		if (find_form_spec_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if (update_record('dean_speciality', $rec))	{
				 add_to_log(1, 'dean', 'speciality update', "blocks/dean/speciality/speciality.php?id=$fid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('specialityupdate','block_dean'), "$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$fid");
			} else
				error(get_string('errorinupdatingspec','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
		}
	}

	print_heading($straddspec, "center", 4);

    print_dean_box_start("center");
?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addspeciality.php?mode=add&amp;fid=$fid";
												else  echo "addspeciality.php?mode=update&amp;fid=$fid&amp;sid=$sid";?>">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string("ffaculty","block_dean") ?>:</b></td>
    <td align="left"> <?php p($faculty->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("numbersp","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="numf" name="numf" size="10" value="<?php p($rec->number) ?>" />
		<?php if (isset($err["number"])) formerr($err["number"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("name") ?>:</b></td>
    <td align="left">
		<input type="text" id="name" name="name" size="70" value="<?php p($rec->name) ?>" />
		<?php if (isset($err["name"])) formerr($err["name"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("qualification","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="qualification" name="qualification" size="70" value="<?php p($rec->qualification) ?>" />
		<?php if (isset($err["qualification"])) formerr($err["qualification"]); ?>
    </td>
</tr>
</table>
   <div align="center">
     <input type="hidden" name="specid" value="<?php p($sid)?>">
 	 <input type="submit" name="addspec" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_dean_box_end();

	print_footer($course);


/// FUNCTIONS ////////////////////
function find_form_spec_errors(&$rec, &$err, $mode='add') {

        if (empty($rec->number)) {
            $err["number"] = get_string("missingname");
		}
        if (empty($rec->name))	{
		    $err["name"] = get_string("missingname");
		}
		/*
        if (empty($rec->qualification))	{
			$err["qualification"] = get_string("missingname");
		}
		*/

    return count($err);
}

?>
