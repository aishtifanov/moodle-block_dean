<?php // $Id: to_excel.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    //require_once"Spreadsheet/Excel/Writer.php";
    $fid = optional_param('fid', 0, PARAM_INT);
    $cid = optional_param('cid', 0, PARAM_INT);
    $sid = optional_param('sid', 0, PARAM_INT);
    $gid = optional_param('gid', 0, PARAM_INT);
    $did = optional_param('did', 0, PARAM_INT);

	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
    	$table = table_excl ($fid, $cid, $sid, $gid, $did);
        print_table_to_excel($table);
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

    add_to_log(SITEID, 'dean', 'retakes view', 'retake.php', $strretakes);

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strretakes";
    print_header("$site->shortname: $strretakes", $site->fullname, $breadcrumbs);

	print_heading($strretakes, "center");

function table_excl ($fid, $cid, $sid, $gid, $did){

 	global $CFG;

 	    // Блок описания таблицы
	        $table->head  = array (get_string('fio', 'block_dean'), get_string('mark','block_dean'));
			$table->align = array ("center", "center");
            $table->columnwidth = array (40,20);
            $table->class = 'moutable';
        	$table->width = '90%';
        	$table->downloadfilename = "rolls";
        	$table->worksheetname = "rolls";
    		$table->titles = array();
    		$table->titles[] = get_string('zaschetexam', 'block_dean');
    		$table->titlesrows = array(20, 20);
    	// Конец описания таблицы

			$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
								  u.lastaccess, m.academygroupid
			               FROM {$CFG->prefix}user u
						   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
			$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
			$studentsql .= 'ORDER BY u.lastname';

			$students = get_records_sql($studentsql);

		 $roll =  get_record('dean_rolls', 'disciplineid', $did, 'academygroupid', $gid);

			for ($i=0; $i<=7; $i++)		{
				$menumark[$i] = get_string('o_'.$i, 'block_dean');
			}

			foreach ($students as $student) {
				// $tabledata[0] = array (print_user_picture($student->id, 1, $student->picture, false, true);
				$tabledata[0] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
				if ($roll)	{
				$markonestudent = get_record('dean_roll_marks', 'rollid', $roll->id, 'studentid', $student->id);
					if ($markonestudent)	{
						$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id, $markonestudent->mark, '', '', '0', true);
					} else  {
						$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id, '0', '', '', '0', true);
					}

				} else {
					$tabledata[] = choose_from_menu($menumark, 'z_'.$student->id, '0', '', '', '0', true);
				}
				//print_r($tabledata);
				$table->data[] = $tabledata;
				unset($tabledata);
			}
         return $table;
 }
   // print_table($table);

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
     /*
 $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
				u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                u.lastaccess, m.academygroupid
                    FROM {$CFG->prefix}user u
                 LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
				WHERE m.academygroupid = $gid AND u.deleted = 0 AND u.confirmed = 1
				ORDER BY lastname ASC";
    $s=get_record_sql($sql);


function lstgroupmember_download($download)
{
    global $CFG, $fid, $cid, $gid, $sid, $did, $rid, $term;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");

        $faculty = get_record('dean_faculty','id',$fid);
        $speciality = get_record('dean_speciality','id',$sid);
        $curriculum = get_record('dean_curriculum', 'id', $cid);
        $agroup = get_record('dean_academygroups', 'id', $gid);
        $discipline = get_record('dean_discipline', 'id', $did);
       // $dateofexamen = get_record('dean_rolls', 'id', $did);
        $dateofexamen = get_record_sql("SELECT * FROM {$CFG->prefix}dean_rolls
        								WHERE academygroupid=$gid and disciplineid=$did");

    //    $teachersid = get_record_sql("SELECT * FROM {$CFG->prefix}dean_rolls
       // 								WHERE academygroupid=$gid and disciplineid=$did");
        $teachers = get_record('user','id',$dateofexamen->teacherid);
        $users = get_record('user','id',$gid);
		// HTTP headers
        $countusers = count($users);
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = "rolls";
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
		$formath =& $workbook->add_format();



		$formath1->set_size(12);
	    $formath1->set_align('center');
	    $formath1->set_align('vcenter');
		$formath1->set_color('black');
		$formath1->set_bold(1);
		$formath1->set_italic();

		$formath->set_size(11);
	    $formath->set_align('left');
	    $formath->set_align('vcenter');
		$formath->set_color('black');
		//$formath->set_bold(1);

		// $formath1->set_border(2);

		$formath2->set_size(11);
	    $formath2->set_align('center');
	    $formath2->set_align('vcenter');
		$formath2->set_color('black');
		$formath2->set_bold(1);
		//$formath2->set_italic();
		$formath2->set_border(1);
		$formath2->set_text_wrap();

		$formatp->set_size(11);
	    $formatp->set_align('left');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

		$myxls->set_column(0,0,4);
		$myxls->set_column(1,1,25);
		$myxls->set_column(2,2,15);
		$myxls->set_row(0, 30);

        $count_field = 29;

     	  for ($k=0; $k<$count_field; $k++)	{
	       for ($l=0; $l<7; $l++)	{
				$myxls->write_blank($k,$l,$formatp);
	 		}
	    }

        $titleText = ('Зачетная ведомость №'.$dateofexamen->number);
		$myxls->write_string(0,0,$titleText,$formath2);
		$myxls->merge_cells(0, 0, 0, 6);

		 $myxls->write_string(1,0, 'Факультет'.'          '.$faculty->name ,$formatp);
		 $myxls->merge_cells(1, 0, 1, 6);
		 $myxls->write_string(2,0, 'Специальность'.'    '.$speciality->number.'  '.$speciality->name ,$formatp);
		 $myxls->merge_cells(2, 0, 2, 6);
		 $myxls->write_string(3,0, 'Форма обучения'.'     '.(get_string($curriculum->formlearning,'block_dean')),$formatp);
		 $myxls->merge_cells(3, 0, 3, 6);
		 $myxls->write_string(4,0, 'Семестр'.'     '.$term ,$formatp);
		 $myxls->merge_cells(4, 0, 4, 6);
		 $myxls->write_string(5,0, 'Группа'.'     '.$agroup->name ,$formatp);
		 $myxls->merge_cells(5, 0, 5, 6);
		 $myxls->write_string(6,0, 'Дисциплина'.'   '.$discipline->name ,$formatp);
		 $myxls->merge_cells(6, 0, 6, 6);
		 $myxls->write_string(7,0, 'Дата проведения зачета или экзамена'.'   '.get_rus_format_date($dateofexamen->datecreated,'full'),$formatp);
		 $myxls->merge_cells(7, 0, 7, 6);
		 $myxls->write_string(8,0, 'Преподаватель'.'   '.$teachers->firstname.' '.$teachers->lastname, $formatp);
		 $myxls->merge_cells(8, 0, 8, 6);


        $myxls->write_string(9,0, 'N' ,$formath2);
        $myxls->write_string(9,1,get_string('username','block_dean'),$formath2);
        $myxls->merge_cells(9, 1, 9, 4);
		$myxls->write_string(9,5,get_string('mark','block_dean'),$formath2);
		$myxls->merge_cells(9, 5, 9, 6);


		$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
								  u.lastaccess, m.academygroupid
			               FROM {$CFG->prefix}user u
						   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
			$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
			$studentsql .= 'ORDER BY u.lastname';

			$students = get_records_sql($studentsql);

         if(!empty($students)) {

	 			$roll =  get_record('dean_rolls', 'disciplineid', $did, 'academygroupid', $gid);
				// print_r($roll);
				// echo '<br><br>';
                  $i=9;$g=1;
				foreach ($students as $student) {
						// $tabledata[0] = array (print_user_picture($student->id, 1, $student->picture, false, true);
					//$tabledata[0] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
					if ($roll)	{						$i++;
						$markonestudent = get_record('dean_roll_marks', 'rollid', $roll->id, 'studentid', $student->id);
						if ($markonestudent)	{							$myxls->write_string($i,0,($g-1).'.',$formatp);
    	       				$myxls->write_string($i,1,fullname($student),$formatp);
    	       				$myxls->merge_cells($i, 1, $i, 4);
           	 				   //$myxls->write_string($i,2,fullname($dean_user),$formatp);
        	   					 //$myxls->write_string($i,3,convert_date($retakeI->datoftake, 'en', 'ru'),$formatp);
        	  					  //$myxls->write_string($i,4,convert_date($retakeI->datofgive, 'en', 'ru'),$formatp);

       						if ($markonestudent->mark==1){
								$myxls->write_string($i,5,$markonestudent,$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
               			    }

							if ($markonestudent->mark==2){
								$myxls->write_string($i,5,get_string('n_2','block_dean'),$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
               			    }

      						if ($markonestudent->mark==3){
								$myxls->write_string($i,5,get_string('n_3','block_dean'),$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
               			    }

    						if ($markonestudent->mark==4){
								$myxls->write_string($i,5,get_string('n_4','block_dean'),$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
               			    }

			   				 if ($markonestudent->mark==5){
								$myxls->write_string($i,5,get_string('n_5','block_dean'),$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
               			    }

			  				if ($markonestudent->mark==6){
								$myxls->write_string($i,5,get_string('n_6','block_dean'),$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
              				 }

			  				if ($markonestudent->mark==7){
								$myxls->write_string($i,5,get_string('n_7','block_dean'),$formatp);
								$myxls->merge_cells($i, 5, $i, 6);
              				   }						}
				    }
				    $g++;

                }
          $workbook->close();
          exit;
	     }
}
     }   */
?>
