<?PHP // $Id: transferstudents.php,v 1.3 2013/10/09 08:47:49 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    define("MAX_USERS_PER_PAGE", 1000);

    $mode = required_param('mode', PARAM_INT);    // 1, 2, 3, 4
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
	$strchangelist = get_string('changeliststudents', 'block_dean');
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
	listbox_faculty("transferstudents.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("transferstudents.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("transferstudents.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    listbox_group_pegas("transferstudents.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode == 4)  {

    $currenttab = 'transferstudents';
    include('tabsonegroup.php');

	/// A form was submitted so process the input
	if ($frm = data_submitted())  {

        if (!empty($frm->remove) and !empty($frm->removeselect) and !empty($frm->groupselect) and confirm_sesskey()) {

			$gidnew = current($frm->groupselect);
			if (!$academygroupnew = get_record('dean_academygroups', 'id', $gidnew)) {
			        error("New group not found!");
		 	}

            // print_r($academygroupnew);
            foreach ($frm->removeselect as $removestudent) 		{
				// 1. Remove from group
				delete_records('dean_academygroups_members', 'userid', $removestudent, 'academygroupid', $academygroup->id);
				if ($mgroups = get_records("groups", "name", $academygroup->name))  {
					 foreach($mgroups as $mgroup)  {
						unenrol_student_dean ($removestudent, $mgroup->courseid);
	                	delete_records('groups_members', 'groupid', $mgroup->id, 'userid', $removestudent);
					 }
				}
				// add_to_log(1, 'dean', 'one student deleted from academygroup', "/blocks/dean/gruppa/transferstudents.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
 	            // 2. Add in new group
				$rec->userid = $removestudent;
				$rec->academygroupid = $gidnew;
				$rec->timeadded = time();

				if (insert_record('dean_academygroups_members', $rec))	{
					 // add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/transferstudents.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
				} else  {
					error(get_string('errorinaddinggroupmember','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
				}

  			    // Get groups (moodle) in course with equal name
				if ($mnewgroups = get_records("groups", "name", $academygroupnew->name))  {
					 foreach($mnewgroups as $mnewgroup)  {
	 	                if (!enrol_student_dean($rec->userid, $mnewgroup->courseid)) {
	                       error("Could not add student with id $rec->userid to this course $mnewgroup->courseid!");
						}

	                	 /// Delete duplicated students in the other group
	                      $strsql = "SELECT g.id, g.name, g.courseid, m.userid
									   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id = m.groupid
									   WHERE (g.courseid={$mnewgroup->courseid}) AND (m.userid={$rec->userid})";
					   	  if ($duplgroups = get_records_sql($strsql))	{
								foreach ($duplgroups as $duplgroup)	 {
			 	                	 delete_records('groups_members', 'groupid', $duplgroup->id, 'userid', $rec->userid);
								}
	 				      }

 	  		        	$record->groupid = $mnewgroup->id;
	      	            $record->userid = $rec->userid;
    	    	        $record->timeadded = time();
        	    	    if (!insert_record('groups_members', $record)) {
            	    	    notify("Error occurred while adding user $rec->userid to group $mnewgroup->id");
	            	    }
                    } //foreach
				} //if
            }
        }
    }

    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                              u.lastaccess, m.academygroupid
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
					   WHERE m.academygroupid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY lastname ASC";

   //print_r($studentsql);
    if ($students = get_records_sql($studentsql)) {
	    $existinguserarray = array();
	    foreach ($students as $student) {
	        $existinguserarray[] = $student->id;
	    }
	    $existinguserlist = implode(',', $existinguserarray);

	    unset($existinguserarray);
	    $countstudents = count($students);
    } else {
    	$countstudents = 0;
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

/*
    if (!empty($frm->searchtext) and $previoussearch) {
        $searchusers = get_users(true, $frm->searchtext, true, $existinguserlist, 'firstname ASC, lastname ASC',
                                      '', '', 0, 99999, 'id, firstname, lastname, email', $cb_checked, $fiosearch);
        $usercount = get_users(false, '', true, $existinguserlist);
    }

/// If no search results then get potential students for this course excluding users already in course
    if (empty($searchusers)) {

        $usercount = get_users(false, '', true, $existinguserlist, 'firstname ASC, lastname ASC', '', '',
                              0, 99999, 'id, firstname, lastname, email', $cb_checked, $fiosearch);
        $users = array();

        if ($usercount <= MAX_USERS_PER_PAGE) {
            $users = get_users(true, '', true, $existinguserlist, 'firstname ASC, lastname ASC', '', '',
                               0, 99999, 'id, firstname, lastname, email', $cb_checked, $fiosearch);
        }

    }

   $searchtext = (isset($frm->searchtext)) ? $frm->searchtext : "";
   $previoussearch = ($previoussearch) ? '1' : '0';
*/
   print_dean_box_start("center");

   $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>


<form name="studentform" id="studentform" method="post" action="transferstudents.php">
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
              echo get_string('students') . ' (' . $countstudents. ')' ;
          ?>
      </td>
      <td></td>
      <td valign="top">
          <?php
              echo get_string("groups") . ' (' . $countagroups. ')';
          ?>
      </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect" multiple
                  onFocus="document.studentform.remove.disabled=true;
                           document.studentform.addselect.selectedIndex=-1;">
          <?php
              if (!empty($students)) {
	              foreach ($students as $student) {
 	                 $fullname = fullname($student, true);
  	                echo "<option value=\"$student->id\">".$fullname.", ".$student->email."</option>\n";
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
          <select name="groupselect[]" size="20" id="groupselect"
                  onFocus="document.studentform.remove.disabled=false;">
          <?php
	          if (!empty($agroups)) {
	              foreach ($agroups as $ag) {
 	                 echo "<option value=\"$ag->id\">".$ag->name."</option>\n";
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
   print_footer();

?>