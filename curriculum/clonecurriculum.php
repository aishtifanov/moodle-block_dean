<?PHP // $Id: clonecurriculum.php,v 1.2 2009/09/02 12:08:29 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

   // $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $fid = required_param('fid', PARAM_INT);          // Faculty id
	$sid = required_param('sid', PARAM_INT);			// Speciality id
	$cid = required_param('cid', PARAM_INT);			// Curriculum id

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


    $strfaculty = get_string('faculty','block_dean');
	$strspeciality = get_string("speciality", "block_dean");
	$strcurriculums = get_string('curriculums','block_dean');

    $straddcurr = get_string('addcurriculum','block_dean');
//	else $straddcurr = get_string('updatecurriculum','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> $straddcurr";
    print_header("$site->shortname: $straddcurr", "$site->fullname", $breadcrumbs);

	$rec->facultyid = $fid;
	$rec->specialityid = $sid;
	$rec->code = 0;
	$rec->name = "";
	$rec->enrolyear = "";
	$rec->formlearning = "";
	$rec->description = "";


	 if($rec = get_record('dean_curriculum','id',$cid)){
	 	 if ($cloneid = insert_record('dean_curriculum',$rec)){
			   $discipline = get_records('dean_curriculum','id',$cid);
		   foreach ($discipline as $discip){
				 $discip->curriculumid = $cloneid;
			 	$past = insert_record('dean_discipline',$discip->curriculumid);
				 notify('Рабочий учебный план скопирован');
		   }
	     }
      }
 ?>
 <table align="center">
	<tr>
	<td>
  <form name="next" method="post" action="curriculum.php?mode=2">
	       <input type="hidden" name="fid" value="<?php echo $fid ?>" />
   <input type="hidden" name="sid" value="<?php echo $sid ?>" />
   <input type="hidden" name="cid" value="<?php echo $cid ?>" />
   <input type="hidden" name="gid" value="<?php echo $gid ?>" />
   <input type="hidden" name="did" value="<?php echo $did ?>" />
   <input type="hidden" name="rid" value="<?php echo $rid ?>" />
   <input type="hidden" name="term" value="<?php echo $term ?>" />
   <input type="hidden" name="mod" value="<?php echo $mod ?>" />
   <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>" />
	    <div align="center">
		<input type="submit" name="curriculum" value="<?php print_string('backtocurriculum','block_dean')?>">
		 </div>
  </form>
  	</td>
  </table>



 <?php
	// print_heading($faculty->name);
//	print_heading($speciality->name);
	//print_heading($straddcurr, "center", 3);

   // print_dean_box_start("center");

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_curr_errors(&$rec, &$err, $mode='add') {


        if (empty($rec->name))	{
		    $err["name"] = get_string("missingname");
		}

    return count($err);
}

?>