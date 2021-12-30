<?php // $Id: excel.php,v 1.2 2011/03/22 06:56:09 shtifanov Exp $

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
    
    if ($USER->id == 59682) {
        $admin_is = true;
    } 


    add_to_log(SITEID, 'dean', 'retakes view', 'retake.php', $strretakes);



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
       // require_once("reports.php");
    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $gid = required_param('gid', PARAM_INT);          // Group id
	$tabroll = optional_param('tabroll', 'itogisessii', PARAM_ALPHA);
    $term = optional_param('term', 1, PARAM_INT);		// # semestra

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
	    $formatp->set_align('center');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

		$myxls->set_column(0,0,10);
		$myxls->set_column(1,1,20);
		$myxls->set_column(2,2,20);
		$myxls->set_column(3,3,20);
		$myxls->set_column(4,4,25);
		$myxls->set_column(5,5,15);
		$myxls->set_row(0, 30);
        //$myxls->write_string(0,0,$strretakes,$formath1);

        $titleText = ('Успеваемость группы');
		$myxls->write_string(0,0,$titleText,$formath2);
		$myxls->merge_cells(0, 0, 0, 4);
        $myxls->write_string(1,0, 'Группа',$formath2);
        $myxls->write_string(1,1, 'Число отличников',$formath2);
        $myxls->write_string(1,2,'Качество знаний (на 4 и 5)',$formath2);
        //$myxls->write_string(1,2,get_string('group','block_dean'),$formath2);
        $myxls->write_string(1,3,'Число студентов, сдавших сессию',$formath2);
        $myxls->write_string(1,4,'Число студентов, имеющтх задолжность',$formath2);



//$myxls->write_string(0,0, $retakeI->academygroupid,$formath2);
		$agroup = get_record('dean_academygroups', 'id', $gid);

        $students = get_records('dean_academygroups_members', 'academygroupid', $gid);
        $kolstudents= count($students);

        $disciplines =  get_records_sql ("SELECT *
		 							    FROM  {$CFG->prefix}dean_discipline
									    WHERE curriculumid={$agroup->curriculumid} AND term=$term
									    ORDER BY courseid");
        $koldiscipline = count($disciplines);
         $kolexz = 0;

         foreach ($disciplines as $discipl){
         if ($discipl->controltype == 'examination'){
         	$kolexz++;
         }
         }

         $sdal = $otlichniki =  $dolg = 0;

     if ($students)
       foreach ($students as $student)  {

		    $mark = array();
            $i=0;
        	foreach ($disciplines as $discipline) 	{

	            if (!$roll =  get_record('dean_rolls', 'disciplineid', $discipline->id, 'academygroupid', $gid)) continue;

                $rol = get_record_sql ("SELECT *
				 					FROM {$CFG->prefix}dean_roll_marks
				 					WHERE rollid={$roll->id} AND studentid={$student->userid}");

	            if (!$rol)  {
	               error(get_string('nomarks', 'block_dean'), '..\faculty\faculty.php');
	            } else {
	                // print_r($rol->mark); echo '<hr>';
	            	$mark[$i++] = $rol->mark;
	            }
             }

            if (!empty($mark))	{

            	$kolmark = count($mark);
   	           // print_r($mark); echo $kolmark; echo '<hr>';

          		$kolhormark = $kolotlmark = $sum_hor_mark = 0;
	            for ($i=0; $i<$kolmark;$i++){
	            	if (($mark[$i] > 2 && $mark[$i] <= 5) || $mark[$i] == 1)  {
	            		$kolhormark++;
	            	}//else{error(get_string('nomarks','block_dean'));}

	            	if ($mark[$i] == 4 || $mark[$i] == 5)  {
	            		$sum_hor_mark += $mark[$i];
	            	}


	            }


	            if ($kolhormark == $kolmark)  {
	                 	$sdal++;
                        $max_otl = $kolexz*5;
                        if ($max_otl == 0) { continue; }
			            $proc = ($sum_hor_mark*1.0)/($max_otl*100.0);
			            //echo $proc.'<hr>';
			            if ($proc > 75)  $otlichniki++;


	            }	else {
                	$dolg++;
                }
	        }
	            //$kolmark = count($mark);
			    unset($mark);
           // $avg_mark = array_sum($mark)/$kolmark;
       }
 		$kach = round (($otlichniki*1.0)/($kolstudents*100.0));

        $myxls->write_string(2,0,$agroup->name,$formatp);
        $myxls->write_string(2,1,$otlichniki,$formatp);
    	$myxls->write_string(2,2,$kach,$formatp);
        $myxls->write_string(2,3,$sdal,$formatp);
        $myxls->write_string(2,4,$dolg,$formatp);
       $workbook->close();
       exit;
	}
}

?>

