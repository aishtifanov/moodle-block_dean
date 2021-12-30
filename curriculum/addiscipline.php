<?PHP // $Id: addiscipline.php,v 1.5 2012/04/06 09:51:38 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);          // Faculty id
	$sid = required_param('sid', PARAM_INT);			// Speciality id
	$cid = required_param('cid', PARAM_INT);			// Curriculum id
	$did = optional_param('did', 0, PARAM_INT);			// Discipline id (courseid)
	$term = optional_param('term', 1, PARAM_INT);	// # semestra

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

	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
 	}

    $strfaculty = get_string('faculty','block_dean');
    $strfaculty = get_string('faculty','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
    $strdisciplines = get_string("disciplines","block_dean");

    if ($mode === "new" || $mode === "add" ) $straddisc = get_string('addiscipline','block_dean');
	else $straddisc = get_string('updatediscipline','block_dean');


    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid\"> $strdisciplines </a>";
	$breadcrumbs .= " -> $straddisc";
    print_header("$site->shortname: $straddisc", "$site->fullname", $breadcrumbs);


	$rec->curriculumid = $cid;
	$rec->courseid = 1;
	$rec->cipher = '-';
	$rec->auditoriumhours = 0;
	$rec->selfinstructionhours = 0;
	$rec->term = $term;
	$rec->termpaperhours = 0;
	$rec->controltype = 'zaschet';
	$rec->name = get_string('frmdisname','block_dean');

	if ($mode === 'add')  {
		$rec->courseid = required_param('courseid');
		$rec->cipher =  required_param('cipher');
		$rec->auditoriumhours = required_param('auditor');
		$rec->selfinstructionhours = required_param('selfi');
		$rec->term = required_param('term');
		$rec->termpaperhours = required_param('termpaperhours');
		$rec->controltype = required_param('controltype');
		$rec->name = required_param('namedis');
        $rec->enrolgroup = optional_param('enrolgroup', 0, PARAM_INT);	
        
        //print_r($rec); // exit(1);

		if (find_form_disc_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if ($rec->name == get_string('frmdisname','block_dean'))  {
				$rec->name = '';
			}
			if ($rec->courseid > 1 && empty($rec->name))	{
				$crs = get_record('course', 'id', $rec->courseid);
				$rec->name = $crs->fullname;
			}
			if (insert_record('dean_discipline', $rec))	{
				if ($rec->enrolgroup)   { 
				   if ($academygroups = get_records('dean_academygroups', 'curriculumid', $cid)) 	{
				
				   		foreach ($academygroups as $academygroup)	{
				   			
				 			    if ($newgrp = get_record('groups', 'name', $academygroup->name, 'courseid', $rec->courseid))	{
				 			    	notify (get_string('groupalreadyenroll', 'block_dean', $academygroup->name) . $rec->name, 'black');
				 			    	continue;
				 			    }  else {
					       	        $newgroup->name = $academygroup->name;
					           	    $newgroup->courseid = $rec->courseid;
									$newgroup->description = '';
									$newgroup->password = '';
									$newgroup->theme = '';
					               	$newgroup->lang = current_language();
					                $newgroup->timecreated = time();
					   	            if (!$newgrpid = insert_record("groups", $newgroup)) {
					       	            error("Could not insert the new group '$newgroup->name'");
					           	    } else {
					           	    	// notify("the new group '$newgroup->name'");
					   	            	add_to_log(1, 'dean', 'new moodle group registered', "blocks/dean/groups/registergroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
					           	    }
					           	}
				   			
					 	        if ($academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id))	{
								    foreach ($academystudents as $astud)	  {
						                /// Enrol student
									    if ($usr = get_record('user', 'id', $astud->userid))	{
										    // print '-'.$usr->id.':'.$usr->lastname.' '. $usr->firstname. '<br>';
										    if ($usr->deleted != 1)	 {
												if (!enrol_student_dean($astud->userid, $rec->courseid))  {
				    		 	                     notify("Could not add student with id $astud->userid to the course $rec->courseid!");
				      				            }
				      				        } else {
				      			 	 	      	delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
				      			  	 	    }
					     			    } else {
			   			        			delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
			      			     		}

					                	 /// Add people to a group
									     $newmember->groupid = $newgrpid;
					    	             $newmember->userid = $astud->userid;
					        	         $newmember->timeadded = time();
					            	     if (!insert_record('groups_members', $newmember)) {
					                	    notify("Error occurred while adding user $astud->userid to group $academygroup->name");
						                 }
						            }
				        	    }
				        	    
				        	    notify(get_string('groupenrollcourse', 'block_dean', $academygroup->name) . $rec->name, 'green');
				            	//	notify('Group not found ' . $academygroup->name);
				   		}
                     }   
				 }				
				 add_to_log(1, 'dean', 'one discipline added', "blocks/dean/curriculum/addiscipline.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('disciplineadded','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/disciplines.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
                 
			} else {
				error(get_string('errorinaddingdisc','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/disciplines.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
            }    
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($did > 0) 	{
			$disc = get_record('dean_discipline', 'id', $did);
			$rec->id = $disc->id;
			$rec->courseid = $disc->courseid;
			$rec->cipher =  $disc->cipher;
			$rec->auditoriumhours = $disc->auditoriumhours;
			$rec->selfinstructionhours = $disc->selfinstructionhours;
			$rec->term = $disc->term;
			$rec->termpaperhours = $disc->termpaperhours;
			$rec->controltype = $disc->controltype;
			$rec->name = $disc->name;
			$oid = $disc->courseid;
		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('discid', PARAM_INT);
		$rec->courseid = required_param('courseid');
		$rec->cipher =  required_param('cipher');
		$rec->auditoriumhours = required_param('auditor');
		$rec->selfinstructionhours = required_param('selfi');
		$rec->term = required_param('term');
		$rec->termpaperhours = required_param('termpaperhours');
		$rec->controltype = required_param('controltype');
		$rec->name = required_param('namedis');

		$oid = required_param('oid');

		// print_r($rec);
		// echo '<hr>'; echo "oid = $oid";

		if (find_form_disc_errors($rec, $err) == 0) {
			$rec->timemodified = time();
			if ($rec->name == get_string('frmdisname','block_dean'))  {
			   $rec->name = '';
			}
			if (update_record('dean_discipline', $rec))	{

				if ($oid != $rec->courseid)  {
				   // Get all group in old course

				   if ($academygroups = get_records('dean_academygroups', 'curriculumid', $cid)) 	{

				   		foreach ($academygroups as $academygroup)	{

   			 			    if ($groupexist = get_record("groups", "name", $academygroup->name, "courseid", $oid))	 {

			      	 	        $newgroup->name = $academygroup->name;
			     	      	    $newgroup->courseid = $rec->courseid;
								$newgroup->description = '';
								$newgroup->password = '';
								$newgroup->theme = '';
			    	           	$newgroup->lang = current_language();
				                $newgroup->timecreated = time();
			 	  	            if (!insert_record("groups", $newgroup)) {
			  	     	            notify("Could not insert the new group '{$newgroup->name}' in course #{$rec->courseid}");
			   	        	    }
								$newgrp = get_record("groups", "name", $academygroup->name, "courseid", $rec->courseid);

					 	        if ($academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id))	{
								    foreach ($academystudents as $astud)	  {

						                /// Enrol student
									    if ($usr = get_record('user', 'id', $astud->userid))	{
										    // print '-'.$usr->id.':'.$usr->lastname.' '. $usr->firstname. '<br>';
										    if ($usr->deleted != 1)	 {
												if (!enrol_student_dean($astud->userid, $rec->courseid))  {
				    		 	                     error("Could not add student with id $astud->userid to the course $rec->courseid!");
				      				            }
				      				        } else {
				      			 	 	      	delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
				      			  	 	    }
					     			    } else {
			   			        			delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
			      			     		}

					                	 /// Add people to a group
									     $newmember->groupid = $newgrp->id;
					    	             $newmember->userid = $astud->userid;
					        	         $newmember->timeadded = time();
					            	     if (!insert_record('groups_members', $newmember)) {
					                	    notify("Error occurred while adding user $astud->userid to group $academygroup->name");
						                 }

					                	/// UnEnrol student
										unenrol_student_dean ($astud->userid, $oid);

						            }
				        	    }

			 	                delete_records("groups", "id", $groupexist->id);
				                delete_records("groups_members", "groupid", $groupexist->id);
				            }
				   		}
				   }
				}

				 add_to_log(1, 'dean', 'discipline update', "blocks/dean/curriculum/addiscipline.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('disciplineupdate','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/disciplines.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
			} else  {
				error(get_string('errorinupdatingdisc','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/disciplines.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
			}
		}

	}

	$coursemenu[0] = get_string("selectacourses","block_dean")."...";
	$allcourses = get_records_sql ("SELECT id, fullname  FROM {$CFG->prefix}course ORDER BY fullname");
	if ($allcourses)  {
		foreach ($allcourses as $course1) 	{
		    $len = mb_strlen($course1->fullname, "UTF-8");
			if ($len > MAX_SYMBOLS_LISTBOX)  {
		    	$course1->fullname = mb_substr($course1->fullname,  0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
		    }
			$coursemenu[$course1->id] = $course1->fullname;
		}
	}

/*
	print_heading($faculty->name);
	print_heading($speciality->name);
	print_heading($curr->name);
*/
    print_dean_box_start("center");
?>

<tr valign="top">
    <td align="right"><b><?php  print_string("ffaculty","block_dean") ?>:</b></td>
    <td align="left"> <?php p($faculty->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("sspeciality","block_dean") ?>:</b></td>
    <td align="left"> <?php echo $speciality->number.'&nbsp;'.$speciality->name ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("curriculum","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->name) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("enrolyear","block_dean") ?>:</b></td>
    <td align="left"> <?php p($curriculum->enrolyear) ?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("formlearning","block_dean") ?>:</b></td>
    <td align="left"> <?php print_string("$curriculum->formlearning","block_dean")?> </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("qualification","block_dean") ?>:</b></td>
    <td align="left"> <?php p($speciality->qualification) ?> </td>
</tr>


<?php
    print_dean_box_end();


	$strhoursnumber = get_string("hoursnumber","block_dean");
	$strcontroltype = get_string("controltype","block_dean");
	$strauditorium	= get_string("auditoriumhours","block_dean");
	$strselfinstruction = get_string("selfinstructionhours","block_dean");

	print_heading($straddisc, "center", 4);

    print_dean_box_start("center");
?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "addiscipline.php?mode=add&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid";
												 else  echo "addiscipline.php?mode=update&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;did=$did&amp;oid=$oid";?>">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string("cipher","block_dean") ?>:</b></td>
    <td align="left">
		<input type="text" id="cipher" name="cipher" size="10" value="<?php p($rec->cipher) ?>" />
		<?php if (isset($err["cipher"])) formerr($err["cipher"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string('course') ?>:</b></td>
    <td align="left">
		<?php choose_from_menu ($coursemenu, 'courseid', $rec->courseid, false);
		  if (isset($err["courseid"])) formerr($err["courseid"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string('discipline', 'block_dean') ?>:</b></td>
    <td align="left">
		<input type="text" id="namedis" name="namedis" size="100" maxlength = "255" value="<?php p($rec->name) ?>" /> </td>
		<?php if (isset($err["namedis"])) formerr($err["namedis"]); ?>
</tr>
<tr valign="top">
    <td align="right"><b><?php  echo $strhoursnumber.'&nbsp;'.$strauditorium; ?>:</b></td>
    <td align="left">
		<input type="text" id="auditor" name="auditor" size="5" value="<?php p($rec->auditoriumhours) ?>" />
		<?php if (isset($err["auditor"])) formerr($err["auditor"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  echo $strhoursnumber.'&nbsp;'.$strselfinstruction; ?>:</b></td>
    <td align="left">
		<input type="text" id="selfi" name="selfi" size="5" value="<?php p($rec->selfinstructionhours) ?>" />
		<?php if (isset($err["selfi"])) formerr($err["selfi"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("term","block_dean") ?>:</b></td>
    <td align="left">
		<?php choose_from_menu (array (1 => 1,2,3,4,5,6,7,8,9,10,11,12), "term", $rec->term, ""); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  echo $strcontroltype ?>:</b></td>
    <td align="left">  <?php    unset($choices);
				    $choices['examination'] = get_string('examination', 'block_dean');
				    $choices['zaschet'] = get_string('zaschet', 'block_dean');
                    $choices['diffzaschet'] = get_string('diffzaschet', 'block_dean');
				    $choices['notis'] = get_string('notis', 'block_dean');
				    choose_from_menu ($choices, "controltype", $rec->controltype, ""); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("termpaper","block_dean") ?>:</b></td>
    <td align="left">
    		<input type="text" id="termpaperhours" name="termpaperhours" size="5" value="<?php p($rec->termpaperhours) ?>" />
    </td>
</tr>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("enroldiscgroup", "block_dean") ?>:</b></td>
    <td align="left">
            <input type="checkbox" value="1" align="LEFT" name="enrolgroup" />
    </td>
</tr>
</table>
   <div align="center">
     <input type="hidden" name="discid" value="<?php p($did)?>">
 	 <input type="submit" name="adddisc" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_dean_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_disc_errors(&$rec, &$err, $mode='add') {

/*
        if (empty($rec->courseid)) {
            $err["courseid"] = get_string("missingname");
		}
*/
        if ($rec->courseid == 1)  {
            if ($rec->name == get_string('frmdisname','block_dean')  || empty($rec->name)) {
            	$err["namedis"] = get_string('errdisname', 'block_dean');
            }
		}

		/*
        if (empty($rec->auditoriumhours) || $rec->auditoriumhours == 0) {
            $err["auditor"] = get_string("missingname");
		}
        if (empty($rec->selfinstructionhours) || $rec->selfinstructionhours == 0)	{
		    $err["selfi"] = get_string("missingname");
		}
        if (empty($rec->term))	{
		    $err["term"] = get_string("missingname");
		}
        if (empty($rec->controltype))	{
		    $err["controltype"] = get_string("missingname");
		}
        */

    return count($err);
}

?>