<?PHP // $Id: registrgroup.php,v 1.4 2013/10/09 08:47:49 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    define("MAX_USERS_PER_PAGE", 5000);

    $mode = required_param('mode', PARAM_INT);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);        // Faculty id
	$sid = required_param('sid', PARAM_INT);		// Speciality id
	$cid = required_param('cid', PARAM_INT);		// Curriculum id
	$gid = required_param('gid', PARAM_INT);		// Academygroup ID
	$ishowall = optional_param('iall', 0, PARAM_INT);		// Show all course
	$modecheck = optional_param('check', 0, PARAM_INT);		// Synchronise enrol/unenrol
	$catid = optional_param('cat', 0, PARAM_INT);		

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strreggroup = get_string('registergroupincourse', 'block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	// $strstudents = get_string("students","block_dean");
    $strstudents   = get_string("students");
    $strsearch        = get_string("search");
    $strsearchresults  = get_string("searchresults");
    $strshowall = get_string("showall");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= "-> $strreggroup";
    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);

    // echo '<hr>'. $ishowall . '<hr>';

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	else if (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }

	if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	}
	else if (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
    }


	if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
 	}

	if (!$academygroup = get_record('dean_academygroups', 'id', $gid)) {
        error("Group not found!");
 	}


	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("registrgroup.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("registrgroup.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("registrgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    listbox_group_pegas("registrgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid);
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode >= 4)  {

    $currenttab = 'registergroupincourse';
    include('tabsonegroup.php');

	if ($ishowall == 0)  {
		if($coursesincurr = get_records('dean_discipline', 'curriculumid', $cid, 'term')) {
			foreach($coursesincurr as $cc)   {
				if ($cc->courseid != 1) {
					$allcourse[$cc->term][] = get_record("course", 'id', $cc->courseid);
				}
			}
		}
	} else	{
	    // $allcourses = get_records("course", '', '', "fullname");
	    $allcourses = array();
	    if ($catid)	{
	    	$allcourses = get_records_sql ("SELECT id, fullname  FROM {$CFG->prefix}course
											WHERE category=$catid  
											ORDER BY fullname");
	    }	
	}

	/// A form was submitted so process the input

    if ($frm = data_submitted())   {
        if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {

		    $academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id);
            // print_r($academystudents); echo '<hr>';

            foreach ($frm->addselect as $addcourse) {
				/// Create a new group
				// debug
				// $crs = get_record('course', 'id', $addcourse);
				// print '<br>='.$crs->fullname.':'.$crs->fullname. '<br>';
				//
 			    if ($newgrp = get_record('groups', 'name', $academygroup->name, 'courseid', $addcourse))	{
 			    	notify ('Группа {$academygroup->name} уже подписана на курс с идентификатором $addcourse.', 'black');
 			    	continue;
 			        // $newgrpid = $newgrp->id;
 	                // delete_records("groups_members", "groupid", $newgrp->id);
 			    }  else {
	       	        $newgroup->name = $academygroup->name;
	           	    $newgroup->courseid = $addcourse;
					$newgroup->description = '';
					$newgroup->password = '';
					$newgroup->theme = '';
	               	$newgroup->lang = current_language();
	                $newgroup->timecreated = time();
	   	            if (!$newgrpid=insert_record("groups", $newgroup)) {
	       	            error("Could not insert the new group '$newgroup->name'");
	           	    } else {
	           	    	// notify("the new group '$newgroup->name'");
	   	            	add_to_log(1, 'dean', 'new moodle group registered', "blocks/dean/groups/registergroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
	           	    }
	           	}

				// $newgrp = get_record("groups", "name", $academygroup->name, "courseid", $addcourse);
			    if ($academystudents)  {
			   	   foreach ($academystudents as $astud)	  {
	                /// Enrol student
				     if ($usr = get_record('user', 'id', $astud->userid))	{
					    // print '-'.$usr->id.':'.$usr->lastname.' '. $usr->firstname. '<br>';
					    if ($usr->deleted != 1)	 {
							if (enrol_student_dean($astud->userid, $addcourse))  {

			                	  /// Delete duplicated students in the other group
			                      $strsql = "SELECT g.id, g.name, g.courseid, m.userid
											   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id = m.groupid
											   WHERE (g.courseid=$addcourse) AND (m.userid={$astud->userid})";
							   	  if ($duplgroups = get_records_sql($strsql))	{
										foreach ($duplgroups as $duplgroup)	 {
					 	                	 delete_records('groups_members', 'groupid', $duplgroup->id, 'userid', $astud->userid);
										}
			 				      }

			                	 /// Add people to a group
			                	 if (!$newmemberwas = get_record('groups_members', 'groupid', $newgrpid, 'userid', $astud->userid))	 {
								     $newmember->groupid = $newgrpid;
				    	             $newmember->userid = $astud->userid;
				        	         $newmember->timeadded = time();
				            	     if (!insert_record('groups_members', $newmember)) {
				                	    notify("Error occurred while adding user $astud->userid to group $academygroup->name");
					                 }
					             }

      			            } else {
    		                      error("Could not add student with id $astud->userid to the course $addcourse!");
    		                }
      			        } else {
      			        	delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
      			        }
      			     } else {
   			        	delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
      			     }
	               }
				}
            }
        } else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {

  	        $academystudents = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id);

            foreach ($frm->removeselect as $removecourse) {
	 		    if ($academystudents) 	{
	 		   		foreach ($academystudents as $astud)	  {
		                /// UnEnrol student
						unenrol_student_dean ($astud->userid, $removecourse);

 						// delete_records('dean_academygroups_members', 'userid', $removestudent);
						// add_to_log(1, 'dean', 'one student deleted from academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid", $USER->lastname.' '.$USER->firstname);
					}
				}
 			    if ($delgrp = get_record("groups", "name", $academygroup->name, "courseid", $removecourse))	{
	                delete_records("groups", "id", $delgrp->id);
 	                delete_records("groups_members", "groupid", $delgrp->id);
 	            }
			}
        } else if (!empty($frm->showall)) {
            unset($frm->searchtext);
            $frm->previoussearch = 0;
        }

    }

    $enrolcourses = array();
	$idcourses    = array();
	$numstudents  = array();
	$numgroups    = array();
	$dean_amembers = array();
    if ($mgroups = get_records("groups", "name", $academygroup->name))  {
    	$i=0;
		foreach($mgroups as $mgroup)  {
			$enrolcourses[$i] =  get_record("course", "id", $mgroup->courseid);
			$idcourses[$i] = $mgroup->courseid;
			if ($memgr = get_records('groups_members', 'groupid', $mgroup->id, 'userid'))	{
				$numstudents[$i] = count($memgr);
			} else {
				$numstudents[$i] = 0;
			}
			if ($agroupsw = get_records_sql("SELECT courseid, name FROM {$CFG->prefix}groups
											 WHERE courseid = {$mgroup->courseid} AND name = '{$mgroup->name}'"))   {
				$numgroups[$i] = count($agroupsw);
			} else {
				$numgroups[$i] = 0;
			}

			if ($damem = get_records('dean_academygroups_members', 'academygroupid', $gid)) 	{
				$dean_amembers[$i] = count($damem);
			} else {
				$dean_amembers[$i] = 0;
			}

			$i++;
		}
	}


    if ($modecheck == 1)		{

	    $academystudents = get_records('dean_academygroups_members', 'academygroupid', $gid);
	    if ($academystudents)  {

			$agroup_arr = array();
		    foreach ($academystudents as $d)	{
		    	$agroup_arr[] =  $d->userid;
		    }

			foreach($mgroups as $mgroup)  {

		    	$moodlestudents = get_records_sql("SELECT id, userid FROM {$CFG->prefix}groups_members WHERE groupid = {$mgroup->id}");
		    	if ($moodlestudents)	{

                    $mgroup_arr = array();
					foreach ($moodlestudents as $m)	{
				    	$mgroup_arr[] =  $m->userid;
				    }

                    $mgroup_diff = array_diff ($mgroup_arr, $agroup_arr);
	                // print_r($mgroup_diff);
	                // exit();

                    if (!empty($mgroup_diff))   {
                        foreach ($mgroup_diff as $mddid)	{

							 if (!unenrol_student_dean($mddid, $mgroup->courseid))  {
    		                      notify("ERROR. COULD NOT UNENROL student with id $mddid to the course {$mgroup->courseid}!!!!!!!");
		 			         } else {
				                  notify("<b>Студент с идентификатором $mddid исключен из курса с идентификатором {$mgroup->courseid}.</b>", 'black');
		 			         }
                        }
                    }

                    $agroup_diff = array_diff ($agroup_arr, $mgroup_arr);
                    // print_r($agroup_diff); echo '!<hr>';
                    // print_r($mgroup_diff); echo '#<hr>';
                    // continue;
	                if (!empty($agroup_diff))   {

	                     foreach ($agroup_diff as $addid)	{

							 if (!enrol_student_dean($addid, $mgroup->courseid))  {
			 		               notify("Could not add student with id $addid to the course {$mgroup->courseid}!");
		 			         } else {
				                   notify("Студент с идентификатором $addid подписан на курс с идентификатором {$mgroup->courseid}.", 'green');
		 			         }
/*
   	                       $strsql = "SELECT g.id, g.name, g.courseid, m.userid
										   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id = m.groupid
										   WHERE (g.courseid={$agroup->courseid}) AND (m.userid=$addid)";
									     if ($duplgroups = get_records_sql($strsql))	{
									foreach ($duplgroups as $duplgroup)	 {
				 	                	delete_records('groups_members', 'groupid', $duplgroup->id, 'userid', $addid);
									}
						     }
*/

			               	 if (!$newmemberwas = get_record('groups_members', 'groupid', $mgroup->id, 'userid', $addid))	 {
							     $newmember->groupid = $mgroup->id;
			    	             $newmember->userid = $addid;
			        	         $newmember->timeadded = time();
			            	     if (!insert_record('groups_members', $newmember)) {
			                	    notify("ERROR occurred while adding user $addid to group $academygroup->name!!!!!!!!");
				                 } else {
				                    notify("Студент с идентификатором $addid зачислен в группу $academygroup->name.", 'green');
				                 }
				             }

	 			         }
	 			    }

                    unset($agroup_diff);
                    unset($mgroup_diff);
	      		}
	      	}
	      	notice('Синхронизация завершена.', "registrgroup.php?mode=$mode&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;iall=$ishowall");
      	}
	}

/*
    $NN = $i;
    for ($i=0; $i<=$NN; $i++)	{
    	echo $enrolcourses[$i]->fullname .  '(' . $numstudents[$i] . ' - ' . $enrolcourses[$i]->id . ')  <br>';
    }
*/


   print_dean_box_start("center");

   // echo '<hr>'. $ishowall . '<hr>';

   $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>


<form name="studentform" id="studentform" method="post" action="registrgroup.php">
<input type="hidden" name="previoussearch" value="<?php echo $previoussearch ?>" />
<input type="hidden" name="mode" value="<?php echo $mode ?>" />
<input type="hidden" name="fid" value="<?php echo $fid ?>" />
<input type="hidden" name="sid" value="<?php echo $sid ?>" />
<input type="hidden" name="cid" value="<?php echo $cid ?>" />
<input type="hidden" name="gid" value="<?php echo $gid ?>" />
<input type="hidden" name="iall" value="<?php echo $ishowall ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
  <table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top" align="center">
          <?php
              echo get_string('listofenrollcourse', 'block_dean') . ': ' . count($enrolcourses) . '<br>';
          ?>
          <select name="removeselect[]" size="10" id="removeselect" multiple
                  onFocus="document.studentform.add.disabled=true;
                           document.studentform.remove.disabled=false;
                           document.studentform.addselect.selectedIndex=-1;" />
          <?php
	    	  $i=0;
              foreach ($enrolcourses as $ec) {
                  if ($dean_amembers[$i] !=  $numstudents[$i])	{
	                  $strfn = '!!!!! (' . $numstudents[$i] . ' - ' . $dean_amembers[$i] . ') ' . mb_substr($ec->fullname, 0, MAX_SYMBOLS_LISTBOX, "UTF-8");
                  } else {
	                  // $strfn = '(' . $numstudents[$i] . ' - ' . $dean_amembers[$i] . ')  ' . substr($ec->fullname,0, 150) ;
					  $strfn = mb_substr($ec->fullname, 0, MAX_SYMBOLS_LISTBOX, "UTF-8");
	              }

	              if ($numgroups[$i] > 1) {
	              		$strfn .=  ' (' . $numgroups[$i] . ')!!!!!' ;
	              }

                  echo "<option value=\"$ec->id\">$strfn</option>\n";
               	  $i++;
              }
          ?>

          </select>
      </td>
	</tr>
	<tr>
      <td valign="top" align="center">
        <input name="add" type="submit" id="add" value="&uarr;" />
        <input name="remove" type="submit" id="remove" value="&darr;" />
      </td>
	</tr>
    <tr>
      <td valign="top" align="center">
          <?php
           	  if ($ishowall == 0)  {
          ?>
          <select name="addselect[]" size="20" id="addselect" multiple
                  onFocus="document.studentform.add.disabled=false;
                           document.studentform.remove.disabled=true;
                           document.studentform.removeselect.selectedIndex=-1;">
          <?php
				for ($term=1; $term<=12; $term++)  {
				    echo "<optgroup label=\"". get_string("term","block_dean"). " $term\">\n";

		        	foreach ($allcourse[$term] as $ec) {
						if (!in_array($ec->id, $idcourses))  {
		                  $strfn = mb_substr($ec->fullname, 0, MAX_SYMBOLS_LISTBOX, "UTF-8");
	                	  echo "<option value=\"$ec->id\">$strfn</option>\n";
	                	}
		            }
					echo "</optgroup>\n";
				}
		  		 echo '</select>';
			  } else {
			  		
			        ?>
			          <table align="center" border="0">
    					<tr>
      						<td valign="top" align="center">
 								<?php echo get_string('categories').'<br>'; ?>     						
      						   <select name="cat" size="20" id="cat">
				         	 	<?php	   
    									$categories = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}course_categories ORDER BY name");;
    									foreach ($categories as $category) {
    										$strfn = mb_substr($category->name, 0, 30, "UTF-8");
    										echo "<option value=\"$category->id\">$strfn</option>\n";
    									}	
    							?>		
							 </select>
							 </td>
					         <td valign="middle" align="center">
        					  <input name="viewcat" type="submit" id="viewcat" value=">>" />
      						</td>
							 <td>
							 	<?php echo get_string('listofallcourse','block_dean') . ': ' . count($allcourses) .'<br>'; ?> 
							  <select name="addselect[]" size="20" id="addselect" multiple
				                  onFocus="document.studentform.add.disabled=false;
				                           document.studentform.remove.disabled=true;
				                           document.studentform.removeselect.selectedIndex=-1;">
			        <?php
					if ($catid)	{
			        	foreach ($allcourses as $ec) {
							if (!in_array($ec->id, $idcourses))  {
			                  $strfn = mb_substr($ec->fullname, 0, 100, "UTF-8");
		                	  echo "<option value=\"$ec->id\">$strfn</option>\n";
	
		                	}
			            }
			        }    
		            echo '</select></td></tr></table>';
			   }

          ?>
         
       </td>
    </tr>
  </table>
</form>

<table align="center" border="0" cellpadding="5" cellspacing="0">
	<tr>
      <td valign="top" align="center">
          <?php
        	 $options = array();
			 $options['fid'] = $fid;
			 $options['sid'] = $sid;
		     $options['cid'] = $cid;
             $options['gid'] = $gid;
		     $options['mode'] = $mode;
			 if ($ishowall == 0) {
 			     $options['iall'] = 1;
   		     	print_single_button("registrgroup.php", $options, get_string('showallcourse','block_dean'));
			 } else  {
				// if (empty($frm->add) && empty($frm->remove))  $options['mode'] = 9;
 			     $options['iall'] = 0;
   		     	print_single_button("registrgroup.php", $options, get_string('showcoursecurr','block_dean'));
			 }
          ?>
      </td>
      <td valign="top" align="center">
          <?php
        	 $options = array();
			 $options['fid'] = $fid;
			 $options['sid'] = $sid;
		     $options['cid'] = $cid;
             $options['gid'] = $gid;
		     $options['mode'] = $mode;
		     $options['iall'] = $ishowall;
		     $options['check'] = 1;
	     	 print_single_button("registrgroup.php", $options, 'Синхронизировать подписку');
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
