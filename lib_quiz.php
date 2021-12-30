<?php // $Id: index.php,v 1.0 2011/03/28 16:00:00 zagorodnyuk Exp $
function table_examschedule($specsym, $action, $fid, $did, $sid, $gid, $kid, $notworktest = 0)
{
	global $CFG, $USER, $ed, $admin_is, $headdepartment_is, $dean_is, $currenttab, $subid, $deanuser;
    
 	$title_edit = get_string('title_edit', 'block_dean');
	$title_icon = get_string('title_icon', 'block_dean');
	$title_course = get_string('title_course', 'block_dean');

	if($dean_is) $headdepartment_is = $kid;
	if($admin_is) $headdepartment_is = $kid;
    $table->head  = array ('N',
    						get_string('group'),
    						get_string('discipline', 'block_dean'),
                            get_string('sspeciality', 'block_dean'),
                            "Кафедра",
                            "Экзаменатор",
                            get_string('auditory', 'block_dean'),
                            get_string('dateexam', 'block_dean'),
                            get_string('timeexam', 'block_dean'),
                            get_string('workquiz', 'block_dean'),
                            get_string('action'));

    $table->align = array ("center", "left", "left", "left", 'left', 'left', 'center', 'center', 'center', 'center', 'center');
    $table->size = array ('5%', '5%', '20%', '20%', '20%', '20%', '10%', '5%', '5%');
	$table->width = '85%';
    $table->columnwidth = array (4, 8, 39, 39, 52, 44, 12, 10, 16, 11);
    $table->titles[] = get_string('examschedule','block_dean');
	$table->downloadfilename = "examschedule";
    $table->worksheetname = 'examschedule';
	$table->titlesrows = array(20);
    $subdep='';
    $i = 1;
    $where = $fidsql = $sidsql = $gidsql = $didsql = $edsql = '';
    $fack = substr($fid, 1);
	if($fid!=0 || $did!=0 || $sid!=0 || $gid!=0 || $kid!=0) $where = ' WHERE ';
	$and = '';
	if($fid!=0) {
		$fidsql = " s.departmentcode=$fid ";
        $and=" AND ";
	}
   if($gid != 0) {
   $gidsql = $and." s.groupid=$gid ";
   $and=" AND ";
   }
   if($ed != 0) {
   $ed1=explode('.',$ed);
   $datsql=mktime(12, 0, 0, $ed1[1], $ed1[2], $ed1[0]);
   $edsql = " AND s.datestart=$datsql ";
   }
   if ($where=='') $edw=' WHERE (s.edworkindid=4 OR s.edworkindid=40)'; else $edw=' AND (s.edworkindid=4 OR s.edworkindid=40)';
	if($action == 'alld') {
        $strsql = "SELECT d.disciplinenameid, d.disciplineid, d.subdepartmentid, s.name
          FROM  mdl_bsu_discipline_subdepartment d
          INNER JOIN mdl_bsu_vw_ref_subdepartments s ON s.id=d.subdepartmentid
          WHERE d.subdepartmentid=$headdepartment_is and d.yearid in (17)";
          $subdepartname='';
          $disciplinenames='';
          if ($subdeparts =  mysql_query($strsql)) {
            while ($subdepart=mysql_fetch_array($subdeparts)) {
			if ($subdepart['disciplineid']>0) $disciplinenames[] = $subdepart['disciplineid'];
            $subdepartname=$subdepart['name'];
            } 
            if ($subdepartname!='') $disciplinenames = implode(',', $disciplinenames);
            }   
        $strsql2 = "SELECT s.id, s.teacherid, s.roomid, s.groupid, s.disciplinenameid, s.disciplineid,s.edworkindid, s.departmentcode, s.datestart, s.timestart, s.timeend, g.name
        FROM  mdl_bsu_schedule s
        INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
        WHERE (s.edworkindid in (4,40)) and (s.disciplineid in ($disciplinenames)) and s.yearid in (17) and s.deleted=0";
	} else
		if($action != 'all') {

            $strsql2 = "SELECT s.id, s.teacherid, s.roomid, s.groupid, s.disciplinenameid,s.disciplineid, s.departmentcode, s.datestart, s.timestart, s.timeend, g.name
            FROM  mdl_bsu_schedule s
            INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
            WHERE s.teacherid=$deanuser AND (s.edworkindid=4 OR s.edworkindid=40) and s.yearid in (17) and s.deleted=0";
		} else {
		     if($did != 0)
             {
		     $strsql = "SELECT d.disciplinenameid,d.disciplineid, d.subdepartmentid, s.name
             FROM  mdl_bsu_discipline_subdepartment d
             INNER JOIN mdl_bsu_vw_ref_subdepartments s ON s.id=d.subdepartmentid
             WHERE d.subdepartmentid=$did and d.yearid in (17)"; 	   
             if ($subdeparts =  mysql_query($strsql)) {
                while ($subdepart=mysql_fetch_array($subdeparts)) {
                    if ($subdepart['disciplineid']>0) $disciplinenames[] = $subdepart['disciplineid'];
                    $subdep=$subdepart['name'];
                    } 
                    $disciplinenames = implode(',', $disciplinenames);
                    }   
             $didsql = $and." s.disciplineid in ($disciplinenames) ";
              }

		     $strsql2 = "SELECT s.id, s.teacherid, s.roomid, s.groupid, s.disciplinenameid, s.disciplineid, s.departmentcode, s.datestart, s.timestart, s.timeend, g.name 
             FROM  mdl_bsu_schedule s
             INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
             $where $fidsql $didsql $gidsql $edw $edsql and s.yearid in (17) and s.deleted=0";


        }
        $plus_sopdisc = 0;
        $plus_quizdisc = 0;
        if ($agroups =  mysql_query($strsql2))
        {
        $category = '161, 64, 108, 81, 155, 111, 114, 140, 149, 159, 160, 77';
        $sqls = get_records_select('course', "", '', 'id');

//		$sqls = get_records_select('course', "category=64 or category=108 or category=81 or category=111 or category=155 or category=114 or category=140 or category=149 or category=161", '', 'id');
		foreach($sqls as $sql) {
			$courseid_a[] = $sql->id;
		}
		$courseid_a = implode(',' ,$courseid_a);
        $edit0 = 0;  
        $icon0 = 0;   
                    
        while ($agroup=mysql_fetch_array($agroups)) {
              if ($subdep=='')
              {
                 $kaf='';
                 if ($headdepartment_is!=0) $kaf=" and d.subdepartmentid=$headdepartment_is";
                 $strsql = "SELECT d.disciplinenameid, d.disciplineid, d.subdepartmentid, s.name
                 FROM  mdl_bsu_discipline_subdepartment d
                 INNER JOIN mdl_bsu_vw_ref_subdepartments s ON s.id=d.subdepartmentid
                 WHERE d.disciplineid =".$agroup['disciplineid']." and d.yearid in (17) $kaf"; 
                 if ($subdeparts =  mysql_query($strsql)) {
                    $subdepart=mysql_fetch_array($subdeparts);
                    $subdepartname=$subdepart['name'];
                    $subdepartid=$subdepart['subdepartmentid'];
                    }  
              }
				$sqls = get_records_select('dean_course_discipline', "disciplineid=".$agroup['disciplineid'], '', 'id, courseid');
				if($sqls) {
					foreach($sqls as $sql) {
						$course_id[] = $sql->courseid;
					}
					$course_id = implode(',', $course_id);
					$name_quiz = $agroup['name'];

					$sqls = get_record_select('quiz', "course in ($course_id) AND name like '%$name_quiz%'", 'id, course');
					if($sqls) {
						$edit = "<a title='$title_icon' href='{$CFG->wwwroot}/mod/quiz/index.php?id=$sqls->course'><img src=\"{$CFG->wwwroot}/mod/quiz/icon.gif\"/></a>";
						$plus_quizdisc++;
					}
					unset($course_id);
				}
           	    $strsql = "SELECT g.groupid, g.planid, p.specialityid, s.specyal 
                 FROM mdl_bsu_plan_groups g
                 INNER JOIN mdl_bsu_plan p ON p.id=g.planid
                 INNER JOIN mdl_bsu_tsspecyal s ON s.idSpecyal=p.specialityid
                 WHERE g.groupid=".$agroup['groupid'];  
                $special =  mysql_query($strsql);
                $specialityid = mysql_fetch_assoc($special);
                if($action == 'all' || $action == 'alld') {

					$auditory = '';
				/*	if($ed != 0) {
						$add = 0;
						if(isset($agroup['datestart'])) {
							if($ed == date("Y.m.d",$agroup['datestart'])) $add = 1;
						}
					} else {
						$add = 1;
					}*/

			//		if($ed == 1) {
                            $user=mysql_query("SELECT id, firstname, lastname,email FROM mdl_user WHERE id=".$agroup['teacherid']);
                            $user=mysql_fetch_array($user);                       
							$usr = get_record_select('user', "email='".$user['email']."'", 'id');
							$cur_date = $cur_time = '';
                            $mkdatetimestart = $agroup['datestart'];
							$razn = $mkdatetimestart - time();
							
                            if(!$admin_is)
								if($razn < 1 && !$admin_is && $mkdatetimestart != 0) $edit = '<font color="#990000">'.get_string('razn', 'block_dean').'</font>';
							$visible = 1;
							$sqls_temp = get_records_select('dean_course_discipline', "disciplineid=".$agroup['disciplineid'], '', 'id, courseid');
                            $edit1 = $edit2 = $edit3 = '';
							if(($headdepartment_is || $admin_is) && ($action == 'alld')) {
						    $edit1= "<a title='$title_course' href='index.php?subid=$headdepartment_is&amp;ct=$currenttab&amp;id=".$agroup['disciplineid']."&amp;cl=1&amp;sid=".$specialityid['specialityid']."'><img src=\"{$CFG->wwwroot}/pix/i/course.gif\"/></a>";
							}
							if($sqls_temp) {
								foreach($sqls_temp as $sql_temp) {
									$course_id[] = $sql_temp->courseid;
								}
								$course_id = implode(',', $course_id);
								$name_quiz = get_string('examination_test', 'block_dean').' ('.$agroup['name'].')';
								$sqls_temp = get_record_select('quiz', "course in ($course_id) AND name='$name_quiz'", 'id, course');
								$workquiz = '-';
								if($sqls_temp) {
									if($notworktest && $action != 'alld') $visible = 0; else $visible = 1;
									if($user['id'] == $deanuser || $admin_is) $edit3 = "<a title='$title_icon' href='{$CFG->wwwroot}/mod/quiz/index.php?id=$sqls_temp->course'><img src=\"{$CFG->wwwroot}/mod/quiz/icon.gif\"/></a>";
									$workquiz = '+';
									$icon0++;
								}
								unset($course_id);
							}
                            $strsql = "SELECT r.id, r.name, r.idbuilding, r.istest, r.usingfortestonsaturday, b.id, b.name as bname
                            FROM  mdl_bsu_area_room r
                            INNER JOIN mdl_bsu_area_building b ON b.id=r.idbuilding
                            WHERE r.id=".$agroup['roomid'];
                            $auditorys =  mysql_query($strsql);
                            $auditory = mysql_fetch_assoc($auditorys);
                        	if($auditory) {
								$a = 0;
								if($auditory['istest'] != '1' && $auditory['usingfortestonsaturday'] != '1') {
									$aud = '<font color="#CCCCCC">'.$auditory['name'].'<br>('.$auditory['bname'].')</font>';
									$explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = '<font color="#CCCCCC">'.$explode.'</font>';
									$cur_time = '<font color="#CCCCCC">'.$explode1.'-'.$explode2.'</font>';
									if($notworktest && $action != 'alld') {
										$edit2 = '';
										$visible = 0;
									} else {
										$visible = 1;
									}
								} else {
									$a = 1;
									$aud=$auditory['name'].'<br>('.$auditory['bname'].')';
                                    $explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = $explode;
									$cur_time = $explode1.'-'.$explode2;
								}
							}                                
                            else
                                {
                                 	$a = 0;
                                    $edit2 = '';
                                    $explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = $explode;
									$cur_time = $explode1.'-'.$explode2;   
                                }
							if($visible) {
								if(trim($specialityid['specialityid']) == '') $specialityid['specialityid'] = 0;
                                 $kaf='';
                                 if ($headdepartment_is!=0) $kaf=" AND departmentid=$headdepartment_is";
								$verify=0;
                                if ($agroup['disciplineid']!=0) $verify = get_record_select('dean_course_discipline', "disciplineid=".$agroup['disciplineid']." $kaf AND courseid IN ($courseid_a) AND specialityid=".$specialityid['specialityid'], 'id');
								if($verify) {
									$sopdiscipline = '+';
									if(!$user) $user['id'] = 0;
									if(($deanuser == $user['id'] || isadmin())) {
										if($a == 1)
											$edit2 = "<a title='$title_edit' href='index.php?subid=".$subdepartid."&amp;ct=$currenttab&amp;action=$action&amp;id=".$agroup['id']."&amp;uid=".$user['id']."&amp;did=".$agroup['disciplineid']."&amp;fid=$fid&amp;sid=".$specialityid['specialityid']."&amp;gid=".$agroup['groupid']."&amp;kid=$kid&amp;ed=$ed'><img src=\"{$CFG->pixpath}/i/edit.gif\"/></a>";
									}
									$plus_sopdisc++;
								} else {
									$sopdiscipline = '-';
									$workquiz = '-';
								}
                                if(!$user) $user['id'] = 0;
                                if ($user) $fullname = "<a href='{$CFG->wwwroot}/user/view.php?id=$usr->id&amp;course=1'>".$user['lastname'].' '.$user['firstname'].'</a>';
                                if($deanuser != $user['id'] && $headdepartment_is && !$admin_is) {
                                	$edit2 = $edit3 = '';
                                }
                                 $strsql = "SELECT id, name
                                 FROM mdl_bsu_ref_disciplinename WHERE id=".$agroup['disciplinenameid'];  
                                 $discip =  mysql_query($strsql);
                                 $discipid = mysql_fetch_assoc($discip);
                                 if ($subdep=='')
                                 $table->data[] = array ($i++,
														$agroup['name'],
														$discipid['name'],
														$specialityid['specyal'],
														$subdepartname,
														$fullname,
														$aud,
														$cur_date,
														$cur_time,
														$sopdiscipline.'/'.$workquiz,
														$edit1.$edit2.$edit3);
                                else
                                $table->data[] = array ($i++,
														$agroup['name'],
														$discipid['name'],
														$specialityid['specyal'],
														$subdep,
														$fullname,
														$aud,
														$cur_date,
														$cur_time,
														$sopdiscipline.'/'.$workquiz,
														$edit1.$edit2.$edit3);
								$edit = '';
							}
				//	} 
				} else {
					$edit1 = $edit2 = $edit3 = '';
                     // это иначе для вывода дисциплин, если action не равен all
                     $strsql = "SELECT d.disciplinenameid, d.disciplineid, d.subdepartmentid, s.name
                     FROM  mdl_bsu_discipline_subdepartment d
                     INNER JOIN mdl_bsu_vw_ref_subdepartments s ON s.id=d.subdepartmentid
                     WHERE d.disciplineid =".$agroup['disciplineid']." and d.yearid in (17)"; 
                     if ($subdeparts =  mysql_query($strsql)) {
                        $subdepart=mysql_fetch_array($subdeparts);
                        $subdepartname=$subdepart['name'];
                        } 
                      $user=mysql_query("SELECT id, firstname, lastname,email FROM mdl_user WHERE id=".$agroup['teacherid']." and deleted=0");        
                      $user=mysql_fetch_array($user);
                      $usr = get_record_select('user', "email='".$user['email']."'", 'id');         				
                   // $user = get_record_select('user', "id=".$agroup['teacherid']." and deleted=0", 'id, firstname, lastname');
						if($deanuser == $user['id'] || isadmin()) {
                            $explode = date("d.m.Y",$agroup['datestart']);
							$mkdatetimestart = $agroup['datestart'];
							$razn = $mkdatetimestart - time();
//							$edit2 = "<a title='$title_edit' href='index.php?subid=$specialityid->idspecyal&amp;action=$action&amp;id=$agroup->Id&amp;uid=$user->id&amp;did=$agroup->DisciplineNameId&amp;fid=$fid&amp;sid=$specialityid->idspecyal&amp;gid=$gid&amp;kid=$kid&amp;ed=$ed'><img src=\"{$CFG->pixpath}/i/edit.gif\"/></a>";
							$edit2 = "<a title='$title_edit' href='index.php?action=$action&amp;subid=".$subdepart['subdepartmentid']."&amp;id=".$agroup['id']."&amp;uid=".$user['id']."&amp;did=".$agroup['disciplineid']."&amp;sid=".$specialityid['specialityid']."'><img src=\"{$CFG->pixpath}/i/edit.gif\"/></a>";
//http://localhost/pegas/blocks/dean/quiz/index.php?subid=260&action=&id=89213&uid=17&did in (16)93&fid=0&sid=260&gid=0&kid=0&ed=
							if($razn < 3) {
//                          	$edit = get_string('razn', 'block_dean');
							}
//print 'sfd';
                            $sqls_temp = get_records_select('dean_course_discipline', "disciplineid=".$agroup['disciplineid']." AND specialityid=".$specialityid['specialityid']."", '', 'id, courseid');
							if($sqls_temp) {
								foreach($sqls_temp as $sql_temp) {
									$course_id[] = $sql_temp->courseid;
								}
								$course_id = implode(',', $course_id);
								$name_quiz = get_string('examination_test', 'block_dean').' ('.$agroup['name'].')';

								$sqls_temp = get_record_select('quiz', "course in ($course_id) AND name='$name_quiz'", 'id, course');
								$workquiz = '-';
								if($sqls_temp) {
									$edit3 = "<a title='$title_icon' href='{$CFG->wwwroot}/mod/quiz/index.php?id=$sqls_temp->course'><img src=\"{$CFG->wwwroot}/mod/quiz/icon.gif\"/></a>";
									$workquiz = '+';
								}
								unset($course_id);
							}
                            $strsql = "SELECT r.id, r.name, r.idbuilding, r.istest, r.usingfortestonsaturday, b.id, b.name as bname
                            FROM  mdl_bsu_area_room r
                            INNER JOIN mdl_bsu_area_building b ON b.id=r.idbuilding
                            WHERE r.id=".$agroup['roomid'];
                            $auditorys =  mysql_query($strsql);
                            $auditory = mysql_fetch_assoc($auditorys);
                           	if($auditory) {
								$a = 0;
								if($auditory['istest'] != '1' && $auditory['usingfortestonsaturday'] != '1') {
									$aud = '<font color="#CCCCCC">'.$auditory['name'].'<br>('.$auditory['bname'].')</font>';
									$explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = '<font color="#CCCCCC">'.$explode.'</font>';
									$cur_time = '<font color="#CCCCCC">'.$explode1.'-'.$explode2.'</font>';
									$edit2 = '';
								} else {
									$a = 1;
									$aud=$auditory['name'].'<br>('.$auditory['bname'].')';
                                    $explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = $explode;
									$cur_time = $explode1.'-'.$explode2;
								}
							} else {
                                $explode = date("d.m.Y",$agroup['datestart']);
                                $explode1 = date("H",$agroup['timestart']);
                                $explode1 .= ":".date("i",$agroup['timestart']);
                                $explode2 = date("H",$agroup['timeend']);
                                $explode2 .= ":".date("i",$agroup['timeend']);
                                $cur_date = $explode;
                                $cur_time = $explode1.'-'.$explode2;
                            }
                            
							$verify = get_record_select('dean_course_discipline', "disciplineid=".$agroup['disciplineid']." AND courseid IN ($courseid_a) AND specialityid=".$specialityid['specialityid'], 'id');
							if($verify) {
								$sopdiscipline = '+';
								if($workquiz == '')	$plus_sopdisc++;
							} else {
								$sopdiscipline = '-';
								$workquiz = '-';
							}

							if($workquiz == '-' && !$verify) $edit2 = '';
                            $strsql = "SELECT id, name
                            FROM mdl_bsu_ref_disciplinename WHERE id=".$agroup['disciplinenameid'];  
                            $discip =  mysql_query($strsql);
                            $discipid = mysql_fetch_assoc($discip);
                            $table->data[] = array ($i++,
                            $agroup['name'],
							$discipid['name'],
							$specialityid['specyal'],
							$subdepartname,
							"<a href='{$CFG->wwwroot}/user/view.php?id=$usr->id&amp;course=1'>".$user['lastname'].' '.$user['firstname'].'</a>',
							$aud,
							$cur_date,
							$cur_time,
							$sopdiscipline.'/'.$workquiz,
							$edit2.$edit3);    
						}
					}
     }

		if($ed == '') {
					if($action == 'all') {		  
					   while ($agroup=mysql_fetch_array($agroups)) {
					         if ($subdep=='')
                              {
                                 $kaf='';
                                 if ($headdepartment_is!=0) $kaf=" and d.subdepartmentid=$headdepartment_is";
                                 $strsql = "SELECT d.disciplinenameid, d.disciplineid, d.subdepartmentid, s.name
                                 FROM  mdl_bsu_discipline_subdepartment d
                                 INNER JOIN mdl_bsu_vw_ref_subdepartments s ON s.id=d.subdepartmentid
                                 WHERE d.disciplineid=".$agroup['disciplineid']." and d.yearid in (17) $kaf";
                                 if ($subdeparts =  mysql_query($strsql)) {
                                    $subdepart=mysql_fetch_array($subdeparts);
                                    $subdepartname=$subdepart['name'];
                                    }  
                                 }
						            $auditory = '';					   
									$edit = '';
                                         $user=mysql_query("SELECT id, firstname, lastname, email FROM mdl_user WHERE id=".$agroup['teacherid']);                             				
                                         $user=mysql_fetch_array($user);  
                                         $usr = get_record_select('user', "email='".$user['email']."'", 'id');
 
                                        //$user = get_record_select('user', "id=".$agroup['teacherid']."", 'id, firstname, lastname');
									
                                     $strsql = "SELECT r.id, r.name, r.idbuilding, r.istest, r.usingfortestonsaturday, b.id, b.name as bname
                            FROM  mdl_bsu_area_room r
                            INNER JOIN mdl_bsu_area_building b ON b.id=r.idbuilding
                            WHERE r.id=".$agroup['roomid'];
                            $auditorys =  mysql_query($strsql);
                            $auditory = mysql_fetch_assoc($auditorys);
                           	if($auditory) {
								if($auditory['istest'] != '1' && $auditory['usingfortestonsaturday'] != '1') {
									$aud = '<font color="#CCCCCC">'.$auditory['name'].'<br>('.$auditory['bname'].')</font>';
									$explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = '<font color="#CCCCCC">'.$explode.'</font>';
									$cur_time = '<font color="#CCCCCC">'.$explode1.'-'.$explode2.'</font>';
									$edit = '';
								} else {
									$a = 1;
									$aud=$auditory['name'].'<br>('.$auditory['bname'].')';
                                    $explode = date("d.m.Y",$agroup['datestart']);
                                    $explode1 = date("H",$agroup['timestart']);
                                    $explode1 .= ":".date("i",$agroup['timestart']);
									$explode2 = date("H",$agroup['timeend']);
                                    $explode2 .= ":".date("i",$agroup['timeend']);
									$cur_date = $explode;
									$cur_time = $explode1.'-'.$explode2;
								}
							} else {
                                $explode = date("d.m.Y",$agroup['datestart']);
                                $explode1 = date("H",$agroup['timestart']);
                                $explode1 .= ":".date("i",$agroup['timestart']);
                                $explode2 = date("H",$agroup['timeend']);
                                $explode2 .= ":".date("i",$agroup['timeend']);
                                $cur_date = $explode;
                                $cur_time = $explode1.'-'.$explode2;
                            }
                                 $strsql = "SELECT id, name
                                 FROM mdl_bsu_ref_disciplinename WHERE id=".$agroup['disciplinenameid'];  
                                 $discip =  mysql_query($strsql);
                                 $discipid = mysql_fetch_assoc($discip);
                                 $strsql = "SELECT g.groupid, g.planid, p.specialityid, s.specyal 
                                 FROM mdl_bsu_plan_groups g
                                 INNER JOIN mdl_bsu_plan p ON p.id=g.planid
                                 INNER JOIN mdl_bsu_tsspecyal s ON s.idSpecyal=p.specialityid
                                 WHERE g.groupid=".$agroup['groupid']; 
                                 $special =  mysql_query($strsql);
                                 $specialityid = mysql_fetch_assoc($special);
                                        $table->data[] = array ('<font color="#FF0000">'.$i++.'</font>',
																'<font color="#FF0000">'.$agroup['name'],
																'<font color="#FF0000">'.$discipid['name'],
																'<font color="#FF0000">'.$specialityid['specyal'],
																'<font color="#FF0000">'.$subdepartname,
																'<font color="#FF0000">'.$user['lastname'].' '.$user['firstname'],
																'<font color="#FF0000">'.$aud,
																'<font color="#FF0000">'.$cur_date,
																'<font color="#FF0000">'.$cur_time,
																'',
															'');
									}
                      }
					}
		if(($action=='all' && $admin_is) || ($deanuser == 1835))
			$table->data[] = array ('',
									'',
									'',
									'',
									'',
									'',
									'',
									'',
									'',
									$plus_sopdisc.'/'.$plus_quizdisc,
									"<img src=\"{$CFG->wwwroot}/mod/quiz/icon.gif\"/>-$icon0<br><img src=\"{$CFG->pixpath}/i/edit.gif\"/>-$edit0");	
}
return $table;
}

function print_header_examshedule($fid, $did, $sid, $gid, $kid) {
	GLOBAL $CFG, $ed, $currenttab;

    $fack = substr($fid, 1);
    $fidsql = " name LIKE '$fack%'";
	print_simple_box_start('center', '100%', 'white');
	echo'<table class="formtable">';
	echo '<tr><td>'.get_string('ffaculty', 'block_dean').'</td><td>';
	$list[0] = get_string('select');
    $strsql = "SELECT id, departmentcode, name 
    FROM  mdl_bsu_ref_department
    WHERE DepartmentNumber>99
    ORDER BY departmentcode"; 
    if ($departs =  mysql_query($strsql)) 
    while ($depart=mysql_fetch_array($departs)) {
	$list[$depart['departmentcode']] = $depart['name'];
    }   
	$stradres = "index.php?action=all&amp;ed=$ed&amp;sid=$sid&amp;gid=$gid&amp;did=$did&amp;ct=$currenttab&amp;fid=";
	popup_form("$stradres", $list, "department", $fid, "", "", "", false);
    
    $subdepsql = '';
	if($fid!=0) $subdepsql = " WHERE $fidsql ";
	unset($list);
	echo '<tr><td>'.'Кафедра'.'</td><td>';
	$list[0] = get_string('select');
    
    $strsql = "SELECT id, name
    FROM  mdl_bsu_vw_ref_subdepartments
    WHERE yearid=17"; 	   
    if ($subdeparts =  mysql_query($strsql))
    while ($subdepart=mysql_fetch_array($subdeparts)) {
	$list[$subdepart['id']] = $subdepart['name'];
    }   
	$stradres = "index.php?action=all&amp;fid=$fid&amp;sid=$sid&amp;gid=$gid&amp;ed=$ed&amp;ct=$currenttab&amp;did=";
	popup_form("$stradres", $list, "subdepartment", $did, "", "", "", false);
    
    $strsql = "SELECT DISTINCT edworkindid, datestart as date
    FROM  mdl_bsu_schedule
    WHERE (edworkindid=4 or edworkindid=40) and yearid in (17) and deleted=0";
    $date_arrays[] = get_string('select'); 
    if ($datetimestarts =  mysql_query($strsql)) 
    {
        while ($datetimestart=mysql_fetch_array($datetimestarts)) {
	    $date = date("Y.m.d",$datetimestart['date']);
	    $date_arrays[$date] = date("d.m.Y",$datetimestart['date']);
         }   
         ksort($date_arrays);
		$stradres = "index.php?action=all&amp;fid=$fid&amp;sid=$sid&amp;did=$did&amp;gid=$gid&amp;ct=$currenttab&amp;ed=";
		echo '<tr><td>'.get_string('examdata', 'block_dean').'</td><td>';
		popup_form("$stradres", $date_arrays, "examdata", $ed, "", "", "", false);
		echo '</td>';
	}

	if($fid != 0) {
		unset($list);
		$list[0] = get_string('select');
        $strsql = "SELECT id, name
            FROM mdl_bsu_ref_groups
            WHERE $fidsql";  
        if ($sqls =  mysql_query($strsql)) 
        while ($str=mysql_fetch_array($sqls)) {
	    $list[$str['id']] = $str['name'];
         }    
		echo '<tr><td>'.get_string('group').'</td><td>';
		$stradres = "index.php?action=all&amp;fid=$fid&amp;sid=$sid&amp;did=$did&amp;ct=$currenttab&amp;ed=$ed&amp;gid=";
		popup_form("$stradres", $list, "group", $gid, "", "", "", false);
	}
	echo'</table>';

	echo'<form name="save" id="saveleaver" method="post" action="examtest.php">';
	echo'</form>';
}

function create_statistic() {
    $table->head  = array ('N',
    						get_string('department', 'block_dean'),
    						get_string('countexamdepartment', 'block_dean'),
    						get_string('countdiscipline', 'block_dean'),
                            get_string('countexamdiscipline', 'block_dean'),
                            get_string('countcreatequiz', 'block_dean')
                            );

    $table->align = array ("center", "left", "center", "center", "center", "center");
    $table->size = array ('5%', '55%', '10%', '10%', '10%', '10%');
	$table->width = '90%';
    $table->columnwidth = array (4, 87, 26, 25, 25, 8);
    $table->titles[] = get_string('examschedule','block_dean');
	$table->downloadfilename = "statistics";
    $table->worksheetname = 'statistics';
	$table->titlesrows = array(20);

//	$departments = get_records_select('bsu_ref_subdepartment', "id>292", 'id', 'id, name');
       
    $strsql = "SELECT id, name
    FROM  mdl_bsu_vw_ref_subdepartments
    WHERE id>395"; 	   
    $i = 1;
	$sum1 = $sum2 = $sum3 = $sum4 = 0;
    if ($subdeparts =  mysql_query($strsql)) 
    while ($data=mysql_fetch_array($subdeparts)) {
      $strsql = "SELECT disciplinenameid, disciplineid, subdepartmentid
      FROM  mdl_bsu_discipline_subdepartment 
      WHERE subdepartmentid=".$data['id']." and yearid in (17)";
      $count4=0; 
      $disciplinenames='';
      if ($discips =  mysql_query($strsql)) {
      while ($discip=mysql_fetch_array($discips)) {
      $disciplinenames[] = $discip['disciplineid'];
      $count4++;
      } 
      if ($count4>0) $disciplinenames = implode(',', $disciplinenames);
      }   
      $strsql = "SELECT count(disciplineid) as count
      FROM  mdl_bsu_schedule 
      WHERE disciplineid in ($disciplinenames) and (edworkindid=4 or edworkindid=40) and yearid in (17) and deleted=0";
      if ($counts =  mysql_query($strsql))	
      while ($count1=mysql_fetch_array($counts)) 
      {
      if (!isset($count1['count'])) $count1['count']=0;
      $count2 = get_record_select('dean_course_discipline', "departmentid=".$data['id'], 'count(id) as count');
      if($count2->count > 0) {
			$courseids = get_records_select('dean_course_discipline', "departmentid=".$data['id'], 'id, courseid');
			foreach($courseids as $courseid) $a[] = $courseid->courseid;
			$s = implode(',', $a);
			$count3 = get_record_select('quiz', "course IN ($s)", 'count(distinct course) as count');
			unset($a);
        } else $count3->count = 0;  
              
        $sum1 = $sum1 + $count1['count'];
		$sum2 = $sum2 + $count2->count;
		$sum3 = $sum3 + $count3->count;
		$sum4 = $sum4 + $count4;
    	$table->data[] = array('<b>'.$data['id'].'.</b> ',
    							$data['name'],
    							$count1['count'],
    							$count4,
    							$count2->count,
    							$count3->count
    						  );
		$i++;
     }   
    }
   	$table->data[] = array('',
   							'<b>'.get_string('itogo', 'block_dean').'</b>',
   							'<b>'.$sum1.'</b>',
   							'<b>'.$sum4.'</b>',
   							'<b>'.$sum2.'</b>',
   							'<b>'.$sum3.'</b>'
   						  );
	return $table;
}

function listbox_subdepartment2($scriptname, $subid, $fid)
{
  global $CFG;

  if(count($fid) == 1) $fid='0'.$fid;

  $menu = array();
  $menu[0] = get_string('selectasubdepartment', 'block_dean').'...';

    $strsql = "SELECT id, name
    FROM  mdl_bsu_vw_ref_subdepartments
    WHERE yearid in (17)"; 
    if ($subdeparts =  mysql_query($strsql))
    while ($subdepart=mysql_fetch_array($subdeparts)) {
	$menu[$subdepart['id']] = $subdepart['name'];
    }   


  echo '<tr> <td>'.get_string('department', 'block_dean').': </td><td>';
  popup_form($scriptname, $menu, 'switchdep', $subid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function delete_quiz_attempt($uniqueid) {
//	delete_records_select('question_attempts', "id=$uniqueid");
	delete_records_select('question_states', "attempt=$uniqueid");
	delete_records_select('question_sessions', "attemptid=$uniqueid");
//	delete_records_select('quiz_attempts', "uniqueid=$uniqueid");
}

?>