<?PHP // $Id: addfaculty.php,v 1.11 2012/11/28 11:10:56 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
	$fid = optional_param('fid', 0, PARAM_INT);
    $ret = optional_param('ret', 0, PARAM_INT);


    if ($mode === "new" || $mode === "add" )
    	  $straddfac = get_string('addfaculty','block_dean');
	else  $straddfac = get_string('updatefaculty','block_dean');
	$strfaculty = get_string('faculty','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"faculty.php\">$strfaculty</a> -> $straddfac";
    print_header("$SITE->shortname: $straddfac", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
    $methodist_is = ismethodist();

    if (!($admin_is || $creator_is || $methodist_is == $fid)) {
        error(get_string('adminaccess', 'block_dean'), "faculty.php");
    }

	$rec->number = "";
	$rec->name = "";
	$rec->deanid = 0;
    $rec->zamdeanid = 0;
    $rec->zzdeanid = 0;
    $rec->z3deanid = 0;
	$rec->deanphone1 = "";
	$rec->deanphone2 = "";
	$rec->deanaddress = "";
    
    if ($ret == 0) {
        $redirlink = "$CFG->wwwroot/blocks/dean/faculty/faculty.php";            
    } else {
        $redirlink = "$CFG->wwwroot/blocks/dean/service/index.php?id=$fid";
    }
    

	if ($mode === 'add')  {
		$rec->number = required_param('numf', PARAM_INT);
		$rec->name = required_param('name');
		$rec->deanid = required_param('deanid', PARAM_INT);
        $rec->zamdeanid = required_param('zamdeanid', PARAM_INT);
        $rec->zzdeanid = required_param('zzdeanid', PARAM_INT);
        $rec->z3deanid = required_param('z3deanid', PARAM_INT);
		$rec->deanphone1 = required_param('telnum');
		$rec->deanaddress = required_param('address');

		if (find_form_faculty_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if (insert_record('dean_faculty', $rec))	{
				 add_to_log(1, 'dean', 'faculty added', 'blocks/dean/faculty/faculty.php', $USER->lastname.' '.$USER->firstname);
				 redirect($redirlink, get_string('facultyadded','block_dean'), 0);
			} else
				error(get_string('errorinaddingfaculty','block_dean'), $redirlink);
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($fid > 0) 	{
			$faculty = get_record('dean_faculty', 'id', $fid);
			$rec->id = $faculty->id;
			$rec->number = $faculty->number;
			$rec->name = $faculty->name;
			$rec->deanid = $faculty->deanid;
            $rec->zamdeanid = $faculty->zamdeanid;
            $rec->zzdeanid = $faculty->zzdeanid;
            $rec->z3deanid = $faculty->z3deanid;
			$rec->deanphone1 = $faculty->deanphone1;
			$rec->deanaddress = $faculty->deanaddress;

		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('facultyid', PARAM_INT);
		$rec->number = required_param('numf', PARAM_INT);
		$rec->name = required_param('name');
		$rec->deanid = required_param('deanid', PARAM_INT);
        $rec->zamdeanid = required_param('zamdeanid', PARAM_INT);
        $rec->zzdeanid = required_param('zzdeanid', PARAM_INT);        
        $rec->z3deanid = required_param('z3deanid', PARAM_INT);
		$rec->deanphone1 = required_param('telnum');
		$rec->deanaddress = required_param('address');

		if (find_form_faculty_errors($rec, $err, $mode) == 0) {
			$rec->timemodified = time();
			if (update_record('dean_faculty', $rec))	{
				 add_to_log(1, 'dean', 'faculty update', 'blocks/dean/faculty/faculty.php', $USER->lastname.' '.$USER->firstname);
				 redirect($redirlink, get_string('facultyupdate','block_dean'), 0);
			} else {
				error(get_string('errorinupdatingfaculty','block_dean'), $redirlink);
			}
		}
	}

	print_heading($straddfac);
//	if ($mode === 'new') print_heading(get_string('newfaculty','block_dean'));
//	else print_heading(get_string('updatefaculty','block_dean'));

    print_dean_box_start("center");
/*
	if (isset($deanmenu)) unset($deanmenu);
	$dds = get_records_sql ("SELECT DISTINCT userid FROM {$CFG->prefix}role_assignments
							 WHERE roleid<=4");
    $deansids = array();
    foreach ($dds as $d)	{
        $deansids[] = $d->userid;
    }
    $listdeansid = implode (',', $deansids); 

	$dekani = get_records_sql("SELECT  id, lastname, firstname FROM {$CFG->prefix}user
							   WHERE id in ($listdeansid)");
	if ($dekani)	{
		foreach ($dekani as $dekan) {
				//$duser = get_record("user", "id", $dekan->id);
				$deanmenu[$dekan->id] = fullname ($dekan);
		}
	}
	natsort($deanmenu);
*/

	$deanmenu[0] =  get_string("selectadean","block_dean")." ...";
    
    /*
    $strsql = "SELECT id, lastname, firstname FROM {$CFG->prefix}user
              where (auth = 'ldap2' || email like '%@bsu.edu.ru') and  deleted = 0
              ORDER BY lastname";
    */
    
    $strsql = "SELECT distinct u.id, lastname, firstname, email FROM mdl_user u
                left join mdl_role_assignments r ON u.id = r.userid
                where r.roleid in (3, 4) AND u.deleted = 0
                ORDER BY u.lastname";                  
	if ($dekani = get_records_sql($strsql))	{
		foreach ($dekani as $dekan) {
				//$duser = get_record("user", "id", $dekan->id);
				$deanmenu[$dekan->id] = fullname ($dekan);
		}
	}

?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addfaculty.php?mode=add&amp;ret=$ret";
												 else echo "addfaculty.php?mode=update&amp;fid=$fid&amp;ret=$ret"; ?>">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string("deanname", "block_dean") ?>:</b></td>
    <td align="left">
		<?php
			 choose_from_menu ($deanmenu, "deanid", $rec->deanid, false);
			 if (isset($err["deanid"])) formerr($err["deanid"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("zamdeanoffaculty", "block_dean"); $deanmenu[0] = get_string("selectzamdean","block_dean")." ..."; ?>:</b></td>
    <td align="left">
		<?php
			 choose_from_menu ($deanmenu, "zamdeanid", $rec->zamdeanid, false);
			 if (isset($err["zamdeanid"])) formerr($err["zamdeanid"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("zzdeanoffaculty", "block_dean") ?>:</b></td>
    <td align="left">
		<?php
			 choose_from_menu ($deanmenu, "zzdeanid", $rec->zzdeanid, false);
        ?>     
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("z3dean", "block_dean") ?>:</b></td>
    <td align="left">
		<?php
			 choose_from_menu ($deanmenu, "z3deanid", $rec->z3deanid, false);
        ?>     
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("numberf", "block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" readonly id="numf" name="numf" size="10" value="<?php p($rec->number) ?>" />
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
    <td align="right"><b><?php  print_string("telnum","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="telnum" name="telnum" size="20" value="<?php p($rec->deanphone1) ?>" />
		<?php if (isset($err["telnum"])) formerr($err["telnum"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("address","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="address" name="address" size="70" value="<?php p($rec->deanaddress) ?>" />
		<?php if (isset($err["address"])) formerr($err["address"]); ?>
    </td>
</tr>
</table>
   <div align="center">
  <input type="hidden" name="facultyid" value="<?php p($fid)?>">
  <input type="submit" name="addfaculty" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_dean_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_faculty_errors(&$rec, &$err, $mode='add')
{
		if ($mode == 'add')  {
	        if (empty($rec->number)) {
	            $err["number"] = get_string("missingname");
			}
			else if (record_exists('dean_faculty', 'number', $rec->number))  {
				$err["number"] = get_string("errornumberexist", "block_dean");
			}
		}
		else	{
			if (empty($rec->number)) {
	            $err["number"] = get_string("missingname");
			}
			else 	{
				$f = get_record('dean_faculty', 'id', $rec->id);
				if ($f->number != $rec->number)  {
					if (record_exists('dean_faculty', "number", $rec->number))  {
						$err["number"] = get_string("errornumberexist", "block_dean");
					}
				}
			}
		}

        if (empty($rec->name))	{
		    $err["name"] = get_string("missingname");
		}
        if (empty($rec->deanid))	{
			$err["deanid"] = get_string("missingname");
		}
        if (empty($rec->zamdeanid))	{
			$err["zamdeanid"] = get_string("missingname");
		}
        if (empty($rec->deanphone1)) 	{
			$err["telnum"] = get_string("missingname");
		}
        if (empty($rec->deanaddress))	{
			$err["address"] = get_string("missingname");
		}

    return count($err);
}

?>