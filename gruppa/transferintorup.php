<?PHP // $Id: transferintorup.php,v 1.5 2009/10/02 10:32:39 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    define("MAX_USERS_PER_PAGE", 1000);

    $mode = required_param('mode', PARAM_INT);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);        // Faculty id
	$sid = required_param('sid', PARAM_INT);		// Speciality id
	$cid = required_param('cid', PARAM_INT);		// Curriculum id
	$gid = required_param('gid', PARAM_INT);		// Academygroup ID

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strchangelist = get_string('changecurriculum', 'block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	// $strstudents = get_string("students","block_dean");
    $strstudents   = get_string("students");
    $strsearch        = get_string("search");
    $strsearchresults  = get_string("searchresults");
    $strshowall = get_string("showall");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strchangelist";
    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
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

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
 	}

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Group not found!");
 	}


	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		 listbox_faculty("transferintorup.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
   		 listbox_speciality("transferintorup.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
 	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode == 2)  {

    $currenttab = 'transferintorup';
    include('tabsonegroup.php');

	/// A form was submitted so process the input
	if ($frm = data_submitted())  {

        if (!empty($frm->remove) and !empty($frm->groupselect) and !empty($frm->curricselect) and confirm_sesskey()) {

			$cidnew = current($frm->curricselect);
			if (!$curric = get_record('dean_curriculum', 'id', $cidnew)) {
			        error("New group not found!");
		 	}

            foreach ($frm->groupselect as $group) 		{

				if ($agroup = get_record("dean_academygroups", "id", $group))  {
					
					$agroup->specialityid = $curric->specialityid;
					$agroup->curriculumid = $cidnew;
  				   if (update_record('dean_academygroups', $agroup)) {
					   notify('Рабочий учебный план изменен', "/blocks/dean/gruppa/transferintorup.php?mode=2&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
				   }

				   else {
						error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
				    }
				}

            }
        }
    }

/// Get all academygroups in this faculty
    $agroupsql  = "SELECT id, facultyid, specialityid, curriculumid, name
                   FROM {$CFG->prefix}dean_academygroups
				   WHERE (facultyid = $fid)  AND (id <> $gid)
 				   ORDER BY name ASC";

    if ($agroups = get_records_sql($agroupsql)) {
    	$countagroups = count($agroups);
    } else {
    	$countagroups = 0;
    }

    $curricsql  = "SELECT id, facultyid, specialityid, name
                   FROM {$CFG->prefix}dean_curriculum
				   WHERE facultyid = $fid
 				   ORDER BY name ASC";

    if ($curric = get_records_sql($curricsql)) {
    	$existingcurricarray = array();
    	foreach ($curric as $curr) {
	    $existingcurricarray[] = $curr->id;
	    }
	    $existingcurriclist = implode(',', $existingcurricarray);
	    unset($existingcurricarray);
    	$countcurric = count($curric);
    } else {
    	$countcurric = 0;
    }

   print_dean_box_start("center");

   $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>


<form name="studentform" id="studentform" method="post" action="transferintorup.php">
<input type="hidden" name="mode" value="<?php echo $mode ?>" />
<input type="hidden" name="fid" value="<?php echo $fid ?>" />
<input type="hidden" name="sid" value="<?php echo $sid ?>" />
<input type="hidden" name="cid" value="<?php echo $cid ?>" />
<input type="hidden" name="gid" value="<?php echo $gid ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
  <table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top">
          <?php
              echo get_string('groups') . ' (' . $countagroups. ')' ;
          ?>
      </td>
      <td></td>
      <td valign="top">
          <?php
              echo get_string("rup","block_dean") . ' (' . $countcurric. ')';
          ?>
      </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="groupselect[]" size="20" id="groupselect" multiple
                  onFocus="document.studentform.remove.disabled=true;
                           document.studentform.addselect.selectedIndex=-1;">
                                     <?php
	          if (!empty($agroups)) {
	              foreach ($agroups as $ag) {
 	                 echo "<option value=\"$ag->id\">".$ag->name."</option>\n";
  		          }
  		      }
          ?>

          </select>
      </td>
      <td valign="top">
        <br />
        <input name="remove" type="submit" id="remove" value="&rarr;" />
        <br />
      </td>
      <td valign="top">
          <select name="curricselect[]" size="20" id="curricselect"
                  onFocus="document.studentform.remove.disabled=false;">
            <?php
              if (!empty($curric)) {
	              foreach ($curric as $curr) {
 	                 $fullname = fullname($curr, true);
  	                echo "<option value=\"$curr->id\">".$curr->name."</option>\n";
   	              }
   	          }
          ?>
         </select>
      </td>
    </tr>
  </table>
</form>

<?php
   print_dean_box_end();

  }
  // print_r($agroup->curriculumid);
   print_footer();

?>