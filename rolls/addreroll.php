<?PHP // $Id: addreroll.php,v 1.2 2011/03/22 06:56:09 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
	$fid = required_param('fid', PARAM_INT);
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $did = required_param('did', PARAM_INT);          // Discipline id
    $rid = required_param('rid', PARAM_INT);          // Roll id
	$retake = optional_param('retake', 1, PARAM_INT);

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }


    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';


    if ($mode === "new" || $mode === "add" )
    	  $straddreroll = get_string('addreroll','block_dean');
	else  $straddreroll = get_string('updatereroll','block_dean');
	$strreroll = get_string('reroll','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=retake\">$strreroll</a> -> $straddreroll";
    print_header("$site->shortname: $straddreroll", $site->fullname, $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), "roll.php");
    }

    $rec->teacherid = 0;
    $rec->studentid = 0;
	$rec->disciplineid = $did;
    $rec->academygroupid = $gid;
	$rec->teacherid = 0;
	$rec->datoftake = "";
	$rec->datofgive = "";
	$rec->teachername = "";


	if ($mode === 'add')  {
		$rec->disciplineid = $did;
        $rec->academygroupid = $gid;
        $rec->teacherid = required_param('teacherid');
		$rec->datoftake = required_param('datoftake');
		$rec->datofgive = required_param('datofgive');
        $rec->studentid = required_param('studentid');

		if (find_form_roll_errors($rec, $err) == 0) {
			if ($newid = insert_record('dean_reroll', $rec))	{
				  add_to_log(1, 'dean', 'one reroll added', 'blocks/dean/rolls/roll2.php', $USER->lastname.' '.$USER->firstname);
				 notice(get_string('rerolladded','block_dean'), "$CFG->wwwroot/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=retake");
			} else
				error(get_string('errorinaddingreroll','block_dean'), "$CFG->wwwroot/blocks/dean/rolls/roll2.php?roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=retake");
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($rid > 0) 	{
			$reroll = get_record('dean_reroll', 'id', $rid);
			$rec->id = $reroll->id;
			$rec->deanid = $reroll->deanid;
			$rec->teachername = $reroll->teachername;
			$rec->datoftake = $reroll->datoftake;
			$rec->datofgive = $reroll->datofgive;
			//$rec->disciplineid = $reroll->disciplineid;
			//$rec->academygroupid = $reroll->academygroupid;

		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('deanid', PARAM_INT);
		//$rec->deanid = required_param('deanid', PARAM_INT);
		$rec->datoftake = required_param('datoftake');
		$rec->datofgive = required_param('datofgive');
		$rec->teachername = $reroll->teachername;
		//$rec->disciplineid = required_param('disciplineid', PARAM_INT);
		//$rec->academygroupid = required_param('academygroupid');

		if (find_form_roll_errors($rec, $err, $mode) == 0) {
			//$rec->timemodified = time();
			if (update_record('dean_reroll', $rec))	{
				 add_to_log(1, 'dean', 'reroll update', 'blocks/dean/rolls/roll2.php', $USER->lastname.' '.$USER->firstname);
				 notice(get_string('rerollupdate','block_dean'), "$CFG->wwwroot/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=retake");
			} else {
				error(get_string('errorinupdatingreroll','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/faculty.php");
			}
		}
	}

	print_heading($straddreroll);
//	if ($mode === 'new') print_heading(get_string('newfaculty','block_dean'));
//	else print_heading(get_string('updatefaculty','block_dean'));

    print_dean_box_start("center");

	if (isset($deanmenu)) unset($deanmenu);
	$dekani = get_records_sql ("SELECT id, userid FROM {$CFG->prefix}user_teachers");
	if ($dekani)	{
		foreach ($dekani as $dekan) {
				$duser = get_record("user", "id", $dekan->userid);
				$deanmenu[$duser->id] = fullname ($duser);
		}
	}
	natsort($deanmenu);
	$deanmenu[0] = get_string("selectateacher","block_dean")." ...";


	if (isset($usermenu)) unset($usermenu);

	$usermenu[0] = get_string("selectastudent","block_dean")." ...";
	$strsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                              t.timeaccess as lastaccess, m.academygroupid
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
					   WHERE m.academygroupid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY lastname ASC";
   //echo $strsql . '<hr>';

	if ($useri = get_records_sql ($strsql))	{
		//print_r($useri);
		foreach ($useri as $user) {
				// $tuser = get_record("user", "id", $user->userid);
				$usermenu[$user->id] = fullname ($user);
		}
	}
	//natsort($usermenu);
    // print_r ($datoftake);

?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addreroll.php?mode=add";
												 else echo "addreroll.php?mode=update&amp;rid=$rid"; ?>">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string("teachername", "block_dean") ?>:</b></td>
    <td align="left">
		<?php
			 choose_from_menu ($deanmenu, "teacherid", $rec->teacherid, false);
			 if (isset($err["teacherid"])) formerr($err["teacherid"]); ?>
    </td>
</tr>

<tr valign="top">
    <td align="right"><b><?php  print_string("username", "block_dean") ?>:</b></td>
    <td align="left">
		<?php
			 choose_from_menu ($usermenu, "studentid", $rec->studentid, false);
			 if (isset($err["studentid"])) formerr($err["studentid"]); ?>
    </td>
</tr>


<tr valign="top">
    <td align="right"><b><?php  print_string("numbert", "block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="datoftake" name="datoftake" size="15" value="<?php p($rec->datoftake) ?>" />
		<?php if (isset($err["datoftake"])) formerr($err["datoftake"]); ?>
    </td>
</tr>

<tr valign="top">
    <td align="right"><b><?php  print_string("numberg","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="datofgive" name="datofgive" size="15" value="<?php p($rec->datofgive) ?>" />
		<?php if (isset($err["datofgive"])) formerr($err["datofgive"]); ?>
    </td>
</tr>
</table>
   <div align="center">
   <input type="hidden" name="fid" value="<?php echo $fid ?>" />
   <input type="hidden" name="sid" value="<?php echo $sid ?>" />
   <input type="hidden" name="cid" value="<?php echo $cid ?>" />
   <input type="hidden" name="gid" value="<?php echo $gid ?>" />
   <input type="hidden" name="did" value="<?php echo $did ?>" />
   <input type="hidden" name="rid" value="<?php echo $rid ?>" />
   <input type="hidden" name="term" value="<?php echo $term ?>" />
   <input type="hidden" name="mod" value="<?php echo $mod ?>" />
   <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />
   <input type="submit" name="addreroll" value="<?php print_string('savechanges')?>">

  </div>
 </center>
</form>


<?php


    print_dean_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_roll_errors(&$rec, &$err, $mode='add')
{
        if (empty($rec->teacherid))	{
			$err["teacherid"] = get_string("missingname");
		}

        if (empty($rec->studentid))	{
			$err["studentid"] = get_string("missingname");
		}

        if (empty($rec->datoftake))	{
		    $err["datoftake"] = get_string("missingname");
		}

        if (empty($rec->datofgive))	{
			$err["datofgive"] = get_string("missingname");
		}


    return count($err);
}

?>