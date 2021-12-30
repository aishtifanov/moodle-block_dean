<?php // $Id: retakeone.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    //require_once"Spreadsheet/Excel/Writer.php";
   // $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		  // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $did = required_param('did', PARAM_INT);          // Discipline id
    $rid = required_param('rid', PARAM_INT);          // Roll id
	$term = optional_param('term', 1, PARAM_INT);	  // # semestra
	$tabroll = optional_param('tabroll', 'zaschetexam', PARAM_ALPHA);
	//$uid = required_param('uid', PARAM_INT);


	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        lstgroupmember_download('xls');
        exit();
	}



function lstgroupmember_download($download)
{
    global $CFG, $fid, $sid, $cid, $gid, $did, $uid, $retake;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");

     //  $roll =  get_record('dean_reroll', 'id', $rid);
  //     $t = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
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
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
 	   }

	   if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	   }
	   elseif (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
       }


       $agroup = get_record('dean_academygroups', 'id', $gid);
       $discipline = get_record('dean_discipline', 'id', $did);
       $student = get_record('user', 'id', $uid);
       $recordbook = get_record('dean_student_studycard','userid',$uid);
       $studrerol = get_record('dean_reroll','studentid', $uid, 'retake', $retake);
       $teacher =  get_record('user', 'id', $studrerol->teacherid);
       $uchstepen =  get_record('dean_teacher_card', 'userid', $studrerol->teacherid);

       if (!$curriculum = get_record('dean_curriculum', 'id', $cid)) {
		error(get_string('errorcurriculum', 'block_dean'), 'curriculum.php?mode=1&fid=0&sid=0');
 	   }
        $form = get_string($curriculum->formlearning,'block_dean');

       $kurs = get_record_sql("SELECT * FROM {$CFG->prefix}dean_academygroups", true);
       $cours = get_kurs($kurs->startyear);


		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "retakeone";
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
		$formatp1 =& $workbook->add_format();
		$formatp2 =& $workbook->add_format();
		$formatp3 =& $workbook->add_format();



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
		//$formath2->set_border(2);
		$formath2->set_text_wrap();

		$formatp->set_size(8);
	    $formatp->set_align('center');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		//$formatp->set_border(1);
		$formatp->set_text_wrap();

		$formatp1->set_size(11);
	    $formatp1->set_align('left');
	    $formatp1->set_align('vcenter');
		$formatp1->set_color('black');
		$formatp1->set_bold(0);
		//$formatp->set_border(1);
		$formatp1->set_text_wrap();

		$formatp2->set_size(11);
	    $formatp2->set_align('left');
	    $formatp2->set_align('vcenter');
		$formatp2->set_color('black');
		//$formatp2->set_bold(0);
		$formatp2->set_bottom(1);
		$formatp2->set_text_wrap();

		$formatp3->set_size(11);
	    $formatp3->set_align('center');
	    $formatp3->set_align('vcenter');
		$formatp3->set_color('black');
		$formatp3->set_bold(0);
		//$formath2->set_italic();
		//$formath2->set_border(2);
		$formatp3->set_text_wrap();

        /*
		$formatr->set_size(11);
	    $formatr->set_align('left');
	    $formatr->set_align('vcenter');
		$formatr->set_color('black');
		$formatr->rotation = 90;
         */
		$myxls->set_column(0,0,10);
		$myxls->set_column(1,1,30);
		$myxls->set_column(2,2,10);
		$myxls->set_column(3,3,5);
		$myxls->set_column(4,4,15);
		$myxls->set_column(5,5,15);
		$myxls->set_row(0, 30);
        //$myxls->write_string(0,0,$strretakes,$formath1);

        $titleText = get_string('BGU', 'block_dean');
		$myxls->write_string(0,0,$titleText,$formath2);
		$myxls->merge_cells(0, 0, 0, 5);
		$myxls->write_string(1,0, get_string('vusname','block_dean'),$formatp);
		$myxls->merge_cells(1, 0, 1, 5);
		$myxls->write_string(2,0,$form,$formatp3);
		$myxls->merge_cells(2, 0, 3, 2);
		$myxls->write_string(2,4,get_string('numret1','block_dean'),$formatp1);
		$myxls->write_string(3,4,get_string('numret2','block_dean'),$formatp1);
		$myxls->write_string(4,4,get_string('numret3','block_dean'),$formatp1);
		$myxls->write_string(5,0,get_string('exlist','block_dean'),$formath2);
		$myxls->merge_cells(5, 0, 5, 5);
		$myxls->write_string(6,0,get_string('note','block_dean'),$formatp);
		$myxls->merge_cells(6, 0, 6, 5);
		$myxls->write_string(7,0,get_string('facultyy','block_dean').$faculty->name,$formatp2);
		$myxls->write_string(7,1,' ',$formatp2);
		$myxls->merge_cells(7, 0, 7, 1);
		$myxls->write_string(7,2,get_string('cours','block_dean').$cours,$formatp2);
		$myxls->write_string(7,3,' ',$formatp2);
		$myxls->merge_cells(7, 2, 7, 3);
		$myxls->write_string(7,4,get_string('groupp','block_dean').$agroup->name,$formatp2);
	//	$myxls->write_string(7,5,' ',$formatp2);
		$myxls->merge_cells(7, 4, 7, 5);
		$myxls->write_string(8,0,get_string('disciplinee','block_dean').$discipline->name,$formatp2);
		$myxls->write_string(8,1,' ',$formatp2);
		$myxls->write_string(8,2,' ',$formatp2);
		$myxls->write_string(8,3,' ',$formatp2);
		$myxls->merge_cells(8, 0, 8, 5);
		$myxls->write_string(9,0,get_string('exeminer','block_dean').$uchstepen->uchstepen.' '.$uchstepen->uchzvanie.' '.$teacher->lastname.' '.$teacher->firstname,$formatp2);
		$myxls->write_string(9,1,' ',$formatp2);
		$myxls->write_string(9,2,' ',$formatp2);
		$myxls->write_string(9,3,' ',$formatp2);
		$myxls->merge_cells(9, 0, 9, 5);
		$myxls->write_string(10,0,get_string('uchzvan','block_dean'),$formatp);
		$myxls->merge_cells(10, 0, 10, 5);
		$myxls->write_string(11,0,get_string('fiostud','block_dean').$student->lastname.'  '.$student->firstname,$formatp2);
		$myxls->write_string(11,1,' ',$formatp2);
		$myxls->write_string(11,2,' ',$formatp2);
		$myxls->write_string(11,3,' ',$formatp2);
		$myxls->merge_cells(11, 0, 11, 5);
		$myxls->write_string(12,0,get_string('¹_zach','block_dean').$recordbook->recordbook,$formatp2);
		$myxls->write_string(12,1,' ',$formatp2);
		$myxls->write_string(12,2,' ',$formatp2);
		$myxls->write_string(12,3,' ',$formatp2);
		$myxls->merge_cells(12, 0, 12, 5);
		$myxls->write_string(13,0,get_string('deystv','block_dean'),$formatp2);
		$myxls->write_string(13,1,' ',$formatp2);
		$myxls->write_string(13,2,' ',$formatp2);
		$myxls->write_string(13,3,' ',$formatp2);
		$myxls->merge_cells(13, 0, 13, 5);
		$myxls->write_string(14,0,get_string('dateoftake','block_dean').convert_date($studrerol->datoftake, 'en','ru'),$formatp2);
		$myxls->write_string(14,1,' ',$formatp2);
		$myxls->write_string(14,2,' ',$formatp2);
		$myxls->write_string(14,3,' ',$formatp2);
		$myxls->merge_cells(14, 0, 14, 5);
		$myxls->write_string(15,4,get_string('deanoffaculty','block_dean'),$formatp2);
		$myxls->write_string(15,5,' ',$formatp2);
		$myxls->merge_cells(15, 4, 15, 5);
		$myxls->write_string(17,0,get_string('mark','block_dean'),$formatp2);
		$myxls->write_string(17,1,' ',$formatp2);
		$myxls->merge_cells(17, 0, 17, 3);
		$myxls->write_string(17,4,get_string('dateofgive','block_dean').convert_date($studrerol->datofgive, 'en','ru'),$formatp2);
		$myxls->write_string(17,5,' ',$formatp2);
		$myxls->merge_cells(17, 4, 17, 5);
		$myxls->write_string(18,0,get_string('cifrandprop','block_dean'),$formatp);
		$myxls->merge_cells(18, 0, 18, 3);
		$myxls->write_string(19,4,get_string('signing','block_dean'),$formatp2);
		$myxls->write_string(19,5,' ',$formatp2);
		$myxls->merge_cells(19, 4, 19, 5);

       $workbook->close();
       exit;
	}
}

?>

