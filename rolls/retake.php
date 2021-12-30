<?php // $Id: retake.php,v 1.2 2011/03/22 06:56:09 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    //require_once"Spreadsheet/Excel/Writer.php";



	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        lstgroupmember_download('xls');
        exit();
	}

    $strretakes = get_string('retakes','block_dean');
    $numberofretake = '№'; // get_string('numberf','block_dean');
    $strname = get_string('name');
    $strdateoftake=get_string('dateoftake','block_dean');
	$strdateofgive=get_string('dateofgive','block_dean');
 	$strmark=get_string('mark','block_dean');
 	$straction = get_string('action','block_dean');


    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$admin_is = isadmin();
	$creator_is = iscreator();

    // add_to_log(SITEID, 'dean', 'retakes view', 'retake.php', $strretakes);

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strretakes";
    print_header("$site->shortname: $strretakes", $site->fullname, $breadcrumbs);

	print_heading($strretakes, "center");

		if ($admin_is || $creator_is) {
	        $table->head  = array ($numberofretake, $strname, $strdateoftake, $strdateofgive, $strmark, $straction);
    	    $table->align = array ("center", "left", "left", "left", "left", "left");
		}
		else  {
	        $table->head  = array ($numberofretake, $strname, $strdateoftake, $strdateofgive, $strmark);
    	    $table->align = array ("center", "left", "left", "left", "left");
		}

		$allfacs = get_records_sql("SELECT * FROM {$CFG->prefix}dean_reroll ORDER BY disciplineid");
		if ($allfacs)	{
			foreach ($allfacs as $retakesI) 	{
			$linkname = "<strong><a href=$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$retakeI->id>$retakeI->studentid</a></strong>";
				if ($admin_is || $creator_is) {
	 				$title = get_string('editretake','block_dean');
					$strlinkupdate = "<a title=\"$title\" href=\"addreroll.php?mode=edit&amp;rid={$retakeI->id}\">";
					$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
					$title = get_string('deleteretake','block_dean');
				    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"delretake.php?rid={$retakeI->id}\">";
	 				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
					$dean_user = get_record('user', 'id', $retakeI->studentid);
	  				$dname = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$dean_user->id.'&amp;course=1">'.fullname($dean_user).'</a></strong>';
					$table->data[] = array ($retakeI, $linkname, $dname, $datoftake, $datofgive, $datofgive, $strlinkupdate);
	            }
				else   {
					$dean_user = get_record('user', 'id', $retakeI->academygroupid);
	  				$dname = fullname($dean_user);
					$table->data[] = array ($retakeI, $linkname, $dname, $datoftake, $datofgive, $datofgive);
				}
	        }
		}

    print_table($table);

	if ($admin_is || $creator_is) {
?><table align="center">
	<tr>
	<td>
  <form name="addfac" method="post" action="addfaculty.php?mode=new">
	    <div align="center">
		<input type="submit" name="addfaculty" value="<?php print_string('addfaculty','block_dean')?>">
		 </div>
  </form>
  	</td>
	<td>
	<form name="download" method="post" action="retake.php?action=excel">
	    <div align="center">
		<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
	    </div>
  </form>
	</td>
	</tr>
  </table>
<?php
	}
    print_footer();

 $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
				u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                t.timeaccess as lastaccess, m.academygroupid
                    FROM {$CFG->prefix}user u
                 LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
				WHERE m.academygroupid = $gid AND u.deleted = 0 AND u.confirmed = 1
				ORDER BY lastname ASC";
    $s=get_record_sql($sql);
function lstgroupmember_download($download)
{
    global $CFG;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");


		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "retakes";
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

		$myxls->set_column(0,0,4);
		$myxls->set_column(1,1,20);
		$myxls->set_column(2,2,25);
		$myxls->set_column(3,3,20);
		$myxls->set_column(4,4,15);
		$myxls->set_column(5,5,15);
		$myxls->set_row(0, 30);
        //$myxls->write_string(0,0,$strretakes,$formath1);

        $titleText = ('Ведомость пересдач группы');
		$myxls->write_string(0,0,$titleText,$formath2);
		$myxls->merge_cells(0, 0, 0, 5);

        $myxls->write_string(1,0, 'N' ,$formath2);
        $myxls->write_string(1,1,'№ пересдачи' ,$formath2);
        //$myxls->write_string(1,2,get_string('group','block_dean'),$formath2);
        $myxls->write_string(1,2,get_string('username','block_dean'),$formath2);
        $myxls->write_string(1,3,get_string('dateoftake','block_dean'),$formath2);
		$myxls->write_string(1,4,get_string('dateofgive','block_dean'),$formath2);
		$myxls->write_string(1,5,get_string('mark','block_dean'),$formath2);


//$myxls->write_string(0,0, $retakeI->academygroupid,$formath2);
		$allfacs = get_records_sql("SELECT * FROM {$CFG->prefix}dean_reroll ORDER BY studentid");
		if ($allfacs)	{
            $i = 1;
			foreach ($allfacs as $retakeI) 	{
				$dean_user = get_record('user', 'id', $retakeI->studentid);
			    $i++;
    	       	$myxls->write_string($i,0,($i-1).'.',$formatp);
    	       	$myxls->write_string($i,1,$retakeI->retake,$formatp);
           	    $myxls->write_string($i,2,fullname($dean_user),$formatp);
        	    $myxls->write_string($i,3,convert_date($retakeI->datoftake, 'en', 'ru'),$formatp);
        	    $myxls->write_string($i,4,convert_date($retakeI->datofgive, 'en', 'ru'),$formatp);

       			if ($retakeI->mark==1){
				$myxls->write_string($i,5,get_string('n_1','block_dean'),$formatp);
                   }
				if ($retakeI->mark==2){
				$myxls->write_string($i,5,get_string('n_2','block_dean'),$formatp);
                   }
      			if ($retakeI->mark==3){
				$myxls->write_string($i,5,get_string('n_3','block_dean'),$formatp);
                   }
    			if ($retakeI->mark==4){
				$myxls->write_string($i,5,get_string('n_4','block_dean'),$formatp);
                   }
			    if ($retakeI->mark==5){
				$myxls->write_string($i,5,get_string('n_5','block_dean'),$formatp);
                   }
			    if ($retakeI->mark==6){
				$myxls->write_string($i,5,get_string('n_6','block_dean'),$formatp);
                   }
			    if ($retakeI->mark==7){
				$myxls->write_string($i,5,get_string('n_7','block_dean'),$formatp);
                   }

              }
	  	     $i++;
  	   		 $myxls->write_string($i,2,get_string('vsego','block_dean'),$formath1);
       		 $myxls->write_formula($i, 3, "=COUNTA(D3:D$i)", $formath1);


		}

       $workbook->close();
       exit;
	}
}

?>

