<?PHP // $Id: certificate_add.php,v 1.6 2013/10/09 08:47:49 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    $mode = required_param('mode', PARAM_INT);       // 1, 2, 3, 4
    $fid = required_param('fid', PARAM_INT);        // Faculty id
	$sid = required_param('sid', PARAM_INT);		// Speciality id
	$cid = required_param('cid', PARAM_INT);		// Curriculum id
	$gid = required_param('gid', PARAM_INT);		// Academygroup ID
	$cert_all = optional_param('cert_all', '');		// Create certificate for all
	$cert_one = optional_param('cert_one', '');		// Create certificate for one
	$operation = optional_param('operation', 'new'); // new, add, edit, update

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

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
 	}

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Group not found!");
 	}


	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("certificate.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("certificate.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("certificate.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    listbox_group_pegas("certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode >= 4)  {

	    $currenttab = 'certificate';
	    include('tabsonegroup.php');

		if ($operation != 'new')	{
			/// A form was submitted so process the input
			if ($rec = data_submitted())  {

		        // print_r($rec);

				if (find_form_cert_errors($rec, $err, $cert_one) == 0) {

					if ($cert_one != '')	{
						$cert->studentid = $rec->uid;
						$cert->disciplineid = $rec->did;
						$cert->number = $rec->number;
						$cert->hours = $rec->hoursnumber;
						$cert->datecreated = make_timestamp($rec->syear, $rec->smonth, $rec->sday);
						$cert->timemodified = time();

						if ($oldcert = get_record('dean_certificate', 'studentid', $cert->studentid, 'disciplineid', $cert->disciplineid))  {
							  notice(get_string('errorcertificate1', 'block_dean', $oldcert->number), "$CFG->wwwroot/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
						}	else {
							if (insert_record('dean_certificate', $cert))	{
								  // add_to_log(1, 'dean', 'one discipline added', "blocks/dean/curriculum/addiscipline.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
								  if ($lastnumber = get_record ('dean_config', 'name', 'lastsertificatenumber'))  {
					                $lastnumber->value = $rec->number;
						            update_record('dean_config', $lastnumber);
								  }
								  notice(get_string('certificateadded','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
								 //redirect(get_string('certificateadded','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", 5);
							} else {
								error(get_string('errorinaddingcert','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
							}
						}
					} else if ($cert_all != '')	{

						$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
												  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
					                              u.lastaccess, m.academygroupid
					                            FROM {$CFG->prefix}user u
					                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";

						$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
						$studentsql .= 'ORDER BY u.lastname';

						if($students = get_records_sql($studentsql)) {
							  $lastrecnumber = $rec->number;
					           foreach ($students as $student) 	{
	               					unset($cert);
									$cert->studentid = $student->id;
									$cert->disciplineid = $rec->did;
									$cert->number = $rec->number;
									$cert->hours = $rec->hoursnumber;
									$cert->datecreated = make_timestamp($rec->syear, $rec->smonth, $rec->sday);
									$cert->timemodified = time();

									// print_r($cert); echo '<hr>';

									if ($oldcert = get_record('dean_certificate', 'studentid', $cert->studentid, 'disciplineid', $cert->disciplineid))  {
										  notify(get_string('errorcertificate1', 'block_dean', $oldcert->number));
									}	else {

										if (insert_record('dean_certificate', $cert))	{
											 // add_to_log(1, 'dean', 'one discipline added', "blocks/dean/curriculum/addiscipline.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
											  $lastrecnumber = $rec->number;
											  notify(get_string('certificateadded','block_dean'), 'green', 'left');
										} else {
											error(get_string('errorinaddingcert','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");
										}
										$rec->number++;
									}

					           }

							  if ($lastnumber = get_record ('dean_config', 'name', 'lastsertificatenumber'))  {
				                $lastnumber->value = $lastrecnumber;
					            update_record('dean_config', $lastnumber);
							  }
							  notice(get_string('certificategroupcreated','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid");

					    }


					}

				}
		    }
		}


	//   print_dean_box_start("center");

		if ($cert_one != '')	{
			$strtitle = get_string("selectastudent","block_dean")."...";
			$studentmenu = array();

		    $studentmenu[0] = $strtitle;


			$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
									  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
		                              m.academygroupid
		                            FROM {$CFG->prefix}user u
		                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";

			$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
			$studentsql .= 'ORDER BY u.lastname';
		    $students = get_records_sql($studentsql);

			if(!empty($students)) {
		           foreach ($students as $student) 	{
					$studentmenu[$student->id] = fullname($student);
				}
				// natsort($groupmenu);
		    }

		    if (!isset($rec->uid))  $rec->uid = 0;
		}


		$strtitle = get_string('selectadiscipline', 'block_dean') . ' ...';
		$groupmenu = array();

		$disciplinemenu[0] = $strtitle;
		$arr_discipline = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_discipline
		 								  WHERE curriculumid={$curriculum->id}
										  ORDER BY name");

		if ($arr_discipline) 	{
			foreach ($arr_discipline as $ds) {
				$disciplinemenu[$ds->id] =$ds->name;
			}
		}
	    if (!isset($rec->did))  $rec->did = 0;


		$lastdate=time();

	    if (empty($rec->number))	{

			if ($lastnumber = get_record ('dean_config', 'name', 'lastsertificatenumber')) {
			    $rec->number = $lastnumber->value;
			} else {
				$rec->number = 'ДО ' . date ('y'). '-0000';
			}

            $oy = substr($rec->number, 5, 2);
            // echo $rec->number . '<hr>'; echo $oy . '<hr>';
            if ($oy != date ('y'))	{
	            $rec->number = 'ДО ' . date ('y'). '-0000';
                $lastnumber->value = $rec->number;
                update_record('dean_config', $lastnumber);
            }

			$rec->number++;
/*
		    $lastnum++;
			$rec->number = 'ДО ' . date ('y');

			if ($lastnum >= 0  && $lastnum <= 9)	{
				 $rec->number .= '-000' . $lastnum;
			} else if ($lastnum >= 10  && $lastnum <= 99)	{
				 $rec->number .= '-00' . $lastnum;
			} else if ($lastnum >= 100  && $lastnum <= 999)	{
				 $rec->number .= '-0' . $lastnum;
			} else 	{
				 $rec->number .= '-' . $lastnum;
		    }
*/
		}

		if (empty($rec->hoursnumber)) {
			$rec->hoursnumber = 72;
		}

		// print_r($rec);
	//   listbox_student("certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;uid=", $fid, $sid, $cid, $gid, $uid);
	//   listbox_discipline("certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did=", $fid, $sid, $cid, $gid, $did);

	?>

	<form name="certificate_add" id="certificate_add" method="post" action="certificate_add.php">
	<input type="hidden" name="mode" value="<?php echo $mode ?>" />
	<input type="hidden" name="fid" value="<?php echo $fid ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />
	<input type="hidden" name="gid" value="<?php echo $gid ?>" />
	<input type="hidden" name="cert_all" value="<?php echo $cert_all ?>" />
	<input type="hidden" name="cert_one" value="<?php echo $cert_one ?>" />
	<input type="hidden" name="operation" value="add" />
	<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
	<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">

	<?php
		if ($cert_one != '')	{
		    echo '<tr><td>'.get_string('student','block_dean').':</td><td>';
		    choose_from_menu($studentmenu, 'uid', $rec->uid, '');
		    if (isset($err["uid"])) formerr($err["uid"]);
			echo '</td></tr>';
		}
	    echo '<tr><td>'.get_string('discipline', 'block_dean').':</td><td>';
	    choose_from_menu($disciplinemenu, 'did', $rec->did, '');
	    if (isset($err["did"])) formerr($err["did"]);
		echo '</td></tr>';
	?>

	<tr>
	    <td><?php print_string("numbercertificate", "block_dean") ?>:</td>
	    <td>
	    <input readonly type="text" name="number" size="15" alt="<?php print_string("numbercertificate", "block_dean") ?>" maxlength="100" value="<?php p($rec->number) ?>" />
	    <?php if (isset($err["number"])) formerr($err["number"]); ?>
	    </td>
	</tr>
	<tr>
	    <td><?php  print_string("hoursnumber", "block_dean")  ?>:</b></td>
	    <td>
			<input type="text" id="hoursnumber" name="hoursnumber" size="5" value="<?php p($rec->hoursnumber) ?>" />
			<?php if (isset($err["hoursnumber"])) formerr($err["hoursnumber"]); ?>
	    </td>
	</tr>
	<tr>
	    <td><?php print_string("datecertificate", "block_dean") ?>:</td>
	    <td>
			<?php print_date_selector("sday", "smonth", "syear", $lastdate); ?>
	    </td>
	</tr>
	  </table>
	   <div align="center">
	 	 <input type="submit" name="savecert" value="<?php print_string('create')?>">
	  </div>

	</form>
	<p>
<?php
   }

  print_footer();


/// FUNCTIONS ////////////////////
function find_form_cert_errors(&$rec, &$err, $cert_one)
{

    if ($cert_one != '')	{
        if (empty($rec->uid)) {
            $err["uid"] = get_string("missingname");
		}
	}

        if (empty($rec->did)) {
            $err["did"] = get_string("missingname");
		}
        if (empty($rec->number)) {
            $err["number"] = get_string("missingname");
		}
        if (empty($rec->hoursnumber))	{
		    $err["hoursnumber"] = get_string("missingname");
		}

    if ($cert_one != '')	{
		if ($cert = get_record('dean_certificate', 'studentid', $rec->uid, 'disciplineid', $rec->did))  {
		    $err["uid"] = get_string('errorcertificate1', 'block_dean', $cert->number);
		}
	}

		if ($cert = get_record('dean_certificate', 'number', $rec->number)) {
		    $err["number"] = get_string('errorcertificate2', 'block_dean', $rec->number);
		}

    return count($err);
}


?>