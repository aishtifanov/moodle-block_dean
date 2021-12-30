<?php // $Id: speciality.php,v 1.6 2013/09/03 14:01:07 shtifanov Exp $

    require_once("../../../config.php");
    // require_once('../lib.php');

    $fid = required_param('id', PARAM_INT);          // Faculty id

	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        lstgroupmember_download('xls', $fid);
        exit();
	}


	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();


    $strfaculty = get_string('faculty','block_dean');
    $strffaculty = get_string("ffaculty","block_dean");
    $strspeciality = get_string("speciality","block_dean");
    $strinformation = get_string("information","block_dean");
    $numbersp= get_string("numbersp","block_dean");
    $strname = get_string("name");
    $facultytab = "{$CFG->prefix}dean_faculty";
    $specialitytab="{$CFG->prefix}dean_speciality";
    $strqualification=get_string("qualification","block_dean");
	$straction = get_string("action","block_dean");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a> -> $strspeciality";
    print_header("$SITE->shortname: $strspeciality", $SITE->fullname, $breadcrumbs);


	if ($fid == 0)  {
	   $faculty = get_record('dean_faculty', 'id', 1);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
    }

    // add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, $strspeciality);

   	$allfacs = get_records_sql("SELECT id, name FROM {$CFG->prefix}dean_faculty where number>10000 ORDER BY number");
	$facultymenu[0] = get_string("selectafaculty","block_dean")."...";
	if ($allfacs)	{
		foreach ($allfacs as $facultyI) 	{
			$facultymenu[$facultyI->id] = $facultyI->name;
		}
	}

   	$allfacs = get_records_sql("SELECT id, name FROM {$CFG->prefix}dean_faculty where number<10000 ORDER BY number");
	if ($allfacs)	{
		foreach ($allfacs as $facultyI) 	{
			$facultymenu[$facultyI->id] = $facultyI->name;
		}
	}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    echo '<tr> <td>'.$strffaculty.': </td><td>';

	if ($fid == 0)  {
	    popup_form("speciality.php?id=", $facultymenu, "switchspeciality", 0, "", "", "", false);
		echo '</td></tr></table>';
	}
	else  {
	    popup_form("speciality.php?id=", $facultymenu, "switchspeciality", "$faculty->id", "", "", "", false);
		echo '</td></tr></table>';
        
        $currenttab = 'speciality';
        include("tabs.php");
		// echo "<hr />";

		if ($admin_is || $creator_is) {
	        $table->head  = array ($numbersp, $strname, $strqualification, get_string('countrup', 'block_dean'), get_string('countgroup', 'block_dean'), $straction);
    	    $table->align = array ('left', 'left', 'left', 'center', 'center',  'center');
		}
		else {
	        $table->head  = array ($numbersp, $strname, $strqualification, get_string('countrup', 'block_dean'));
    	    $table->align = array ('left', 'left', 'left', 'center', 'center');
		}

		$arr_specs =  get_records('dean_speciality', 'facultyid', $faculty->id, 'name');

		foreach ($arr_specs as $spec) {

			if ($admin_is || $creator_is) {
				$countrup = count_records('dean_curriculum', 'specialityid', $spec->id);
				$countgroups = count_records('dean_academygroups', 'specialityid', $spec->id);
 				$title = get_string('editspeciality','block_dean');
				$strlinkupdate = "<a title=\"$title\" href=\"addspeciality.php?mode=edit&amp;fid={$faculty->id}&amp;sid={$spec->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deletespeciality','block_dean');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delspeciality.php?fid={$faculty->id}&amp;sid={$spec->id}\">";
 				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";

				$title = get_string('curriculums','block_dean');
			    $strspecname = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=3&amp;fid={$faculty->id}&amp;sid={$spec->id}\"><strong>{$spec->name}</strong></a>";
				$table->data[] = array ($spec->number, $strspecname, $spec->qualification, $countrup, $countgroups, "$strlinkupdate");
			}
			else  if ($teacher_is) 	{
				$title = get_string('curriculums','block_dean');
			    $strspecname = "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=3&amp;fid={$faculty->id}&amp;sid={$spec->id}\"><strong>{$spec->name}</strong></a>";
				$table->data[] = array ($spec->number, $strspecname, $spec->qualification, $countrup);
			}
			else {
				$table->data[] = array ($spec->number, $spec->name, $spec->qualification, $countrup);
			}

		}
		print_heading("$strspeciality", "center");
    	print_table($table);
		if ($admin_is || $creator_is) {
            ?>	<table align="center">
            	<tr>
            	<td>
              <form name="addspec" method="post" action="<?php echo "addspeciality.php?mode=new&amp;fid={$faculty->id}" ?>">
            	    <div align="center">
            		<input type="submit" name="addspeciality" value="<?php print_string('addspeciality','block_dean')?>">
            	    </div>
              </form>
              </td>
            	<td>
            	<form name="download" method="post" action="<?php echo "speciality.php?action=excel&amp;id={$faculty->id}" ?>">
            	    <div align="center">
            		<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
            	    </div>
              </form>
            	</td>
            	</tr>
            	<tr> <td colspan = 2>
            	<hr>
            	<form name="download" method="post" action="<?php echo "importfaculty.php?fid={$faculty->id}" ?>">
            	    <div align="center">
            		<input type="submit" name="importfaculty" value="<?php print_string('importfaculty','block_dean')?>">
            	    </div>
              </form>
            
            	</td></tr>
            	<tr> <td colspan = 2>
            	<form name="download" method="post" action="<?php echo "sybchrfaculty.php?fid={$faculty->id}" ?>">
            	    <div align="center">
            		<input type="submit" name="sybchrfaculty" value="<?php print_string('sybchrfaculty','block_dean')?>">
            	    </div>
              </form>
            
            	</td></tr>
            	<tr> <td colspan = 2>
            	<hr>
            	<form name="download" method="post" action="<?php echo "enrallstud.php?fid={$faculty->id}" ?>">
            	    <div align="center">
            		<input type="submit" name="enrallstud" value="<?php print_string('enrallstud', 'block_dean')?>">
            	    </div>
              </form>
            
            	</td></tr>
            
            	<tr> <td colspan = 2>
            	<form name="download" method="post" action="<?php echo "statsfaculty.php?fid={$faculty->id}" ?>">
            	    <div align="center">
            		<input type="submit" name="statsfaculty" value="<?php print_string('statstoza', 'block_dean')?>">
            	    </div>
              </form>
            
            	</td></tr>
            
              </table>
            <?php
		}
	}

    print_footer();

function lstgroupmember_download($download, $fid)
{
    global $CFG;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");


		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "speciality";
        header("Content-Disposition: attachment; filename=\"$downloadfilename.xls\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

/// Creating a workbook
        $workbook = new Workbook("-");
        $myxls =& $workbook->add_worksheet($downloadfilename);

/// Print names of all the fields
		$formath1 =& $workbook->add_format();
		$formath2 =& $workbook->add_format();
		$formatp =& $workbook->add_format();

		$formath1->set_size(12);
	    $formath1->set_align('center');
	    $formath1->set_align('vcenter');
		$formath1->set_color('black');
		$formath1->set_bold(1);
		$formath1->set_italic();
		// $formath1->set_border(2);

		$formath2->set_size(11);
	    $formath2->set_align('center');
	    $formath2->set_align('vcenter');
		$formath2->set_color('black');
		$formath2->set_bold(1);
		//$formath2->set_italic();
		$formath2->set_border(2);
		$formath2->set_text_wrap();

		$formatp->set_size(11);
	    $formatp->set_align('left');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

        $txtl = new textlib();
		$faculty = get_record('dean_faculty', 'id', $fid);

		$myxls->set_column(0,0,4);
		$myxls->set_column(1,1,20);
		$myxls->set_column(2,2,60);
		$myxls->set_column(3,3,20);
		$myxls->set_row(0, 30);

		$strwin1251 =  $txtl->convert($faculty->name, 'utf-8', 'windows-1251');
        $myxls->write_string(0,0,$strwin1251,$formath1);
		$myxls->merge_cells(0, 0, 0, 3);



        $myxls->write_string(1,0, 'N' ,$formath2);

        $numbersp = get_string('numbersp','block_dean');
        $strwin1251 =  $txtl->convert($numbersp, 'utf-8', 'windows-1251');
    	$myxls->write_string(1, 1, $strwin1251, $formath1);

        $speciality = get_string('speciality','block_dean');
    	$strwin1251 =  $txtl->convert($speciality, 'utf-8', 'windows-1251');
    	$myxls->write_string(1, 2, $strwin1251, $formath1);

        $qualification = get_string('qualification','block_dean');
        $strwin1251 =  $txtl->convert($qualification, 'utf-8', 'windows-1251');
    	$myxls->write_string(1, 3, $strwin1251, $formath1);

		$faculty = get_record('dean_faculty', 'id', $fid);

		$all_specs =  get_records('dean_speciality', 'facultyid', $faculty->id, 'name');

		if ($all_specs)	{
            $i = 1;
			foreach ($all_specs as $spec) 	{
				$i++;
    	       	$myxls->write_string($i,0,($i-1).'.',$formatp);
    	       	$myxls->write_string($i,1,$spec->number,$formatp);
    			$strwin1251 =  $txtl->convert($spec->name, 'utf-8', 'windows-1251');
        	    $myxls->write_string($i,2,$strwin1251,$formatp);
        	    $strwin1251 =  $txtl->convert($spec->name, 'utf-8', 'windows-1251');
           	   	$myxls->write_string($i,3,$strwin1251,$formatp);
	 		 }
	  	     $i++;
  	   		 $vsego = get_string('vsego','block_dean');
  	   		 $strwin1251 =  $txtl->convert($spec->name, 'utf-8', 'windows-1251');
  	   		 $myxls->write_string($i,2,$strwin1251,$formath1);
       		 $myxls->write_formula($i, 3, "=COUNTA(D3:D$i)", $formath1);
		}

       $workbook->close();
       exit;
	}
}

?>


