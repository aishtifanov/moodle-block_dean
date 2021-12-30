<?php // $Id: formacademygroup.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');


    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
	$cid = required_param('cid', PARAM_INT);			// Curriculum id

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strffaculty = get_string("ffaculty","block_dean");
    $strspeciality = get_string("speciality","block_dean");
    $strsspeciality = get_string("sspeciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= "-> $strgroups";

    print_header("$site->shortname: $strgroups", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0 && $sid != 0)  {
       error(get_string("errorselect","block_dean"), "academygroup.php?mode=1&amp;fid=0&amp;sid=0&amp;cid=0");
	}

	if ($fid == 0 && $cid != 0)  {
       error(get_string("errorselect","block_dean"), "academygroup.php?mode=1&amp;fid=0&amp;sid=0&amp;cid=0");
	}

	if ($sid == 0 && $cid != 0)  {
       error(get_string("errorselectspec","block_dean"), "academygroup.php?mode=1&amp;fid=$fid&amp;sid=0&amp;cid=0");
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

	if ($cid == 0)  {
	   $curriculum = get_record_sql("SELECT * FROM {$CFG->prefix}dean_curriculum ", true);
	}
	elseif (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
        error(get_string('errorcurriculum', 'block_dean'), '..\curriculum\curriculum.php?mode=1&fid=0&sid=0');
    }

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);


	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("academygroup.php?mode=1&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("academygroup.php?mode=2&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("academygroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=", $fid, $sid, $cid);
	echo '</table>';

	if ($fid != 0 && $sid != 0 && $cid != 0 && $mode > 2)  {
	    $currenttab = 'frommoodletoacademy';
	    include('tabsgroups.php');

	    $strcgroups = get_string('coursegroup', 'block_dean');
	    $strugroups = get_string('academygroup', 'block_dean');

		print_heading(get_string('formacademygroupfromcourse', 'block_dean'),'center', 4);

		// Get all groups in courses
		// One SQL-query !!!!!!!!
		$allgroups = array();
		$groups = get_records_sql ("SELECT id, name	FROM {$CFG->prefix}groups");
		if ($groups)	{
	        foreach ($groups as $group) {
				$allgroups[$group->id] = $group->name;
			}
		}

		// Delete dublicate name and sorted
		$uniquegroups1  = array_unique($allgroups);
		natsort($uniquegroups1);

		// Delete selected university (academy) group
		if (isset($_POST["unigroupsdel"]) && isset($_POST["groupsinspecform"]))  {
			$arr = $_POST["groupsinspecform"];
	    	foreach ($arr as $agroup) {
				$metagr = get_record('dean_academygroups', 'name', $agroup);
				delete_records('dean_academygroups', 'name', $agroup);
				delete_records('dean_academygroups_members', 'academygroupid', $metagr->id);
			}
		}

		// Get all university (academy) group
		$ugroups0 = get_records_sql ("SELECT id, name FROM {$CFG->prefix}dean_academygroups");
		if ($ugroups0)  {
	        foreach ($ugroups0  as $u0) {
					$uallgroups[] = $u0->name;
			}
			// Get groups not in university
			$uniquegroups  = array_diff ($uniquegroups1, $uallgroups);
		}
		else $uniquegroups = $uniquegroups1;

		// Get university group only one faculty and one speciality and one curriculum
		$ugroups1 = get_records_sql ("SELECT id, name
									  FROM {$CFG->prefix}dean_academygroups
									  WHERE facultyid=$fid AND specialityid=$sid AND curriculumid=$cid");

		if ($ugroups1)  {
	        foreach ($ugroups1  as $u1) {
				$groupsinspec[] = $u1->name;
			}
			natsort($groupsinspec);
		}

		// Add selected group in university
		if (isset($_POST["nongroupsadd"]) && isset($_POST["uniquegroupsform"]))  {
			$arr = $_POST["uniquegroupsform"];
	    	foreach ($arr as $agroup) {
        	  	$groupsinspec[] = $agroup;

				$rec->name = $agroup;
				$rec->facultyid = $fid;
				$rec->specialityid = $sid;
				$rec->curriculumid = $cid;
				$rec->kurs = 1;
				$rec->description = '';
				$academygroupid = insert_record('dean_academygroups', $rec);
				add_to_log(1, 'dean','one academygroup added', "blocks/dean/groups/formacademygroup.php?mode=new&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				if(!$academygroupid)  {
					error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/formacademygroup.php?mode=9&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
				}
				else  {
					// Add academy_members in academy_groups
					$cgroups = get_records('groups', 'name', $agroup);
					$allmembersID = array(); // Input ALL member in ones array
					if ($cgroups)  {
						foreach ($cgroups as $cg1)   {
							if ($members = get_group_users($cg1->id))  {
								foreach ($members as $mem)   {
										$allmembersID[] = $mem->id;
								}
							}
						}
						$uniquemembers1 = array_unique($allmembersID);

						foreach ($uniquemembers1 as $memid)  {
							$rec1->userid = $memid;
							$rec1->academygroupid = $academygroupid;
							if(!insert_record('dean_academygroups_members', $rec1))  {
								error(get_string('errorinaddinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/formacademygroup.php?mode=99&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
							}
						}
					}
				}
			}
			$uniquegroups = array_diff ($uniquegroups, $arr);
		}


?>
<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">
    <tr>
      <th width="45%" align="center" class="header"><?php p($strcgroups) ?></td>
      <th width="10%" align="center" class="header"> </td>
      <th width="45%" align="center" class="header"><?php p($strugroups) ?></td>
    </tr>
    <tr align="center" valign="top">
      <td class="generalboxcontent"><p>
        <form name="form1" id="form1" method="post" action="formacademygroup.php<?php echo "?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid"; ?>">
          <select name="uniquegroupsform[]" size="15" multiple="multiple">
            <?php
                if (!empty($uniquegroups)) {
                    foreach ($uniquegroups as $agroup) {
                        echo "<option value=\"$agroup\">$agroup</option>";
                    }
                }
            ?>
          </select>
          </p>
          <p>
	  </td>
   	  <td class="generalboxcontent" valign="center"><p>
            <input type="submit" name="nongroupsadd" value="<?php print_string('add') ?> --&gt;"/>
        </form>
        <form name="form2" id="form2" method="post" action="formacademygroup.php<?php echo "?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid"; ?>">
          <input type="submit" name="unigroupsdel" value="&lt;-- <?php print_string('delete') ?> "/>
      </td>

      <td class="generalboxcontent"><p>
          <select name="groupsinspecform[]" size="15" multiple="multiple">
            <?php

                if (!empty($groupsinspec)) {
                    foreach ($groupsinspec as $agroup) {
                        echo "<option value=\"$agroup\">$agroup</option>";
                    }
                }

            ?>
          </select>
          </p>
		</form>
      </td>
    </tr>
  </table>
<?php
	}

    print_footer();

?>


