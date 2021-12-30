<?PHP // $Id: changelistgroup.php,v 1.4 2013/10/09 08:47:49 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_search.php');
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
	listbox_faculty("changelistgroup.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("changelistgroup.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("changelistgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    listbox_group_pegas("changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode == 4)  {

    $currenttab = 'changeliststudents';
    include('tabsonegroup.php');

	$checked = $fiosearch = $cb_checked = '';
	$fio = 	$ln =  $fn = '';

	/// A form was submitted so process the input
	if ($frm = data_submitted())
	  {
	  // Изменения Загороднюк Роман: добавлены эти строки
    	$fiosearch = $frm->searchto;
  		if($frm->fullcoincidence == 'ON') {
			$checked = 'checked';
			$cb_checked = 'ON';
		}

		switch ($fiosearch) {
			case 'fio':
				$fio = 'checked';
		    break;
			case 'ln':
				$ln = 'checked';
		    break;
			case 'fn':
				$fn = 'checked';
		    break;
		}

		switch ($frm->searchto) {
			case 'fio':
				$fio = 'checked';
		    break;
			case 'ln':
				$ln = 'checked';
		    break;
			case 'fn':
				$fn = 'checked';
		    break;
		}
	// Конец изменений


        if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
            $timeadded = time();
			$rec->academygroupid = $gid;
			$rec->timeadded = $timeadded;

            foreach ($frm->addselect as $addstudent) {
                add_student_to_dean_group($addstudent, $academygroup);
			} //foreach
        } else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
            foreach ($frm->removeselect as $removestudent) {
				delete_records('dean_academygroups_members', 'userid', $removestudent, 'academygroupid', $academygroup->id);
				if ($mgroups = get_records("groups", "name", $academygroup->name))  {
					 foreach($mgroups as $mgroup)  {
						unenrol_student_dean ($removestudent, $mgroup->courseid);
	                	delete_records('groups_members', 'groupid', $mgroup->id, 'userid', $removestudent);
					 }
				}
				// add_to_log(1, 'dean', 'one student deleted from academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
            }
        } else if (!empty($frm->showall)) {
            unset($frm->searchtext);
            $frm->previoussearch = 0;
        }
    }

    $previoussearch = (!empty($frm) && (!empty($frm->search) or ($frm->previoussearch == 1))) ;

/// Get all existing students and teachers for this course.
/*
    if (!$students = get_course_students($course->id, "u.firstname ASC, u.lastname ASC", "", 0, 99999,
                                         '', '', NULL, '', 'u.id,u.firstname,u.lastname,u.email')) {
        $students = array();
    }
*/

	/// Get all existing students for this academygroup.
    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                              u.lastaccess, m.academygroupid
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
					   WHERE m.academygroupid = $gid AND u.deleted = 0 AND u.confirmed = 1
					   ORDER BY lastname ASC";

   //print_r($studentsql);
    $studentscount = 0;
    if ($students = get_records_sql($studentsql))	{
    	$studentscount = count($students);
    }


    $existinguserarray = array();
    foreach ($students as $student) {
        $existinguserarray[] = $student->id;
    }
    $existinguserlist = implode(',', $existinguserarray);

    unset($existinguserarray);


/// Get search results excluding any users already in this course
    if (!empty($frm->searchtext) and $previoussearch) {
        $searchusers = get_users(true, $frm->searchtext, true, $existinguserlist, 'lastname ASC, firstname ASC',
                                      '', '', 0, 99999, 'id, firstname, lastname, email');
        $usercount = get_users(false, '', true, $existinguserlist);
    }

/// If no search results then get potential students for this course excluding users already in course
    if (empty($searchusers)) {

        $users = array();
        $usercount = count_records('user');
/*
        $usercount = get_users(false, '', true, $existinguserlist, 'firstname ASC, lastname ASC', '', '',
                              0, 99999, 'id, firstname, lastname, email', $cb_checked, $fiosearch);
        $users = array();

        if ($usercount <= MAX_USERS_PER_PAGE) {
            $users = get_users(true, '', true, $existinguserlist, 'firstname ASC, lastname ASC', '', '',
                               0, 99999, 'id, firstname, lastname, email', $cb_checked, $fiosearch);
        }
*/
    }


/*

    /// Get search results excluding any users already in academygroup
    if (!empty($frm->searchtext) and $previoussearch) {
	    $LIKE      = sql_ilike();
    	// $fullname  = " CONCAT(u.firstname,\" \",u.lastname) ";
		$fullname  = "u.lastname";
        $search = trim($frm->searchtext);
    	$searchsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
	                      FROM {$CFG->prefix}user u
                          LEFT JOIN {$CFG->prefix}user_students t ON t.userid = u.id
                          LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = t.userid
						  WHERE	($fullname $LIKE '%$search%' OR email $LIKE '%$search%')
						  GROUP BY t.userid
						  HAVING (((Count(m.userid))=0))
						  ORDER BY u.lastname ASC";

        $searchusers = get_records_sql($searchsql);
        $usercount = count($searchusers);
    }
	else {
    /// If no search results then get potential students for this academygroup excluding users already in other academygroup
	    $notingroupsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
                          FROM {$CFG->prefix}user u
                          LEFT JOIN {$CFG->prefix}user_students t ON t.userid = u.id
                          LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = t.userid
						  GROUP BY t.userid
						  HAVING (((Count(m.userid))=0))
						  ORDER BY u.lastname ASC";

    	$users = get_records_sql($notingroupsql);
		$usercount = count($users);
	}
*/


   $searchtext = (isset($frm->searchtext)) ? $frm->searchtext : "";
   $previoussearch = ($previoussearch) ? '1' : '0';

   print_dean_box_start("center");

   $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>


<form name="studentform" id="studentform" method="post" action="changelistgroup.php">
<input type="hidden" name="previoussearch" value="<?php echo $previoussearch ?>" />
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
              echo get_string('students') . ' (' . $studentscount . ')' ;
          ?>
      </td>
      <td></td>
      <td valign="top">
          <?php
              echo get_string("potentialstudents") . ' (' . $usercount. ')';
          ?>
      </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect" multiple
                  onFocus="document.studentform.add.disabled=true;
                           document.studentform.remove.disabled=false;
                           document.studentform.addselect.selectedIndex=-1;">
          <?php
              foreach ($students as $student) {
                  $fullname = fullname($student, true);
                  echo "<option value=\"$student->id\">".$fullname.", ".$student->email."</option>\n";
              }
          ?>

          </select></td>
      <td valign="top">
        <br />
        <input name="add" type="submit" id="add" value="&larr;" />
        <br />
        <input name="remove" type="submit" id="remove" value="&rarr;" />
        <br />
      </td>
      <td valign="top">
          <select name="addselect[]" size="20" id="addselect" multiple
                  onFocus="document.studentform.add.disabled=false;
                           document.studentform.remove.disabled=true;
                           document.studentform.removeselect.selectedIndex=-1;">
          <?php
         if (!empty($searchusers)) {
                  echo "<optgroup label=\"$strsearchresults (" . count($searchusers) . ")\">\n";
                  foreach ($searchusers as $user) {
                      $fullname = fullname($user, true);
                      echo "<option value=\"$user->id\">".$fullname.", ".$user->email."</option>\n";
                  }
                  echo "</optgroup>\n";
              }
        else {
                  if ($usercount > MAX_USERS_PER_PAGE) {
                      echo '<optgroup label="'.get_string('toomanytoshow').'"><option></option></optgroup>'."\n"
                          .'<optgroup label="'.get_string('trysearching').'"><option></option></optgroup>'."\n";
                  }
                  else {
                      if ($usercount > 0) {    //fix for bug#4455
                          foreach ($users as $user) {
                              $fullname = fullname($user, true);
                              echo "<option value=\"$user->id\">".$fullname.", ".$user->email."</option>\n";
                          }
                      }
                  }
              }

          ?>
         </select>
         <br />
		<?php
// Изменения Загороднюк Роман: добавлены эти строки
			echo '</td></tr></table><center>'.
			"<table cellspacing='0' cellpadding='10' align='center' class='generaltable generalbox'><tr><td align=left>".
			get_string('lookup', 'block_dean').': <br>'.
			'<br><input type="radio" value="fio" '."$fio".' name="searchto">'.get_string('fio', 'block_dean').
			'<br><input type="radio" value="ln" '."$ln".' name="searchto">'.get_string('lastnames' , 'block_dean').
			'<br><input type="radio" value="fn" '."$fn".' name="searchto">'.get_string('firstnames', 'block_dean').
			'</td></tr></table><center><input type="checkbox" '."$checked".' name="fullcoincidence"'. 'value="ON">'.get_string('fullcoincidence', 'block_dean').'<br><br></center>';
// Конец изменений
		?>

         <input type="text" name="searchtext" size="30" value="<?php p($searchtext) ?>"
                  onFocus ="document.studentform.add.disabled=true;
                            document.studentform.remove.disabled=true;
                            document.studentform.removeselect.selectedIndex=-1;
                            document.studentform.addselect.selectedIndex=-1;"
                  onkeydown = "var keyCode = event.which ? event.which : event.keyCode;
                               if (keyCode == 13) {
                                    document.studentform.previoussearch.value=1;
                                    document.studentform.submit();
                               } " />
         <input name="search" id="search" type="submit" value="<?php p($strsearch) ?>" />
         <?php
              if (!empty($searchusers)) {
                  echo '<input name="showall" id="showall" type="submit" value="'.$strshowall.'" />'."\n";
              }
         ?>
       </td>
    </tr>
  </table>
</form>

<?php
   print_dean_box_end();
  }
/*
   $options = array();
   $options['fid'] = $fid;
   $options['sid'] = $sid;
   $options['cid'] = $cid;
   $options['gid'] = $gid;

   echo '<table width="100%" cellspacing="0"><tr>';
   echo '<td align="center">';
   print_single_button("savechangelist.php", $options, get_string('savechanges'));
   echo '</td></tr><table>';
*/
   print_footer();


?>