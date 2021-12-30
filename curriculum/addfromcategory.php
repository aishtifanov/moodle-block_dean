<?PHP // $Id: addfromcategory.php,v 1.1 2012/03/16 07:20:33 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $fid = required_param('fid', PARAM_INT);          // Faculty id
	$sid = required_param('sid', PARAM_INT);			// Speciality id
	$cid = required_param('cid', PARAM_INT);			// Curriculum id
	$term = optional_param('term', 1, PARAM_INT);	// # semestra
	$ccid = optional_param('ccid', 0, PARAM_INT);	// # semestra    

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

    $straddisc = 'Добавить дисциплины из категории';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid\"> $strdisciplines </a>";
	$breadcrumbs .= " -> $straddisc";
    print_header("$SITE->shortname: $straddisc", $SITE->fullname, $breadcrumbs);

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

    if ($ccid > 0)  {

    	$allcourses = get_records_sql ("SELECT id, fullname  FROM {$CFG->prefix}course
                                        WHERE category=$ccid 
                                        ORDER BY fullname");
    	if ($allcourses)  {
    		foreach ($allcourses as $course1) 	{
    			$coursemenu[$course1->id] = $course1->fullname;
                if (record_exists('dean_discipline', 'curriculumid', $cid, 'courseid', $course1->id))   {
                    notify("Дисциплина <b>$course1->fullname</b> уже есть в рабочем учебном плане.");
                }  else {
                    unset($rec);                    
                    $rec->curriculumid = $cid;
        			$rec->courseid = $course1->id;
        			$rec->cipher =  '-';
        			$rec->auditoriumhours = 0;
        			$rec->selfinstructionhours = 0;
        			$rec->term = $term;
        			$rec->termpaperhours = 0;
        			$rec->controltype = 'zaschet';
        			$rec->name = $course1->fullname;
                    $rec->timemodified = time();
                    if (insert_record('dean_discipline', $rec))	{
                        notify("Дисциплина <b>$rec->name</b> успешно добавлена в рабочий учебный план.", 'green');
                    }  else {
                         error(get_string('errorinaddingdisc','block_dean'), "$CFG->wwwroot/blocks/dean/curriculum/disciplines.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
                    }
                }      
    		}
    	}
    }
    
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    $url = $CFG->wwwroot. '/blocks/dean/curriculum/addfromcategory.php';
	listbox_course_category("$url?fid=$fid&sid=$sid&cid=$cid&term=$term&ccid=", $ccid);
	echo '</table>';

	print_footer();


function listbox_course_category($scriptname, $ccid)
{
  global $CFG;

  $disciplinemenu = array();
  $disciplinemenu[0] = get_string('missingcategory') . ' ...';

    
  $categories = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}course_categories ORDER BY name");
  foreach ($categories as $category) {
     $disciplinemenu[$category->id] = mb_substr($category->name, 0, 100, "UTF-8");
  }    

  echo '<tr><td>'.get_string('categories').':</td><td>';
  // popup_form($scriptname, $disciplinemenu, 'switchcategory', $ccid, '', '', '', false);
  popup_form($scriptname, $disciplinemenu, 'switchcategory', $ccid, '', '', '', false, 'self', '', null, 'Добавить в РУП');
  echo '</td></tr>';
  return 1;
}

?>