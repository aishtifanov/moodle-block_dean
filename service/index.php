<?php // $Id: index.php,v 1.0 2011/07/07 12:00:56 zagorodnyuk Exp $
	require_once("../../../config.php");
	require_once("../lib.php");
	require_once("const.php");
	require_once("lib.php");
	
	require_once($CFG->libdir . '/tablelib.php');
	require_once($CFG->libdir . '/uploadlib.php');
	require_once($CFG->libdir . '/filelib.php');
	require_once($CFG->dirroot . '/backup/lib.php');

	$fid = optional_param('id', 0);					// Faculty id
	$del = optional_param('del', '');				// name file for delete
	$npdel=optional_param('npdel', 0);				// not print delete service id
	$currenttab = optional_param('ct', 0);			// Current Tab
	$servicetab = optional_param('cs', 1);			// Service Tab
	$currentservicetab = optional_param('cst', 1);	// Service Current Tab
	$sid = optional_param('sid', 0);	 			// Speciality id
	$semid = optional_param('semid', 0);	 			// Speciality id
	$gid = optional_param('gid', 0);				// Group id
	$uid = optional_param('uid', 0);				// User id
    $deanuser = optional_param('puid', 0);				// User id
	$oid = optional_param('oid', 0);				// id
	$tid = optional_param('tid', 0);				// service id
	$did = optional_param('did', 0);				// discipline id
	$fic = optional_param('fic', 0);				// control type id
	$sesid=optional_param('sesid', 0);				// session id
	$ses=optional_param('ses', 0);					// current session id
	$edyearid = optional_param('yid', 0);			// current year id
	$super_users = "0";
	$reception_inquiry = optional_param('riy', 0);	// service id
	$action = optional_param('action', 0);			//
	$delete = optional_param('delete', 0);			// удаление справки с ошибочными данными
	$n = optional_param('n', 0);					// number service
    $recid = optional_param('recid', 0);			// id record for delete

    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', 20, PARAM_INT);

	if($edyearid == 0) $edyearid = get_current_edyearid();

    if($recid != 0) {
        delete_records_select('dean_service', "id=$recid");
        delete_records_select('dean_service_number', "keyid=$recid");
    }

    @raise_memory_limit("256M");

	require_login();
    
    $user=mysql_query("SELECT id, email FROM dean.mdl_user WHERE username='".$USER->username."'");
    $user=mysql_fetch_array($user);
    $deanuser=$user['id'];

	$editfio = $editdolg = '';
    $error_editdolg = $error_editfio = '';
    $frm = data_submitted();
    
   if (!$frm){
    if(isset($frm->yid)) $edyearid = $frm->yid;
    if(isset($frm->editdolg)) $editdolg = $frm->editdolg;
    if(isset($frm->editfio)) $editfio = $frm->editfio;
	if(!isset($frm->fio)) $frm->fio = '';
    }
    
	if($delete != 0) {
		delete_records_select('dean_service', "id=$delete");
		delete_records_select('dean_service_number', "keyid=$delete");
	}

	if($npdel != 0) {
		delete_records_select('dean_service', "id=$npdel");
	}
     
	if($action != 0) {
		$edyearid = get_record_select('dean_service', "id=$action", 'yearid');
//		print_r($edyearid);
		$edyearid = $edyearid->yearid;
		include('file.php');
		exit();
	}

	if (!$site = get_site()) {
	    redirect("$CFG->wwwroot/$CFG->admin/index.php");
	}
	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	if(!$teacher_is) {
		$teacher_is = isteacher_dean();
	}
    $fd=array();
	$methodist_is = ismethod($oid,0,$super_users);
	$dean_is = isdean();
	$student_is = isstud($deanuser);
    $grant_user_is = isgrant();
/*
	$verify = get_records_select('bsu_students', "codephysperson='$USER->username'", 'id, codephysperson');
	if(!$student_is && !$dean_is && !$methodist_is && !$admin_is && !$teacher_is)
	if(!$verify) {
		error(get_string('not_found_pupil', 'block_cdoservice'), $CFG->wwwroot);
	}
*/

	if ($admin_is) {
	    $fidbsu = $fid;
	}
	if ($methodist_is!=false) {
	    $fd=explode(',',$methodist_is);
        if (count($fd)==2) {
     	 $fid = $fd[1];
        }
        $fidbsu = $fid;
	}
    
	if($del != '') {
//		delete_records('dean_service', 'id', $del);
		unlink("{$CFG->dataroot}/$del");
	}
	$error = get_string('errorservice', 'block_cdoservice');

  
	if($dean_is && !($methodist_is!=false)) {
	//	$servicetab = 3;
	//	$fid = $dean_is;
	}
    
    $data=new stdClass();

	$breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';
	$breadcrumbs .= ' -> ' . get_string('service', 'block_cdoservice');
	print_header("$site->shortname: ", $site->fullname, $breadcrumbs);
    if ($methodist_is==false && !$dean_is && !$student_is && !$admin_is ) {
        if ($grant_user_is)
        {
            $currenttab = 4;
            $servicetab = 4;	
            $currentservicetab = 4;
        }
        else
        {			
            redirect("$CFG->wwwroot", $error);
			exit();
        } 
	    }
        
    if ($currenttab!=0)
	{
	   $data->groupid = $currenttab;
    }
    else
    {    
      $data->groupid = $gid;  
    }
	$data->usermodified = $USER->id;
	if (!($methodist_is!=false) && !$admin_is) {
	    $ur = get_record_select('user', "id=$USER->id", 'username');
	    $usr=mysql_query("SELECT id FROM dean.mdl_user WHERE username='".$ur->username."'");
        $usr=mysql_fetch_assoc($usr);
	    $data->userid = $usr['id'];
	} else {
        $ur = get_record_select('user', "id=$uid", 'username');
	    $usr=mysql_query("SELECT id FROM dean.mdl_user WHERE username='".$ur->username."'");
        $usr=mysql_fetch_assoc($usr);
	    $data->userid = $usr['id'];
	}

	if (isset($frm->order_pack)) {
        $students=mysql_query("SELECT id,username FROM dean.mdl_bsu_group_members WHERE groupid='".$gid."' and deleted=0");
		$frms = (array)$frm;
        if(mysql_num_rows($students)>0)
		while($student=mysql_fetch_array($students)) {
	    $usr=mysql_query("SELECT id FROM dean.mdl_user WHERE username=".$student['username']);
        $usr=mysql_fetch_assoc($usr);  
   			if(isset($frms[$usr['id']])) {
//   				print "<br>userid=".$student->userid.'<br>';
   				$data->userid = $usr['id'];
				$data->groupid = $gid;
                $data->yearid = $edyearid;
			    $data->count = 1;
			    $data->stip = 0;
			    $data->predyavlenie = get_string('predyavlenie1', 'block_cdoservice');
			    $data->serviceid = 1;
			    $data->timemodified = time();
			    insert_record('dean_service', $data);
   			}
		}
	}

	if(isset($frm->rezervnumber)) {
		$data->count = 1;
		$data->serviceid = $currentservicetab;
	    $data->yearid = $edyearid;
	    $data->predyavlenie = '<font color="#AA0000">Резервирование номера документа методистом</font>';
	    $data->idotdelenie = $frm->ido;
	    $data->did = 0;
        $data->semid = 0;
	    $data->fic = 0;
	    $data->timemodified = time();

	    $id = insert_record('dean_service', $data);

        $f=mysql_query("SELECT id,departmentcode FROM dean.mdl_bsu_ref_groups WHERE id='".$data->groupid."'");
        $f=mysql_fetch_array($f);   
		$numberser = get_numberser($f['departmentcode'], $data->serviceid,$data->yearid, $id, 0, $data->idotdelenie);
		unset($data);
		$data->id = $id;
		$data->printedtime = time();
		$data->predyavlenie = '<font color="#AA0000">Резервирование номера документа методистом: №'.$numberser.'</font>';
		update_record('dean_service', $data);
	}

	if (isset($frm->order1)) {
		if(!$verify = get_record_select('dean_service', "groupid=$data->groupid AND userid=$data->userid AND printedtime=0 AND serviceid=1", 'id')) {
		    $data->count = $frm->doc1[0];
		    $data->stip = $frm->stip[0];
		    $data->predyavlenie = $frm->predyavlenie[0];
		    $data->serviceid = 1;
		    $data->yearid = $edyearid;
		    $data->timemodified = time();
		    insert_record('dean_service', $data);

		    if($frm->doc1[1] != 0) {
			    $data->count = $frm->doc1[1];
			    $data->stip = $frm->stip[1];
			    $data->predyavlenie = $frm->predyavlenie[1];
			    $data->serviceid = 1;
			    $data->timemodified = time();
			    insert_record('dean_service', $data);
		 	}

		    if($frm->doc1[2] != 0) {
			    $data->count = $frm->doc1[2];
			    $data->stip = $frm->stip[2];
			    $data->predyavlenie = $frm->predyavlenie[2];
			    $data->serviceid = 1;
			    $data->timemodified = time();
			    insert_record('dean_service', $data);
		 	}
		}
	}

	if (isset($frm->order2)) {
	    $data->count = 1;
	    $data->idotdelenie = $frm->ido;
	    $data->did = $did;
	    $data->fic = $fic;
        $data->semid = $semid;
	    $data->serviceid = 2;
	    $data->yearid = $edyearid;
	    $data->timemodified = time();
	    $verify = get_record_select('dean_service', "userid=$deanuser AND serviceid=2 AND printedtime=0 AND groupid=$currenttab AND did=$did AND fic=$fic AND semid=$semid", 'id');
	    if (!$verify)  insert_record('dean_service', $data);
	}

	if (isset($frm->order3)) {
		if(($frm->sesid != 0)) {
		 if(($frm->text1 != 0)&&($frm->text2 != 0)&&($frm->text3 != 0))
         {
		    $data->count = 1;
		    $data->serviceid = 3;
		    $data->fiodatpad = $frm->fiodatpad;
		    $data->work = $frm->service3;
		    $data->sessionid = $frm->sesid;
		    $data->reception_inquiry = $reception_inquiry;
			$data->yearid = $edyearid;
		    $data->timemodified = time();
            $data->text1 = $frm->text1;
            $data->text2 = $frm->text2;
            $data->text3 = $frm->text3;
            
//print_object($data);
//exit();            
//		    $verify = get_record_select('dean_service', "userid=$uid AND serviceid=3 AND groupid=$currenttab AND yearid=$yearid AND sessionid=$frm->session", 'id');
//		    if (!$verify)
//if(($frm->text1 != 0)()())
		    insert_record('dean_service', $data);
		    $user->id = $uid;
			switch($reception_inquiry) {
				case 2:
					$user->address = $frm->reception_inquiry;
                    $usr=get_record_select("UPDATE user SET address=$frm->reception_inquiry WHERE id=".$user->id);
				break;
				case 3:
					$user->email = $frm->reception_inquiry;
                    $usr=get_record_select("UPDATE user SET email=$frm->reception_inquiry WHERE id=".$user->id);
				break;
				case 4:
					$user->phone1 = $frm->reception_inquiry;
                    $usr=get_record_select("UPDATE user SET phone1=$frm->reception_inquiry WHERE id=".$user->id);
				break;
			} 
        }
        else
        {
            if($frm->text1 == 0) echo print_heading('<font color="#AA0000">Вы не указали тип документа</font>', 'center', 1);  
            if($frm->text2 == 0) echo print_heading('<font color="#AA0000">Вы не указали причину вызова</font>', 'center', 1);  
            if($frm->text3 == 0) echo print_heading('<font color="#AA0000">Вы не указали уровень образования</font>', 'center', 1);
            
        }  
		} else {
		    print_heading('<font color="#AA0000">' . get_string('error4', 'block_cdoservice') . '</font>', 'center', 1);
		}
	}

    $methodist_is = ismethod($oid,0,$super_users);
	$error = 0;
    
	if ($methodist_is!=false) {
	    $fd=explode(',',$methodist_is);
        if (count($fd)==2) {
     	 $fid = $fd[1];
        }
        $fidbsu = $fid;
	}

    if(isset($frm->dolgnost)) {
        if($frm->dolgnost == 6) {
            if(trim($frm->editdolg) == '') {
                $error_editdolg = '<font color="#ff0000">Введите должность</font>';
                $error = 1;
            }
            if(trim($frm->editfio) == '') {
                $error_editfio = '<font color="#ff0000">Введите ФИО должностного лица</font>';
                $error = 1;
            }
        }
    }    
    
	if (($servicetab == 1) && (empty($frm->dolgnost) && (!empty($frm))) && ($methodist_is!=false)) {
	    $error = 1;
	    if(isset($frm->print)) print_heading('<font color="#AA0000">' . get_string('error1', 'block_cdoservice') . '</font>', 'center', 4);
	}

	   
	$fioisp = get_record_select('user', "id=$USER->id", 'firstname, lastname, phone1');
	$phone = $fioisp->phone1;

	if ($phone == '' && $methodist_is!=false) {
	    $error = 2;
	    print_heading('<font color="#AA0000">' . get_string('error2', 'block_cdoservice') . '</font>', 'center', 4);
	}
		if($oid == 2) {
/*
			$grup = get_record_select('dean_academygroups', "facultyid=$fid AND idotdelenie=$oid AND (begwinter=0 OR endwinter=0 OR begsummer=0 OR endsummer=0)", 'id');
			if($grup && $servicetab == 1) {
			    print_heading(get_string('error3', 'block_cdoservice'), 'center', 4);
			}
*/
			$a = "<a href='{$CFG->wwwroot}/blocks/dean/faculty/sesdates.php?fid=$fid'>";
		    print_heading(get_string('href0', 'block_cdoservice', $a), 'center', 4);
		}

	$fioisp = trim($fioisp->lastname) . ' ' . trim($fioisp->firstname);
	$fioisp = explode(' ', $fioisp);
	$f1 = mb_substr($fioisp[1], 0, 2);
	$f2 = mb_substr($fioisp[2], 0, 2);
	$fioisp = $f1 . '.' . $f2 . '. ' . $fioisp[0];
            
	if (isset($frm->print) && !$error) { 
       
	$selectedids=array();
    foreach($frm as $fld=>$val)  
    {  
    if (strpos($fld,'selid')!==false) {
          $selectedids[]=$val;
        } 
    if (strpos($fld,'id')!==false) {
       }
    } 
    if (count($selectedids)>0) $selectedids=implode(',',$selectedids); else $selectedids='';
    include('file.php');
	}
	print_simple_box_start("center", "%100");
    if ($admin_is||$grant_user_is||count($fd)>2) {
    print_heading('<font color="#ff0000">ВНИМАНИЕ! Раздел "Электронные услуги для обучающихся" перенесен в систему "ИнфоБелГУ: Учебный процесс" в виде соответствующего пункта раздела "Сведения об обучающихся".<br /> <br /></font><a href="http://dekanat.bsu.edu.ru/blocks/bsu_nabor/service/index.php">Перейти к разделу</a>', 'center', 3);    
    exit();
    echo '<table align="center">';
    $scriptname = "index.php?cs=$servicetab&amp;id=";
    listbox_faculty1($scriptname, $fid, $fd);
    echo '</table></center>';
    }
	$student_is = isstud($deanuser);
    	if ($student_is && !($methodist_is!=false) && !$admin_is) {
    	print_heading('<font color="#ff0000">ВНИМАНИЕ! Раздел "Электронные услуги" перенесен в систему "ИнфоБелГУ: Учебный процесс" в виде соответствующего пункта раздела "Личный кабинет студента".</font><br /><br /></font><a href="http://dekanat.bsu.edu.ru/blocks/bsu_nabor/service/index.php">Перейти к разделу</a>', 'center', 3);    
        exit();
	    include('pupil.php');
	} else {
	   	print_heading('<font color="#ff0000">ВНИМАНИЕ! Раздел "Электронные услуги для обучающихся" перенесен в систему "ИнфоБелГУ: Учебный процесс" в виде соответствующего пункта раздела "Сведения об обучающихся".<br /><br /></font><a href="http://dekanat.bsu.edu.ru/blocks/bsu_nabor/service/index.php">Перейти к разделу</a>', 'center', 3);    
        exit();
	    include('tabs.php');

	    switch ($servicetab) {
			case 1:
			    if ($fid != 0) {
                    $new = '';
                    switch($fid) {
                        case 8:
                            $new = '_1';
                        break;
                    }                    
			     
//			    	if($USER->id == 59502) $oid ='1,2,3,4,5,6,7,8,9';
					echo"<center>" .
					"<form name='printservice' method='post' action='index.php'>";
					echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
					$list_dolgnost[0] = get_string('select');
					$list_dolgnost[1] = get_string('dolgnost1', 'block_cdoservice');
					$list_dolgnost[2] = get_string('dolgnost2', 'block_cdoservice');
                    $list_dolgnost[3] = get_string('dolgnost3', 'block_cdoservice');
                    $list_dolgnost[4] = get_string('dolgnost4', 'block_cdoservice');
                    $list_dolgnost[5] = get_string('dolgnost5', 'block_cdoservice');
                    $list_dolgnost[6] = 'Введенная';
                    
                    ksort($list_dolgnost);
					if (!isset($frm->dolgnost))
					    $frm->dolgnost = 0;
					print_row(get_string('dolgnost', 'block_cdoservice'), choose_from_menu($list_dolgnost, 'dolgnost', $frm->dolgnost, '', '', '0', true, false),'center');
					print_row(get_string('fio3', 'block_cdoservice'), get_string('fio', 'block_cdoservice'), 'left');


                    $user=mysql_query("SELECT deanid, zamdeanid, zzdeanid FROM dean.mdl_bsu_ref_department_dop WHERE DepartmentCode=".$fid);
                    $user=mysql_fetch_array($user);                
                    
                    $user1=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id='".$user['deanid']."'");
                    $user1=mysql_fetch_array($user1); 
	
					if ($user1) {
					    $user1 = trim($user1['lastname']) . ' ' . trim($user1['firstname']);
					} else {
					    $user1 = get_string('fill','block_cdoservice');
					}
//print $fid;                    
					//print_row(get_string('fio1'.$new, 'block_cdoservice'), "<a href='{$CFG->wwwroot}/blocks/dean/faculty/addfaculty.php?mode=edit&fid=$fid&ret=1'>" . $user1 . '</a>', 'left');
                    
                    print_row(get_string('fio1'.$new, 'block_cdoservice'), "<a href='http://dekanat.bsu.edu.ru/blocks/bsu_ref/references.php?rn=bsu_ref_department&action=persons&fid=$fid' target='blank'>" . $user1 . '</a>', 'left');

                    $user1=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id='".$user['zamdeanid']."'");
                    $user1=mysql_fetch_array($user1); 
					if ($user1) {
					    $user1 = trim($user1['lastname']) . ' ' . trim($user1['firstname']);
					} else {
					    $user1 = get_string('fill','block_cdoservice');
					}
					print_row(get_string('fio2', 'block_cdoservice'), "<a href='http://dekanat.bsu.edu.ru/blocks/bsu_ref/references.php?rn=bsu_ref_department&action=persons&fid=$fid' target='blank'>" . $user1 . '</a>', 'left');

                    $user1=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id='".$user['zzdeanid']."'");
                    $user1=mysql_fetch_array($user1); 					
                   
                    if ($user1) {
					    $user1 = trim($user1['lastname']) . ' ' . trim($user1['firstname']);
					} else {
					    $user1 = get_string('fill','block_cdoservice');
					}
					if($fid == 9) {
						print_row(get_string('dean_faculty_zam', 'block_cdoservice'), "<a href='http://dekanat.bsu.edu.ru/blocks/bsu_ref/references.php?rn=bsu_ref_department&action=persons&fid=$fid' target='blank'>". $user1 . '</a>', 'left');
					} else {
						print_row(get_string('fio4', 'block_cdoservice'), "<a href='http://dekanat.bsu.edu.ru/blocks/bsu_ref/references.php?rn=bsu_ref_department&action=persons&fid=$fid' target='blank'>" . $user1 . '</a>', 'left');
					}
                    
                    
                    print_row('Должность: '.'<input name="editdolg" type="text" size="50" value="'.$editdolg.'"/>'."<br />$error_editdolg", 
                              'ФИО: '.'<input name="editfio" type="text" size="50" value="'.$editfio.'"/>'."<br />$error_editfio", 'left');
                              
                   
					echo'</table>'; 
                    echo '<input type="hidden" name="id" id="id" value="' . $fid . '" />';
//					if($oid == 3) $oid ='3,4';
//					if($USER->id == 59502) $oid ='2,4';
					$basedir = "/1/svc/$fid/$oid";
					if($admin_is) $basedir = "/1/svc/$fid";
					make_upload_directory($basedir);
					$basedir = "{$CFG->dataroot}".$basedir;
					if (file_exists($basedir)) {
					    $filelist = list_directories_and_files($basedir);
					    if ($filelist) {
						echo'<p align="left"><ul align="left">';
						$alt = get_string('delete');
						foreach ($filelist as $file) {
							$filename = get_string('text'.$file[0], 'block_cdoservice');
							$name = "1/svc/$fid/$oid/$file";
						    print "<li>$filename <a href='{$CFG->wwwroot}/file.php/$name'>$file</a> <a href='index.php?del=$name&amp;id=$fid'><img src='$CFG->pixpath/i/cross_red_small.gif' alt='$alt'></a>";
						}
						echo'</ul></p>';
					    }

				    	if($admin_is) {
				    		$oid = '1,2,3,4,5,6,7,8,9';
						}
//				    	if($oid == 3) $oid = '3,4';
/*
$result = mysql_query("SELECT * FROM dean.mdl_bsu_students");
$count_bsu_students = mysql_num_rows($result);

//print "count_bsu_students=$count_bsu_students<br />";
if($count_bsu_students == 0) {
    print_heading('<font color="#ff0000">Создание справок не возможно, произошла ошибка во время синхронизации.</font>', 'center', 3);    
  	print_simple_box_end();
	print_footer();
    exit(); 
}
*/
                        $count = get_count_print($USER->id, $fid, $oid, $methodist_is, $super_users);
                        
						if ($count > 0) {
                            if($admin_is) {
							    echo'<input type="submit" disabled name="print" value="' . get_string('print', 'block_cdoservice') . ' (' . $count . ')' . '" />
									</center>';
							} else {
							    echo'<input type="submit" name="print" value="' . get_string('print', 'block_cdoservice') . '" />
									</center>';
							}
                            print_heading(get_string('liststudent', 'block_cdoservice'), 'center', 3);
							if($gid == 0) {
								$idgroup = get_listgroup_id_faculty($fid, $oid);
							} else {
								$idgroup = $gid;
							}
							$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;gid=";
							echo '<center>';           
							
                            listbox_group2($scriptname, $fid, 0, 0, $gid, $oid);
							echo '</center><br>';
                           
                            $usr_sql="";
                            
                           	if ($methodist_is!=false) {	
                           	    $fd=explode(',',$methodist_is);
                                   if (count($fd)>2) {                                    
                                    $usr_sql="AND usermodified=$USER->id";  
                                   }
                                   else
                                   {
                                    $usr_sql="AND usermodified not in ($super_users)";    
                                   }
                            }
                            
							$sql = "SELECT id, groupid, userid, serviceid, sessionid FROM {$CFG->prefix}dean_service WHERE groupid in ($idgroup) AND printedtime=0 $usr_sql";
           

                                              
                            $datas = get_records_sql($sql);
                            
							if($datas) {
							    $table=new stdClass;
								$table->head = array(get_string('npp', 'block_cdoservice'),
													get_string('fiofield', 'block_cdoservice'),
													get_string('group'),
													get_string('type_service', 'block_cdoservice'),
													get_string('type_session', 'block_cdoservice'),
													get_string('time_session', 'block_cdoservice'),
													get_string('action')
													);

								$table->align = array('center', 'center', 'left', 'left', 'center', 'center');
								$table->titles[]= get_string('title_report', 'block_cdoservice');
								//$table->titlesrows = array(20);
								$table->worksheetname = 'report';
								$table->downloadfilename = 'report';
								$i = 1;
								foreach($datas as $data) {
                                    $user=mysql_query("SELECT username, firstname, lastname FROM dean.mdl_user WHERE id=".$data->userid);
                                    $user=mysql_fetch_array($user); 
                                                                                                                                              
                                    $grupps=mysql_query("SELECT id,name FROM dean.mdl_bsu_ref_groups WHERE id=".$data->groupid);
                                    $gruppa=mysql_fetch_array($grupps);
							    	$gruppa = $gruppa['name'];
                                    
							    	$session = '';
									$font_start = '';
									$font_end = '';
									$time_session = '';
							    	if($data->sessionid != 0) {
							    		$session = get_string('session'.$data->sessionid, 'block_dean');
										$edyearid = get_current_edyearid();
								    	$time_session = get_record_select('dean_journal_session', "groupid=$data->groupid AND edyearid=$edyearid AND numsession=$data->sessionid", 'id, datestart, dateend');
                                        $error = 0;
										if($time_session) {
											if($time_session->datestart == 0 || $time_session->dateend == 0) {
												$error = 1;
											} else {
												$time_session = date('d.m.Y', $time_session->datestart).' - '.date('d.m.Y', $time_session->dateend);
											}
										} else {
											$error = 1;
										}
										if($error == 1) {
											$font_start = '<font color="#FF0000">';
											$font_end = '</font>';
											$time_session = "<a href='{$CFG->wwwroot}/blocks/dean/faculty/sesdates.php?fid=$fid&amp;ses=$data->sessionid'>".get_string('notimesession', 'block_cdoservice').'</a>';
										}
									}

								    $table->data[] = array($font_start.$i,
								    						$font_start.trim($user['lastname']) . ' ' . trim($user['firstname']).$font_end,
								    						$font_start.$gruppa.$font_end,
								    						$font_start.get_string('text'.$data->serviceid, 'block_cdoservice').$font_end,
								    						$font_start.$session.$font_end,
								    						$font_start.$time_session.$font_end,
															$font_start.'<input type="checkbox" name="selid_'.$i.'" value="'.$data->id.'">'.$font_end.'<br>'.
                                                            "<a href='index.php?cs=$servicetab&id=$fid&recid=$data->id'>Удалить</a>"
                                                            );
									$i++;
								}
                                
							//	echo"<form name='printservice' method='post' action='index.php'>";
							//	echo'<table align="center">';
							//	print_row(get_string('dolgnost', 'block_cdoservice'), choose_from_menu($list_dolgnost, 'dolgnost', $frm->dolgnost, '', '', '0', true, false));
							//	echo'</table><br>';
							    print_table($table);
                                echo'</center></form>';
						//	    echo'<center><br><input type="submit" name="printselect" value="' . get_string('print1', 'block_cdoservice') . '" />
					//				</center></form>';
							}
						} else {
						    print_heading('<font color="#AA0000">' . get_string('alert', 'block_cdoservice') . '</font>', 'center', 4);
						}
					}
			    }
		    break;
			case 2:
			    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
//			    $scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;sid=";
//			    listbox_speciality($scriptname, $fid, $sid);
//			    if ($sid != 0) {
			    	if($admin_is) $oid = '1,2,3,4,5,6,7,8,9';
//			    	if($oid == 3) $oid = '3,4';
//			    	if($USER->id == 59502) $oid ='2,4';
					$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=";
//					get_listgroup_id_faculty($fid);
					listbox_group($scriptname, $fid, $sid, 0, $gid, $oid, 0);
//			    }                    
			    echo '</table></center>';
			    if ($gid != 0 && $uid == 0) {
                    $agroup=mysql_query("SELECT id, name FROM dean.mdl_bsu_ref_groups WHERE id=".$gid);
                    $agroup=mysql_fetch_assoc($agroup); 

                    notify("<i><font size='2' color='#FF0000'>Замечание:</font><font size='2'> если отображаемый состав группы не совпадает с действующим, надо выполнить синхронизацию группы <a href=\"../synchronization/ldapsync.php?namegroup={$agroup['name']}\">здесь</a></font>");

					print_list_group($gid);

			    }
			    if ($uid != 0) {
					include('pupil.php');
			    }
		    break;
			case 3:
			    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		    	if($admin_is) $oid = '1,2,3,4,5,6,7,8,9';
//		    	if($oid == 3) $oid = '3,4';
//		    	if($USER->id == 59502) $oid ='2,4';
				$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=";
				listbox_group($scriptname, $fid, $sid, 0, $gid, $oid, 0);
			    echo '</table></center>';
			    if ($gid != 0 && $uid == 0) {
					echo"<form name='form_bkp' method='post' action='index.php'>";

                    $agroup=mysql_query("SELECT id, name FROM dean.mdl_bsu_ref_groups WHERE id=".$gid);
                    $agroup=mysql_fetch_assoc($agroup); 

                    notify("<i><font size='2' color='#FF0000'>Замечание:</font><font size='2'> если отображаемый состав группы не совпадает с действующим, надо выполнить синхронизацию группы <a href=\"../synchronization/ldapsync.php?namegroup={$agroup['name']}\">здесь</a></font>");

					print_list_group($gid, 1);
					echo'<center><input name ="order_pack" type="submit" value="'. get_string('order', 'block_cdoservice').'"></center>';

					echo'<input type="hidden" name="id" value="'.$fid.'" />
						<input type="hidden" name="ct" value="'.$currenttab.'" />
						<input type="hidden" name="cs" value="'.$servicetab.'" />
						<input type="hidden" name="cst" value="'.$currentservicetab.'" />
						<input type="hidden" name="sid" value="'.$sid.'" />
						<input type="hidden" name="gid" value="'.$gid.'" />
						<input type="hidden" name="uid" value="'.$uid.'" />
                        <input type="hidden" name="puid" value="'.$deanuser.'" />
						<input type="hidden" name="did" value="'.$did.'" />
                        <input type="hidden" name="semid" value="'.$semid.'" />
						<input type="hidden" name="fic" value="'.$fic.'" />
						<input type="hidden" name="riy" value="'.$reception_inquiry.'" />
					</form>';
			    }
		    break;
	    	case 4:
	    		if($fid == 0) {
					print_footer();
	    			exit();
	    		}
	    		if($fid != 0) {
				    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';

					$listservicemenu = array();
					$servicemenu[0] = get_string('servicemenu', 'block_cdoservice').' ...';
					$servicemenu[1] = get_string('text1', 'block_cdoservice');
					$servicemenu[2] = get_string('text2', 'block_cdoservice');
					$servicemenu[3] = get_string('text3', 'block_cdoservice');

					$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;tid=";
					echo '<tr><td>'.get_string('type_service', 'block_cdoservice').':</td><td>';
					popup_form($scriptname, $servicemenu, 'servicemenu', $tid, '', '', '', false);
					echo '</td></tr>';

					if($gid == 0) {
						$idgroup = get_listgroup_id_faculty($fid);
					} else {
						$idgroup = $gid;
					}
//					if($USER->id == 59502) $oid ='2,4';
					$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;tid=$tid&amp;sid=$sid&amp;gid=";
					listbox_group($scriptname, $fid, 0, 0, $gid, $oid);

					unset($listservicemenu);
					$listservicemenu = array();
					$servicemenu[0] = get_string('reception_inquiry00', 'block_cdoservice').' ...';
					$servicemenu[1] = get_string('reception_inquiry1', 'block_cdoservice');
					$servicemenu[2] = get_string('reception_inquiry2', 'block_cdoservice');
					$servicemenu[3] = get_string('reception_inquiry3', 'block_cdoservice');
					$servicemenu[4] = get_string('reception_inquiry4', 'block_cdoservice');

					$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;tid=$tid&amp;sid=$sid&amp;gid=$gid&amp;riy=";
     				echo'<tr><td>'.strip_tags(get_string('sposob', 'block_cdoservice')).':</td><td>';
					popup_form($scriptname, $servicemenu, 'reception_inquiry', $reception_inquiry, '', '', '', false);
					echo'</td></tr>';
					if(!isset($frm->fio)) $frm->fio = '';
     				echo'<tr><td>'.strip_tags(get_string('fiofield', 'block_cdoservice')).':</td><td>';
					echo"<form name='search' method='post' action='index.php'>".
						'<input type="text" name="fio" size="30" value="'.$frm->fio.'">
						<input type="submit" name="search" value="' . get_string('search') . '" />';
					echo'<input type="hidden" name="id" value="'.$fid.'" />
						<input type="hidden" name="tid" value="'.$tid.'" />
						<input type="hidden" name="cs" value="'.$servicetab.'" />
						<input type="hidden" name="sid" value="'.$sid.'" />
						<input type="hidden" name="gid" value="'.$gid.'" />
						<input type="hidden" name="riy" value="'.$reception_inquiry.'" />
					</form>';
					echo'</td></tr>';
/*
				    $scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;tid=$tid&amp;sid=";
				    listbox_speciality($scriptname, $fid, $sid);
				    if ($sid != 0) {
				    	if($admin_is) $oid = '1,2,3,4,5,6,7,8,9';
						$scriptname = "index.php?cs=$servicetab&amp;id=$fid&amp;tid=$tid&amp;sid=$sid&amp;gid=";
						if($gid == 0) {
							$idgroup = get_listgroup_id_speciality($sid);
						} else {
							$idgroup = $gid;
						}
						listbox_group($scriptname, $fid, $sid, 0, $gid, $oid);
				    } else {
					    $idgroup = get_listgroup_id_faculty($fid);
					}
*/
				    echo '</table></center>';
				}

			    $table->head = array(get_string('npp', 'block_cdoservice'),
			    					get_string('number_service', 'block_cdoservice'),
									get_string('printfio', 'block_cdoservice'),
									get_string('fiofield', 'block_cdoservice'),
									get_string('group'),
			    					get_string('type_service', 'block_cdoservice'),
			    					get_string('sposob', 'block_cdoservice'),
			    					get_string('time_service_zakaz', 'block_cdoservice'),
			    					get_string('time_service_print', 'block_cdoservice'),
			    					get_string('action')
			    					);

			    $table->align = array('center', 'center', 'left', 'left', 'center', 'center', 'center', 'center', 'center', 'center');
			    $table->titles[] = get_string('title_report', 'block_cdoservice');
			    $table->titlesrows = array(20);
			    $table->worksheetname = 'report';
			    $table->downloadfilename = 'report';
//			    $table->columnwidth = array('10', '35', '35', '10', '10', '10');

				$servicesql = '';
		    	if($tid != 0) $servicesql.= " AND s.serviceid=$tid ";
		    	if($reception_inquiry != 0) $servicesql.= " AND s.reception_inquiry=$reception_inquiry ";
                          
//			                                
                $usr_sql="";
                    
               	if ($methodist_is!=false) {
           	      $fd=explode(',',$methodist_is);
                  if (count($fd)>2) {
                     $usr_sql="AND s.usermodified=$USER->id";  
                 }
                 else
                 {
                  $usr_sql="AND s.usermodified not in ($super_users)";    
                 }
                }
                            
                $datas = get_records_select('dean_service', "groupid in ($idgroup) $servicesql $usr_sql", 'printedtime DESC', 'id, groupid, userid, serviceid, timemodified, reception_inquiry');$edyearid = get_current_edyearid();
				$sql = "SELECT concat(s.id,sn.number),sn.number, s.id, s.groupid, s.userid, s.serviceid, s.timemodified, s.reception_inquiry, s.fic, s.predyavlenie, s.idotdelenie FROM {$CFG->prefix}dean_service s, {$CFG->prefix}dean_service_number sn WHERE s.id=sn.keyid AND s.groupid in ($idgroup) $servicesql $usr_sql order by sn.id DESC";
                
                //print '<br>'.$sql.'<br>';
                //print "$sql";

				if($frm->fio == '') {
					$datas = get_records_sql($sql, $page*$perpage, $perpage);
				} else {
					$datas = get_records_sql($sql);
				}
			    if($datas) {
					$frm->fio = trim($frm->fio);
			    	$i = 1;
				    foreach($datas as $data) {
				    		if($frm->fio == '') {
                                $user=mysql_query("SELECT username, firstname, lastname, address, email, phone1 FROM dean.mdl_user WHERE id=".$data->userid);
                            } else {
				                $user=mysql_query("SELECT username, firstname, lastname, address, email, phone1 FROM dean.mdl_user WHERE id=".$data->userid." AND lastname LIKE '%$frm->fio%'");    
							}    
                     /*  if (!mysql_num_rows($user)>0)
                       {
				    		if(trim($frm->fio) == '') {
						    	$user = get_record_select('user', "id=$data->userid", 'firstname, lastname, address, email, phone1');
							} else {
								$user = get_record_select('user', "id=$data->userid AND lastname LIKE '%$frm->fio%'", 'firstname, lastname, address, email, phone1');
							}
                          $usaddress=$user->address;
                          $usemail=$user->email;
                          $usphone1=$user->phone1;
                          $uslastname=$user->lastname;
                          $usfirstname=$user->firstname;
                       }     
                       else
                       {   
                          $user=mysql_fetch_assoc($user); 
                          $usaddress=$user['address'];
                          $usemail=$user['email'];
                          $usphone1=$user['phone1'];
                          $uslastname=$user['lastname'];
                          $usfirstname=$user['firstname'];
                       }     */
                       if (mysql_num_rows($user)>0)
                       {
                            $user=mysql_fetch_assoc($user); 
                          $usaddress=$user['address'];
                          $usemail=$user['email'];
                          $usphone1=$user['phone1'];
                          $uslastname=$user['lastname'];
                          $usfirstname=$user['firstname'];
                            $grupps=mysql_query("SELECT name FROM dean.mdl_bsu_ref_groups WHERE id=".$data->groupid);
                            $gruppa=mysql_fetch_array($grupps);
         
      			    		$keys = get_records_select('dean_service_number', "keyid=$data->id AND number=$data->number", 'id, timemodified, number, userprintedid, serviceid, jobid');
								if(!$keys) {
									$key->reception_inquiry = 0;
									$key->timemodified = '';
									$key->userprintedid = 0;
									$number = '';

									$text = '';
									if($data->serviceid == 3) {
										$text = get_string('reception_inquiry'.$data->reception_inquiry, 'block_cdoservice');
										switch($data->reception_inquiry) {
											case 2:
												$text = get_string('reception_inquiry_info_small'.$data->reception_inquiry, 'block_cdoservice').'<font color="#555599">'.$usaddress.'</font>';
											break;
											case 3:
												$text = get_string('reception_inquiry_info_small'.$data->reception_inquiry, 'block_cdoservice').'<font color="#0000ff">'.$usemail.'</font>';
											break;
											case 4:
												$text =get_string('reception_inquiry_info_small'.$data->reception_inquiry, 'block_cdoservice').'<font color="#FF5555">'.$usphone1.'</font>';
											break;
										}
									}

						    		$gruppa = $gruppa['name'];
                                    $printuser=mysql_query("SELECT firstname, lastname FROM dean.mdl_user WHERE id=".$key->userprintedid);
                                    $printuser=mysql_fetch_assoc($printuser); 

						    		$type = '';
						    		switch($data->fic) {
						    			case 4:
							    			$type = '<b>'.get_string('examination', 'block_dean').'</b>';
						    			break;
						    			case 5:
						    				$type = '<b>'.get_string('zaschet', 'block_dean').'</b>';
						    			break;
									}

								    $table->data[] = array($i,
								    						$number,
								    						trim($printuser['lastname']) . ' ' . trim($printuser['firstname']),
								    						trim($uslastname) . ' ' . trim($usfirstname),
								    						$gruppa,
								    						get_string('text'.$data->serviceid, 'block_cdoservice'). '<br>'. $type,
								    						$text,
								    						date('d.m.Y H:i:s', $data->timemodified),
								    						$key->timemodified,
								    						'');
									$i++;
								} else {
									$gruppa = $gruppa['name'];
									foreach($keys as $key) {
										if($data->serviceid == 2) {
											$number = get_string('number'.$data->idotdelenie, 'block_cdoservice').$key->number;
										} else {
											$number = get_string('is', 'block_cdoservice') . '-1' . $fidbsu . '-' . $key->number;
										}

										$key->timemodified = date('d.m.Y H:i:s', $key->timemodified);

										$text = '';
										if($data->serviceid == 3) {
											$text = get_string('reception_inquiry'.$data->reception_inquiry, 'block_cdoservice');
											switch($data->reception_inquiry) {
												case 2:
													$text = get_string('reception_inquiry_info_small'.$data->reception_inquiry, 'block_cdoservice').'<font color="#555599">'.$usaddress.'</font>';
												break;
												case 3:
													$text = get_string('reception_inquiry_info_small'.$data->reception_inquiry, 'block_cdoservice').'<font color="#0000ff">'.$usemail.'</font>';
												break;
												case 4:
													$text =get_string('reception_inquiry_info_small'.$data->reception_inquiry, 'block_cdoservice').'<font color="#FF5555">'.$usphone1.'</font>';
												break;
											}
										}
										$text_action = '';
										if($key->jobid != 0) $text_action = "<a href='index.php?action=$data->id&amp;n=$key->number'><img src='$CFG->pixpath/f/word.gif'></a>";
							    		$type = '';
							    		switch($data->fic) {
							    			case 4:
								    			$type = '<b>'.get_string('examination', 'block_dean').'</b>';
							    			break;
							    			case 5:
							    				$type = '<b>'.get_string('zaschet', 'block_dean').'</b>';
							    			break;
										}
                                        if($key->jobid == 0) $text_action = $data->predyavlenie;
                                        $printuser=mysql_query("SELECT firstname, lastname FROM dean.mdl_user WHERE id=".$key->userprintedid);
                                        $printuser=mysql_fetch_assoc($printuser); 
                                        
                                        $table->data[] = array($i,
									    						$number,
									    						trim($printuser['lastname']) . ' ' . trim($printuser['firstname']),
									    						trim($uslastname) . ' ' . trim($usfirstname),
									    						$gruppa,
									    						get_string('text'.$data->serviceid, 'block_cdoservice'). '<br>'. $type,
									    						$text,
									    						date('d.m.Y H:i:s', $data->timemodified),
									    						$key->timemodified,
									    						$text_action);

			                            $i++;
									}
								}
                                }
					}
				}
				if($frm->fio == '') {
                    $usr_sql="";    
                    if ($methodist_is!=false) {
               	      $fd=explode(',',$methodist_is);
                      if (count($fd)>2) {
                        $usr_sql="AND s.usermodified=$USER->id";  
                      }
                      else {
                        $usr_sql="AND s.usermodified not in ($super_users)";
                      }
                    }
					$sql = "SELECT count(s.id) FROM {$CFG->prefix}dean_service s, {$CFG->prefix}dean_service_number sn WHERE s.id=sn.keyid AND s.groupid in ($idgroup) $servicesql $usr_sql";
					$href = "index.php?cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;tid=$tid&amp;gid=$gid&amp;riy=$reception_inquiry&amp;perpage=$perpage&amp;";
					$all_who_in_movepupil = count_records_sql($sql);
					print_paging_bar($all_who_in_movepupil, $page, $perpage, $href);
					print_table($table);
					print_paging_bar($all_who_in_movepupil, $page, $perpage, $href);
				} else {
					print_table($table);
				}
	    	break;
	    }
	}

	print_simple_box_end();
    
	print_footer();

	function print_list_group($gid, $order_pack = 0) {
	    GLOBAL $CFG, $fid, $currenttab, $servicetab, $sid, $gid;

	    $strnever = get_string('never');
	    $tablecolumns = array('picture', 'fullname', 'username', 'password', 'email',
		'city', 'lastaccess', 'action');
	    $tableheaders = array('', get_string('fullname'), get_string('username'), get_string('password'),
		get_string('email'), get_string('city'), get_string('lastaccess'), get_string('action'));

	    // Should use this variable so that we don't break stuff every time a variable is added or changed.
	    $baseurl = "index.php?ct=$currenttab&amp;cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=$gid";

	    $table = new flexible_table("user-index-$gid");

	    $table->define_columns($tablecolumns);
	    $table->define_headers($tableheaders);

	    $table->define_baseurl($baseurl);

	    $table->sortable(true, 'lastname');

	    $table->set_attribute('cellspacing', '0');
	    $table->set_attribute('id', 'students');
	    $table->set_attribute('class', 'generaltable generalbox');

	    $table->setup();

	    if ($whereclause = $table->get_sql_where()) {
			$whereclause .= ' AND ';
	    }
	    $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.auth,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                          u.lastaccess, m.groupid
	                          FROM dean.mdl_user u
	                   INNER JOIN mdl_bsu_group_members m ON m.username = u.username ";

	    $whereclause .= 'groupid = ' . $gid . ' AND ';

	    $studentsql .= 'WHERE ' . $whereclause . ' u.deleted = 0 AND u.confirmed = 1 AND m.deleted=0';

	    if ($sortclause = $table->get_sql_sort()) {
			$studentsql .= ' ORDER BY ' . $sortclause;
	    }
      
        $students=mysql_query($studentsql);
        
         if(mysql_num_rows($students)>0) {
		while($student=mysql_fetch_array($students)) {
		  $studid = get_record_select('user', "username='".$student['username']."'", 'id');
		    if ($student['lastaccess']) {
				$lastaccess = format_time(time() - $student['lastaccess']);
		    } else {
				$lastaccess = $strnever;
		    }
			$edit = get_string('discussion');
			$message = 'Связь';
			if($order_pack == 0) {
				$fullname = "<a href='index.php?cs=2&amp;id=$fid&amp;sid=$sid&amp;gid=$gid&amp;uid=".$studid->id."'>" .trim($student['lastname']) . ' ' . trim($student['firstname']) . "</a>";
				$text = "<a href='{$CFG->wwwroot}/message/discussion.php?id=19&amp;message=$message'>"."<img alt='$edit' src='{$CFG->pixpath}/i/edit.gif'>".'</a>';
			} else {
				$fullname = trim($student['lastname']) . ' ' . trim($student['firstname']);
				$verify = get_record_select('dean_service', "userid=".$student['id']." AND serviceid=1 AND printedtime=0 AND groupid=$gid", 'id');
				if($verify) {
					$text = '';
				} else {
					$text = '<input name="'.$student['id'].'" type="checkbox">';
				}
			}

		    $table->add_data(
		    	array(print_user_picture($studid->id, 1, $student['picture'], false, true),
				"<strong>".$fullname."</strong></div>",
				"<strong>".$student['username']."</strong>",
				"-",
				$student['email'],
				"<i>".$student['city']."</i>",
				"<center><small>$lastaccess</small></center>",
				$text
			));
		}
		echo '<div align=center>';
		$table->print_html();
		echo '</div>';
	    }
	}
  /*  }
    else
    {
   	 $breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';
	 $breadcrumbs .= ' -> ' . get_string('service', 'block_cdoservice');
     print_header("$site->shortname: ", $site->fullname, $breadcrumbs);
     print_simple_box_start("center", "%100");
    print_heading('<font color="#ff0000">Извините, страница находится в разработке</font>', 'center', 3);  
  	print_simple_box_end();
	print_footer();
    }*/
   if (strpos($CFG->dbhost,'localhost')===false&&strpos($CFG->dbhost,'127.0.0.1')===false){ 
      mysql_close($link);
      }
?>