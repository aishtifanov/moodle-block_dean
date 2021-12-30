<?php // $Id: file.php,v 1.0 2011/07/07 12:00:56 zagorodnyuk Exp $

	if($action != 0) {
		$sql = get_record_select('dean_service_number', "keyid=$action", 'id, jobid, facultyid, number');
		$frm->dolgnost = $sql->jobid;
		$keyid = $n;

		$fid = $sql->facultyid;
		$fidbsu = $fid;

        $methodist_is = ismethod($oid,0,$super_users);
        
        $fioisp = get_record_select('user', "id=$USER->id", 'firstname, lastname, phone1');
		$phone = $fioisp->phone1;
		$fioisp = FULLNAME($fioisp);
        
	}

    $new = '';
                 
	$dolgnost = get_string('dolgnost' . $frm->dolgnost, 'block_cdoservice');
    $oid2=$oid;
    
	switch ($frm->dolgnost) {
	    case 1:
			$iofio = get_string('fio', 'block_cdoservice');
		break;
	    case 2: case 3:
            $iofioid=mysql_query("SELECT deanid, deanname, zamdeanid, zzdeanid FROM dean.mdl_bsu_ref_department_dop WHERE DepartmentCode=".$fid);
            $iofioid=mysql_fetch_array($iofioid);                
            if (isset($iofioid['deanname']))
            $dolgnost =$iofioid['deanname'];
            
            $iofio=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id='".$iofioid['deanid']."'");
            $iofio=mysql_fetch_array($iofio); 
            if (isset($iofio['lastname'])&&isset($iofio['firstname']))
            {
			$iofio = trim($iofio['lastname']) . ' ' . trim($iofio['firstname']);
            }
            else
            {
            $iofio='';        
            }
		break;
	    case 4:case 5:
            $iofioid=mysql_query("SELECT deanid, zamdeanid,zamdeanname, zzdeanid,zzdeanname FROM dean.mdl_bsu_ref_department_dop WHERE DepartmentCode=".$fid);
            $iofioid=mysql_fetch_array($iofioid);    
                        
            $methodist_is = ismethod($oid,0,$super_users);
            $striofioid=0;$iofio2=$iofio3='';
            $striofioid = $iofioid['zamdeanid'];
            $iofio2=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id=".$striofioid);
            $iofio2=mysql_fetch_array($iofio2); 
            $iofio2 = trim($iofio2['lastname']) . ' ' . trim($iofio2['firstname']);			    
            $striofioid = $iofioid['zzdeanid'];
            $iofio3=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id=".$striofioid);
            $iofio3=mysql_fetch_array($iofio3); 
            $iofio3 = trim($iofio3['lastname']) . ' ' . trim($iofio3['firstname']);
            $oid3=substr($oid,2,strlen($oid)-2);
			if (strpos($oid3,'2')!==false) {
			 $iofio = $iofio2; 
             if (isset($iofioid['zamdeanname']))
             $dolgnost =$iofioid['zamdeanname'];
			} else {
			 $iofio = $iofio3;  
             if (isset($iofioid['zzdeanname']))
             $dolgnost =$iofioid['zzdeanname'];            
			}
            
		break;
        case 6:
            if($action == 0) {
                $dolgnost = $frm->editdolg;
                $iofio = $frm->editfio;
            } else {
                $sql_data = get_record_select('dean_service', "id=$action", 'id, dolg, fio');             
                $dolgnost = $sql_data->dolg;
                $iofio = $sql_data->fio;
            }
        break;
	}

	if($frm->dolgnost != 6) {
	   if ($iofio!='')
       {
        $iofio = explode(' ', $iofio);
    	$f1 = mb_substr($iofio[1], 0, 2);
    	$f2 = mb_substr($iofio[2], 0, 2);
    	$iofio = $f1 . '.' . $f2 . '. ' . $iofio[0];
       }
       if ($iofio2!='')
       {
        $iofio2 = explode(' ', $iofio2);
    	$f1 = mb_substr($iofio2[1], 0, 2);
    	$f2 = mb_substr($iofio2[2], 0, 2);
    	$iofio2 = $f1 . '.' . $f2 . '. ' . $iofio2[0];
       }
       if ($iofio3!='')
       {
        $iofio3 = explode(' ', $iofio3);
    	$f1 = mb_substr($iofio3[1], 0, 2);
    	$f2 = mb_substr($iofio3[2], 0, 2);
    	$iofio3 = $f1 . '.' . $f2 . '. ' . $iofio3[0];
       }
    }
	$save = 1;

	if($action != 0) {
		$allprinteds = get_records_select('dean_service', "id=$action", '', '*');
	} else {
		$allprinteds = get_data_pupils($fid, $oid, $selectedids, $methodist_is, $super_users);
	}
    
	$text1 = $text2 = $text3 = '';
	$exam_list = 1;
	$saveparalel = 0;
    $deanerror=0; 
	foreach ($allprinteds as $allprinted) {
		$edyearid = $allprinted->yearid;
 
        $sql_data=mysql_query("SELECT username, firstname, lastname FROM dean.mdl_user WHERE id=".$allprinted->userid);
        $sql_data=mysql_fetch_array($sql_data); 
        $username = $sql_data['username'];
        $sql_stud_data=mysql_query("SELECT distinct codephysperson, name FROM dean.mdl_bsu_students WHERE codephysperson=".$username);
        $sql_stud_data=mysql_fetch_array($sql_stud_data); 
        $fio = trim($sql_stud_data['name']);    
        $paralel = 0; 
        $zaoch=mysql_query("SELECT planid FROM dean.mdl_bsu_plan_groups WHERE groupid=".$allprinted->groupid);
        $zaoch=mysql_fetch_array($zaoch);   
            
        $zathetka=mysql_query("SELECT zathetka FROM dean.mdl_bsu_group_members WHERE username=".$sql_data['username']." and groupid=".$allprinted->groupid);
        $zathetka=mysql_fetch_assoc($zathetka);            
        
        //echo "SELECT zathetka FROM dean.mdl_bsu_group_members WHERE username=".$sql_data['username']." and groupid=".$allprinted->groupid."<br><br>";  
         
        $planid=$zaoch['planid'];
        
        $zaochnaya=mysql_query("SELECT specialityid, kvalif,timenorm,edformid FROM dean.mdl_bsu_plan WHERE id=".$planid);
        $zaochnaya=mysql_fetch_assoc($zaochnaya);   
        

        $specid = $zaochnaya['specialityid'];
        $kval = $zaochnaya['kvalif'];
        $timenorm = $zaochnaya['timenorm'];
        if ($frm->dolgnost==4||$frm->dolgnost==5)
        {
        if ($zaochnaya['edformid'] == 2) {
			 $iofio = $iofio2; 
		} 
        if ($zaochnaya['edformid'] == 3||$zaochnaya['edformid'] == 4) {
			 $iofio = $iofio3;
		}
        }
         $specid2=array(0);
         $specs=mysql_query("SELECT idspecyal FROM dean.mdl_bsu_tsspecyal WHERE idspecyal3=".$specid);
         while($spec=mysql_fetch_array($specs)) {
         $specid2[]=$spec['idspecyal'];
         }
         $specid2=implode(',',$specid2);

   /*    $sql_data=mysql_query("SELECT paralel 
        FROM mdl_bsu_students_2013
        WHERE codephysperson=".$sql_data['username']." AND ((idSpecyal=$specid AND idperemetch!=25) or (idSpecyal in ($specid2) AND idkodprith=32))");
       
        
        $sql_data=mysql_query("SELECT paralel 
        FROM mdl_bsu_students_2013
        WHERE codephysperson=".$sql_data['username']." AND zathetka=".$zathetka['zathetka']);
    
       
        if(mysql_num_rows($sql_data)>0) {
        $sql_data=mysql_fetch_assoc($sql_data); 
			switch($sql_data['paralel']) {
				case 1: case 2:
					$paralel = 1;
				break;
			}
		}
        
*/
	    switch ($allprinted->serviceid) {
			case 1: case 3: case 2:
			    $text[1] = $service1;
			    $data = date("d.m.Y");
			    $text[1] = str_replace('#data#', $data, $text[1]);
			    $text[1] = str_replace('#dolgnost#', $dolgnost, $text[1]);
			    $text[1] = str_replace('#iofio#', $iofio, $text[1]);
			    $text[1] = str_replace('#fioisp#', $fioisp, $text[1]);
			    $text[1] = str_replace('#phone#', $phone, $text[1]);               
                $text[1] = str_replace('#fio#', $fio, $text[1]);
                
                $faculty=mysql_query("SELECT d.name, g.name as gname, g.departmentcode, g.endyear, g.idedform 
                FROM dean.mdl_bsu_ref_groups g
                INNER JOIN dean.mdl_bsu_ref_department d ON d.departmentcode=g.departmentcode
                WHERE g.id=".$allprinted->groupid);
                $faculty=mysql_fetch_assoc($faculty);  
       /*       
                $users=mysql_query("SELECT id, codephysperson, daterojd, dateotsh, kurs_new, FormObutsTxt, idperemetch, textperem, numprikazpost, datepost, idkodprith, textprith, numprikazotsh, paralel, paraleltxt, sex, zathetka, kurs_new, idkodprith, idstudent 
                FROM mdl_bsu_students_2013
                WHERE codephysperson='".$username."' AND idkodprith<>2 AND datepost<>'' AND ((idSpecyal=$specid AND idperemetch!=25) or (idSpecyal in ($specid2) AND idkodprith=32)) ORDER BY datepost");
                
                
                if (mysql_num_rows($users)>1)
                {
                $users=mysql_query("SELECT id, codephysperson, daterojd, dateotsh, kurs_new, FormObutsTxt, idperemetch, textperem, numprikazpost, datepost, idkodprith, textprith, numprikazotsh, paralel, paraleltxt, sex, zathetka, kurs_new, idkodprith, idstudent 
                FROM mdl_bsu_students_2013
                WHERE codephysperson='".$username."' AND numprikazotsh='' AND datepost<>'' AND (idSpecyal=$specid AND idperemetch!=25) ORDER BY datepost");
                }   
      */                  
			    
                $prikazs=mysql_query("SELECT id, codephysperson, numprikazpost, kurs_new, datepost, dateprikaz
                FROM dean.mdl_bsu_students
                WHERE codephysperson=".$username." AND zathetka='".$zathetka['zathetka']."' order by period desc");
                  
                $users=mysql_query("SELECT id, codephysperson, daterojd, dateotsh, kurs_new, FormObutsTxt, idperemetch, textperem, numprikazpost, datepost, idkodprith, textprith, numprikazotsh, paralel, paraleltxt, sex, zathetka, kurs_new, idkodprith, idstudent 
                FROM dean.mdl_bsu_students
                WHERE codephysperson=".$username." AND zathetka='".$zathetka['zathetka']."' AND idkodprith=1");       
                
                if (mysql_num_rows($users)>1)
                {
                $prikazs=mysql_query("SELECT id, codephysperson, numprikazpost,kurs_new, datepost, dateprikaz
                FROM dean.mdl_bsu_students
                WHERE codephysperson='".$username." AND (idSpecyal=$specid) AND idkodprith=1 ORDER BY period");
                 
                $users=mysql_query("SELECT id, codephysperson, daterojd, kurs_new, FormObutsTxt, idkodprith, sex, zathetka
                FROM dean.mdl_bsu_students
                WHERE codephysperson='".$username."' AND (idSpecyal=$specid) AND idkodprith=1 ORDER BY datepost");
                }

                while($prikaz=mysql_fetch_array($prikazs)) {
                        $start=explode("-",$prikaz['datepost']);
                        $yearpost=$start[0];
                        if ($start[0]>2013)
                        $start = $start[2].'.'.$start[1].'.'.$start[0];
                        else
                        $start = '01.09.'.$start[0];   
                        $zach=explode("-",$prikaz['dateprikaz']);
                        $zach = $zach[2].'.'.$zach[1].'.'.$zach[0];
					    $number = $prikaz['numprikazpost'];     
                }
 
			    while($user=mysql_fetch_array($users)) {
                        $fackid = $faculty['departmentcode'];
                        $year = explode('-', $user['daterojd']);
                        $daterojd = $year[2].'.'.$year[1].'.'.$year[0];
					    $year = $year[0];
                        $otdelen=mysql_query("SELECT otdelenie FROM dean.mdl_bsu_tsotdelenie WHERE idotdelenie=".$faculty['idedform']);
                        while($otdel=mysql_fetch_array($otdelen))
                        {                        
					    $otdelenie = $otdel['otdelenie'];
                        }
                        if ($allprinted->serviceid!=2)
                        {
                        $otdelenie = str_replace(get_string('aya', 'block_cdoservice'), get_string('oy', 'block_cdoservice'), $otdelenie);
                        $otdelenie = str_replace(get_string('yaya', 'block_cdoservice'), get_string('ey', 'block_cdoservice'),  $otdelenie);
                        }
//					    $spec = get_string('spec2_4', 'block_cdoservice');
					    switch ($kval) {
							case 3:	$spec = get_string('spec3', 'block_cdoservice');
					    }
					   // $facult = mb_substr($faculty['name'], 8);   
                        $facult = explode(".", $faculty['name']);
                        $facult=$facult[1];
						if($allprinted->serviceid != 2) {
						    $facult = str_replace(get_string('ii', 'block_cdoservice'), get_string('ogo', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('faculty2', 'block_cdoservice'), get_string('facultya2', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('faculty', 'block_cdoservice'), get_string('facultya', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('colledge2', 'block_cdoservice'), get_string('colledgea2', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('colledge', 'block_cdoservice'), get_string('colledgea', 'block_cdoservice'), $facult);                             
						    $facult = str_replace(get_string('instituta2', 'block_cdoservice'), get_string('institut2', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('instituta', 'block_cdoservice'), get_string('institut', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('institut2', 'block_cdoservice'), get_string('instituta2', 'block_cdoservice'), $facult);
                            $facult = str_replace(get_string('institut', 'block_cdoservice'), get_string('instituta', 'block_cdoservice'), $facult);        
						} else {
							$facult = str_replace(get_string('faculty', 'block_cdoservice'), '', $facult);
						}
					    $facult = trim($facult);
                        //$facult = mb_strtoupper(mb_substr($facult, 0, 1), 'UTF-8').mb_strtolower(mb_substr($facult,1), 'UTF-8');
                            
						$sex = get_string('sex'.$user['sex'], 'block_cdoservice');
					    
                        $kvalif=mysql_query("SELECT KvakifRP, kvalif, kvalifKod FROM dean.mdl_bsu_tskvalifspec WHERE idkvalif=".$kval);
                        $kvalif=mysql_fetch_array($kvalif); 
				        
						$kvaliftext = get_string('kvaliftext'.$kval, 'block_cdoservice');

				/*	    switch ($faculty['idedform']) {
							case 2:
							    switch ($kval) {
								case 2:
								    $num = 4;
								    switch($specid) {
                                      case 304: case 320:case 584:case 607:
                                      	$num = 5;
                                      break;
									}
								    break;
								case 3:
								    $num = 5;
								    switch($specid) {
                                      case 28: case 63: case 67: case 616:case 807:case 617:
                                      	$num = 6;
                                      break;
									}
								    break;
								case 4:
								    $num = 2;
								    break;
							    }
							    break;
							case 3:
							    switch ($kval) {
									case 2:
									    $num = 5;
									    break;
									case 3:
									    $num = 6;
									    switch($specid) {
	                                      case 63:case 807:
											$num = 5;
	                                      break;
										}
								    break;
									case 4:
									    $num = 3;
									    break;
								    }
							    break;
							case 4:
							    switch ($kval) {
									case 2:
									    $num = 5;
									    break;
									case 3:
									    $num = 6;
									    break;
									case 4:
									    $num = 3;
									    break;
								    }
							    break;
						}*/

                        $qualification=trim($kvalif['kvalif']);
                        $kvalifnum = trim($kvalif['kvalifKod']);
					    $kvalif = trim($kvalif['KvakifRP']);
					    $specyal=mysql_query("SELECT specyal FROM dean.mdl_bsu_tsspecyal WHERE idspecyal=".$specid);
                        $specyal=mysql_fetch_array($specyal);
                        $specyal=explode(" ",$specyal['specyal']);
					    $specyalnum = $specyal[0];
                        if (strpos($specyalnum,".")===false&&is_numeric($specyalnum))
                        {
                        if (substr($specyalnum, strlen($specyalnum)-1, 1)!='.')
                        $specyalnum.=".".$kvalifnum;
                        else
                        $specyalnum.=$kvalifnum;
                        }
                        $specyalname="";
                        foreach($specyal as $id=>$spec)
					    if ($id>0) $specyalname.=" ".$spec;
					    $specyal = $specyalnum .  $specyalname;
					    $formobuts = $user['FormObutsTxt'];

					    $pos = strpos($formobuts, get_string('formobuts', 'block_cdoservice'));
					    if ($pos === false) {
							$formobuts = get_string('platnaya', 'block_cdoservice');
					    } else {
							$formobuts = get_string('besplatnaya', 'block_cdoservice');
					    }
                        
                        $course=$user['kurs_new']%100;
                        /*
					    $course = substr($faculty['gname'], 4, 2);
					    if ($course[0] == 0) {
							$course = substr($faculty['gname'], 5, 1);
					    }*/

						$groupid = $allprinted->groupid;

						if($journal_session = get_record_select('dean_journal_session', "facultyid=$fid AND groupid=$groupid AND edyearid=$edyearid AND isset=1 AND numsession=$allprinted->sessionid AND datestart<>0 AND dateend<>0")) {
							$curyear = date('y', $journal_session->datestart);
							$curyear1 = date('Y', $journal_session->datestart);
						} else {
							$curyear = date('y');
							$curyear1 = date('Y');
							$journal_session->datestart = time();
						}
                        if ($allprinted->semid%2!=0) $sm=$allprinted->semid+1; else $sm=$allprinted->semid; 
						if($kval == 4) {
						    if ($allprinted->semid!=0) 
                            {
                            if ($faculty['idedform'] == 3||$faculty['idedform'] == 4) { 
                            $course = $sm/2; 
                            }
                            else
                            $course = $sm/2-4; 
                            }
                            else $course = $curyear1 - $yearpost;
						} else {
						    if ($allprinted->semid!=0) $course = $sm/2; else $course = $curyear - $course;
						}
                                               
                        if ($allprinted->semid==0) 
                        {
						$mmm = date('m' , $journal_session->datestart);
						switch($mmm) {
							case 8: case 9: case 10: case 11: case 12:
								$course = $course + 1;
							break;
						}
                        }
                        $timenorm=explode('.',$timenorm);
					    //$end = substr($faculty['gname'], 4, 2) + $timenorm[0];
                        $end = $faculty['endyear']; 
                        $month = '06';
                        if ($timenorm[1]!='') $month = '02';
			    if ($faculty['idedform'] == 2) {
			   //  if($kval == 4) 
               //     $end+=4;
			     if ($month=='02') 
                    $end = '28.'.$month.'.'.$end;
                 else
					$end = '30.'.$month.'.'.$end;
			    } else {
					if($kval == 4) {
					 //   $end+=5; 
						$end = '28.02.'.$end;
					} else {
						$end = '30.'.$month.'.'.$end;
					}
                }
                if($journal_endstud = get_record_select('dean_journal_session', "facultyid=$fid AND groupid=$groupid AND numsession=11")) {
                            if ($journal_endstud->endstud!=0)
                            $end=date('d.m.Y', $journal_endstud->endstud);
                } 
			    $text[1] = str_replace('#kvaliftext#', $kvaliftext, $text[1]);
			    $text[1] = str_replace('#sex#', $sex, $text[1]);
			    $text[1] = str_replace('#end#', $end, $text[1]);
			    $text[1] = str_replace('#course#', $course, $text[1]);
			    $text[1] = str_replace('#formobuts#', $formobuts, $text[1]);
			    $text[1] = str_replace('#specyal#', $specyal, $text[1]);
			    $text[1] = str_replace('#faculty#', $facult, $text[1]);
//			    $text[1] = str_replace('#spec#', $spec, $text[1]);
			    $text[1] = str_replace('#kvalif#', $kvalif, $text[1]);
			    $text[1] = str_replace('#otdelenie#', $otdelenie, $text[1]);
			    $text[1] = str_replace('#year#', $year, $text[1]);
                $text[1] = str_replace('#daterojd#', $daterojd, $text[1]);
			    $text[1] = str_replace('#start#', $start, $text[1]);
			    $text[1] = str_replace('#zach#', $zach, $text[1]);
			    $text[1] = str_replace('#number#', $number, $text[1]);
                
                $academy = '';

                if($user['idkodprith'] == 6) {
                    $academ_info=mysql_query("SELECT idacadem, academ, datebeginacadem, dateendacadem, numprikazacadem, dateprikazacadem FROM dean.mdl_bsu_studacadem WHERE idstudent=".$user['idstudent']);
                    $academ_info=mysql_fetch_array($academ_info);
                    
                    
                    $data_work = $academ_info['datebeginacadem'];
                    $data_work = explode(' ', $data_work);
                    $data_work = explode('-', $data_work[0]);
                    $data_work = $data_work[2].'.'.$data_work[1].'.'.$data_work[0];
                    $datebeginacadem = $data_work; 

                    $data_work = $academ_info['dateendacadem'];
                    $data_work = explode(' ', $data_work);
                    $data_work = explode('-', $data_work[0]);
                    $data_work = $data_work[2].'.'.$data_work[1].'.'.$data_work[0];
                    $dateendacadem = $data_work;
                    
                    $data_work = $academ_info['dateprikazacadem'];
                    $data_work = explode(' ', $data_work);
                    $data_work = explode('-', $data_work[0]);
                    $data_work = $data_work[2].'.'.$data_work[1].'.'.$data_work[0];
                    $dateprikazacadem = $data_work;                    

                    $academy = "Находится в академическом отпуске ".$academ_info['academ']." с $datebeginacadem по $dateendacadem. (Приказ № ".$academ_info['numprikazacadem']." от $dateprikazacadem.)";
                    
                }
                $text[1] = str_replace('#academy#', $academy, $text[1]);

				if($allprinted->serviceid == 2) {
					$text[2] = $service2;
					$text[2] = str_replace('#otdelenie#', $otdelenie, $text[2]);
					$text[2] = str_replace('#faculty#', $facult, $text[2]);
					$text[2] = str_replace('#c#', $course, $text[2]);

					$semestr = $allprinted->semid;
					$text[2] = str_replace('#s#', $semestr, $text[2]);
					$text[2] = str_replace('#g#', $faculty['gname'], $text[2]);
					if($action != 0) {
						$numberser = $keyid;
					} else {
						$numberser = get_numberser($fackid, $allprinted->serviceid, $allprinted->yearid, $allprinted->id, $frm->dolgnost, $allprinted->idotdelenie);
					}

					$numberser = get_string('number'.$faculty['idedform'], 'block_cdoservice').$numberser;

					$text[2] = str_replace('#numberser#', $numberser, $text[2]);
                    $cred=''; 
                    if($allprinted->fic !=13)
                    {
                    $d=mysql_query("SELECT a.id as id, b.name as name, a.semestrexamen, a.semestrzachet, a.semestrdiffzach
                    FROM dean.mdl_bsu_discipline a 
                    INNER JOIN dean.mdl_bsu_ref_disciplinename b ON a.disciplinenameid=b.id
                    WHERE a.id=$allprinted->did ORDER BY b.name");
                    $d=mysql_fetch_array($d);
                    $discname=$d['name'];
                    
                    $discip = mysql_query("SELECT d.id, d.disciplineid, d.numsemestr, d.lection, d.praktika, d.lab, d.ksr, d.srs 
                    FROM dean.mdl_bsu_discipline_semestr d 
                    WHERE d.disciplineid=$allprinted->did && d.numsemestr=$allprinted->semid");
                    if(mysql_num_rows($discip)>0) 
                    {
                    $discip=mysql_fetch_array($discip);
                    $hr=$discip['lection']+$discip['praktika']+$discip['lab']+$discip['ksr']+$discip['srs'];
                    }
                    $semestrs=mysql_query("SELECT d.id, d.disciplineid, d.numsemestr 
                                FROM dean.mdl_bsu_discipline_semestr d 
                                WHERE d.disciplineid=$allprinted->did");
                    if(mysql_num_rows($semestrs)>0) 
                    {
                    $allterms=array();   
                    while($semestr=mysql_fetch_array($semestrs)) 
                    $allterms[]=$semestr['numsemestr'];   
                    sort($allterms); 
                    $i=0;
                    while($allterms[$i]!=$allprinted->semid&&$i<count($allterms))
                    $i++; 
                    $terms='';   
                    if ($d['semestrexamen']!='0') $terms.=$d['semestrexamen'];
                    if ($d['semestrzachet']!='0') $terms.=$d['semestrzachet'];
                    if ($d['semestrdiffzach']!='0') $terms.=$d['semestrdiffzach'];
                    $term=numbterm($terms);
                    $noformcontrol=true;      
                    while($noformcontrol&&$i>0) 
                    {
                    foreach($term as $trm) 
                    if ($allterms[$i-1]==$trm) 
                    {
                    $noformcontrol=false;
                    break;
                    }
                    if ($noformcontrol) 
                    {
                    $nm=$allterms[$i-1];
                    $i--;
                    $disc = mysql_query("SELECT d.id, d.disciplineid, d.numsemestr, d.lection, d.praktika, d.lab, d.ksr, d.srs 
                    FROM dean.mdl_bsu_discipline_semestr d 
                    WHERE d.disciplineid=$allprinted->did && d.numsemestr=$nm");     
                    $disc=mysql_fetch_array($disc);
                    $hr=$hr+$disc['lection']+$disc['praktika']+$disc['lab']+$disc['ksr']+$disc['srs'];
                    } 
                    }
                    }
                    $cred=round($hr/36,1);
                    if ($allprinted->fic==4) $cred++;
                    $cred='/'.$cred;
                    }
                    else
                    {
                    $practice=mysql_query("SELECT p.name, pt.name as pname
                    FROM dean.mdl_bsu_plan_practice p
                    INNER JOIN dean.mdl_bsu_ref_practice_type pt ON pt.id=p.practicetypeid
                    WHERE p.id='".$allprinted->did."'");
                    $practice=mysql_fetch_array($practice); 
                    $discname = $practice['pname'].' '.$practice['name'];   
                    $hr='-';   
                    }
                    $text[2] = str_replace('#d#', $discname, $text[2]);
					$text[2] = str_replace('#ch#', $hr.$cred, $text[2]);
					$text[2] = str_replace('#fio#', $fio, $text[2]);
					$text[2] = str_replace('#zach#', $user['zathetka'], $text[2]);
					$text[2] = str_replace('#data#', $data, $text[2]);

					if($allprinted->fic == 4) {
                    	$text[2] = str_replace('#ue1', '<u>', $text[2]);
                    	$text[2] = str_replace('#ue2', '</u>', $text[2]);
						$text[2] = str_replace('#ue3', '', $text[2]);
                    	$text[2] = str_replace('#ue4', '', $text[2]);
						$text[2] = str_replace('#ue5', '', $text[2]);
                    	$text[2] = str_replace('#ue6', '', $text[2]);                          
					} 
                    if($allprinted->fic == 5||$allprinted->fic == 39) {
                        $text[2] = str_replace('#ue3', '<u>', $text[2]);
                    	$text[2] = str_replace('#ue4', '</u>', $text[2]);
						$text[2] = str_replace('#ue1', '', $text[2]);
                    	$text[2] = str_replace('#ue2', '', $text[2]); 
						$text[2] = str_replace('#ue5', '', $text[2]);
                    	$text[2] = str_replace('#ue6', '', $text[2]);                          
					}                   
                    $text[2] = str_replace('#ue3', '', $text[2]);
                    $text[2] = str_replace('#ue4', '', $text[2]);
					$text[2] = str_replace('#ue1', '', $text[2]);
                    $text[2] = str_replace('#ue2', '', $text[2]);
					$text[2] = str_replace('#ue5', '', $text[2]);
                    $text[2] = str_replace('#ue6', '', $text[2]);                         
					switch($faculty['idedform']) {
						case 3: 
							$time = 0;
							for($i=0;$i<10;$i++) {
								$D = date("D", time()+$i*86400);
								if($D == 'Sun') $time = $time + 86400;
							}
							$datad = time() + 777600 + $time;
						    $datad = date("d.m.Y", $datad);
						break;
						case 2:case 4:
							$time = 0;
							for($i=0;$i<3;$i++) {
								$D = date("D", time()+$i*86400);
								if($D == 'Sun') $time = $time + 86400;
							}
							$datad = time() + 172800 + $time;
						    $datad = date("d.m.Y", $datad);
						break;
					}
					$text[2] = str_replace('#datad#', $datad, $text[2]);
					$text[2] = str_replace('#dolgnost#', $dolgnost, $text[2]);
					$text[2] = str_replace('#iofio#', $iofio, $text[2]);
                    
                    if ($allprinted->fic!=13)
                    {
                    $fioexs=mysql_query("SELECT b.teacherid
                    FROM dean.mdl_bsu_edwork_mask a 
                    INNER JOIN dean.mdl_bsu_teachingload b ON a.id=b.edworkmaskid
                    INNER JOIN dean.mdl_bsu_discipline d ON d.disciplinenameid=a.disciplinenameid
                    WHERE d.id=$allprinted->did AND a.planid=$planid AND a.term=$allprinted->semid AND a.edworkkindid=$allprinted->fic");
                    }
                    else
                    {
                    $fioexs=mysql_query("SELECT b.teacherid
                    FROM dean.mdl_bsu_edwork_mask a 
                    INNER JOIN dean.mdl_bsu_teachingload b ON a.id=b.edworkmaskid
                    WHERE a.practiceid=$allprinted->did AND a.planid=$planid AND a.term=$allprinted->semid AND a.edworkkindid=$allprinted->fic");
                    }
                    while($data0=mysql_fetch_array($fioexs)) {
                        $list0[] = $data0['teacherid'];
                    }
                    if (count($list0)>0||$allprinted->fic==13)
                    {
                    $list0 = implode(',', $list0);
                    $username=mysql_query("SELECT firstname, lastname, username FROM dean.mdl_user WHERE id IN ($list0)");
                    if(mysql_num_rows($username)>0) {
                    unset($list0);
                    $i = 0; 
                    while($data=mysql_fetch_array($username)) {
       	  				if(isset($data['lastname'])) {
       	  				  	$firstname = explode(' ', $data['firstname']);
							$f2 = mb_substr(trim($firstname[0]), 0, 2);
                            if (strlen($firstname[1])>1)
							{$f3 = mb_substr(trim($firstname[1]), 0, 2);}
                            else
                            {$f3 = mb_substr(trim($firstname[2]), 0, 2);}
							$f4 = $data['lastname'];
							$fioex = $f2 . '.' . $f3 . '. '. $f4;
						} else {
							$fioex = '';
						}
						$text[2] = str_replace("#fioex$i#", $fioex, $text[2]);
						$i++;                
                    }
                    }
					}
                    else
                    {
                    $edw="edworkindid=$allprinted->fic";
                    if ($allprinted->fic==5||$allprinted->fic==4)
                    $edw="(edworkindid=$allprinted->fic OR edworkindid=40)";
                    $user_groupname=mysql_query("SELECT id,oldname FROM dean.mdl_bsu_ref_groups WHERE id='".$allprinted->groupid."'");
                    $user_groupname=mysql_fetch_array($user_groupname); 
                    $grp="groupid=$allprinted->groupid";
                    if ($user_groupname['oldname']!='0')
                    {
                    $grp="(groupid=$allprinted->groupid OR groupid=".$user_groupname['oldname'].")";
                    }
                    $fioexs=mysql_query("SELECT teacherid
                    FROM dean.mdl_bsu_schedule  
                    WHERE disciplineid=$allprinted->did AND $grp AND term=$allprinted->semid AND $edw");
                    if(mysql_num_rows($fioexs)>0) {
                    while($data0=mysql_fetch_array($fioexs)) {
                        $list0[] = $data0['teacherid'];
                    }
                    $list0 = implode(',', $list0);
                    $username=mysql_query("SELECT firstname, lastname, username FROM dean.mdl_user WHERE id IN ($list0)");
                     if(mysql_num_rows($username)>0) {
                    unset($list0);
                    $i = 0; 
                    while($data=mysql_fetch_array($username)) {
       	  				if(isset($data['lastname'])) {
       	  				  	$firstname = explode(' ', $data['firstname']);
							$f2 = mb_substr(trim($firstname[0]), 0, 2);
                            if (strlen($firstname[1])>1)
							{$f3 = mb_substr(trim($firstname[1]), 0, 2);}
                            else
                            {$f3 = mb_substr(trim($firstname[2]), 0, 2);}
							$f4 = $data['lastname'];
							$fioex = $f2 . '.' . $f3 . '. '. $f4;
						} else {
							$fioex = '';
						}
						$text[2] = str_replace("#fioex$i#", $fioex, $text[2]);
						$i++;                
                    }
                    }
					}
                    }	
					for($j=0;$j<=3;$j++) {
						$text[2] = str_replace("#fioex$j#", '', $text[2]);
					}
					if($exam_list % 2 == 0) {
						$text2.=$text[2]."<span style='font-size:12.0pt;font-family:'Times New Roman','serif';mso-fareast-font-family:
								'Times New Roman';mso-ansi-language:RU;mso-fareast-language:RU;mso-bidi-language:
								AR-SA'><br clear=all style='mso-special-character:line-break;page-break-before:
								always'>
								</span>
								<p class=MsoNormal><span style='font-size:11.0pt;mso-bidi-font-size:12.0pt'><o:p>&nbsp;</o:p></span></p>";

					} else {
						$text2.= $text[2].'<br>';
					}
					$exam_list++;
				}

				if($allprinted->serviceid == 3) {
				    
//             ******************** пока не понятно что писать    
/*
					if($kval == 4) {
						$course = get_string('course'.$course, 'block_cdoservice');
					} else {
						$course = $course.' '.get_string('course', 'block_cdoservice');
					}
/**/

                    switch($allprinted->text1) {
                        case 1: $text_1 = 'допущенному(ой) к вступительным испытаниям'; break;
                        case 2: $text_1 = 'слушателю подготовительного отделения образовательной организации высшего образования'; break;
                        case 3: $text_1 = 'обучающемуся по #otdelenie# форме обучения на #course# курсе'; break;
                    }
                    switch($allprinted->text2) {
                        case 1: $text_2 = 'прохождения вступительных испытаний'; break;
                        case 2: $text_2 = 'промежуточной аттестации'; break;
                        case 3: $text_2 = 'государственной итоговой аттестации'; break;
                        case 4: $text_2 = 'итоговой аттестации'; break;
                        case 5: $text_2 = 'подготовки и защиты выпускной квалификационной работы и/или сдачи итоговых государственных экзаменов'; break;
                        case 6: $text_2 = 'завершения диссертации на соискание ученой степени кандидата наук '; break;
                    }
                    switch($allprinted->text3) {
                        case 1: $text_3 = 'основного общего'; break;
                        case 2: $text_3 = 'среднего общего'; break;
                        case 3: $text_3 = 'среднего профессионального'; break;
                        case 4: $text_3 = 'высшего'; break;
                    }
				
                                    
					switch($paralel) {
						case 1:
							$saveparalel++;
							$text[3] = $service03;
						break;
						default:
							$text[3] = $service3;
					}
                
                
                    $dean_journal_session = get_record_select('dean_journal_session', "groupid=$allprinted->groupid AND edyearid=$edyearid AND numsession=$allprinted->sessionid AND isset=1", 'id, typedocument');
					$uvedomlenie = 0;
					if($dean_journal_session->typedocument == 2) {
						$saveparalel++;
						$text[3] = $service003;
						$uvedomlenie = 1;
					}

					if($text3 != '') {
						if($uvedomlenie ==1) {
							$saveparalel = 0;
							$text3.="<span style='font-size:12.0pt;font-family:'Times New Roman','serif';mso-fareast-font-family:
									'Times New Roman';mso-ansi-language:RU;mso-fareast-language:RU;mso-bidi-language:
									AR-SA'><br clear=all style='mso-special-character:line-break;page-break-before:
									always'>
									</span>
									<p class=MsoNormal><span style='font-size:11.0pt;mso-bidi-font-size:12.0pt'><o:p>&nbsp;</o:p></span></p>";
						} else {
							$div = $saveparalel % 2;
	                        if($div == 0) {
								$text3.="<span style='font-size:12.0pt;font-family:'Times New Roman','serif';mso-fareast-font-family:
										'Times New Roman';mso-ansi-language:RU;mso-fareast-language:RU;mso-bidi-language:
										AR-SA'><br clear=all style='mso-special-character:line-break;page-break-before:
										always'>
										</span>
										<p class=MsoNormal><span style='font-size:11.0pt;mso-bidi-font-size:12.0pt'><o:p>&nbsp;</o:p></span></p>";
							} else {
								$text3.='<br><br><br><br><br><br><br><br><br>';
							}
						}
					}


					if($action != 0) {
						$numberser = $keyid;
					} else {
						$numberser = get_numberser($fackid, $allprinted->serviceid, $allprinted->yearid,  $allprinted->id, $frm->dolgnost);
					}
                    $sfackid=$fackid;
                    if (strlen($fackid)>4&&substr($fackid,3,2)=='00')
                    $sfackid=substr($fackid,0,3);
					$numberser = get_string('is', 'block_cdoservice') . '-' . '1'.$sfackid . '-' . $numberser;

					$text[3] = str_replace('#text1#', $text_1, $text[3]);
                    $text[3] = str_replace('#text2#', $text_2, $text[3]);
                    $text[3] = str_replace('#text3#', $text_3, $text[3]);
                    
                    $text[3] = str_replace('#numberser#', $numberser, $text[3]);
				    $text[3] = str_replace('#data#', $data, $text[3]);
				    $text[3] = str_replace('#dolgnost#', $dolgnost, $text[3]);
				    $text[3] = str_replace('#iofio#', $iofio, $text[3]);
				    $text[3] = str_replace('#fioisp#', $fioisp, $text[3]);
				    $text[3] = str_replace('#phone#', $phone, $text[3]);
				    $text[3] = str_replace('#fio#', $fio, $text[3]);
				    $text[3] = str_replace('#course#', $course, $text[3]);
				    $text[3] = str_replace('#faculty#', $facult, $text[3]);
				    $text[3] = str_replace('#otdelenie#', $otdelenie, $text[3]);
                    $text[3] = str_replace('#specyal#', $specyal, $text[3]);
                    
				    $qualification = get_string('qualification1', 'block_cdoservice');
				    $text[3] = str_replace('#kvalif#', $qualification, $text[3]);

					if($action != 0) {
						$work = get_record_select('dean_service', "id=$action", 'id, work, fiodatpad');
					} else {
						$work = get_record_select('dean_service', "userid=$allprinted->userid AND serviceid=$allprinted->serviceid AND printedtime=0", 'id, work, fiodatpad');
					}
				    $text[3] = str_replace('#work#', $work->work, $text[3]);
				    $text[3] = str_replace('#fiodat#', $work->fiodatpad, $text[3]);

                    $session = get_record_select('dean_journal_session', "groupid=$allprinted->groupid AND edyearid=$allprinted->yearid AND numsession=$allprinted->sessionid", 'id, datestart, dateend');
					$startses = date('d.m.Y', $session->datestart);
					$endses = date('d.m.Y', $session->dateend);
					$timeses = $session->dateend - $session->datestart;

				    $text[3] = str_replace('#startses#', $startses, $text[3]);
				    $text[3] = str_replace('#endses#', $endses, $text[3]);
				    $timeses = $timeses/86400 + 1;
				    $text[3] = str_replace('#timeses#', round($timeses), $text[3]);

				    $text3.= $text[3];

				}

			    $stip = '';
			    if ($allprinted->stip == 1) {
					$stip = get_string('stip1', 'block_cdoservice');
			    }
			    if (trim($allprinted->predyavlenie) == '') {
					$predyavlenie = get_string('predyavlenie', 'block_cdoservice') . ' ' . get_string('predyavlenie1', 'block_cdoservice');
			    } else {
					$predyavlenie = get_string('predyavlenie', 'block_cdoservice') . ' ' . $allprinted->predyavlenie;
			    }
			    $text[1] = str_replace('#stip#', $stip, $text[1]);
			    $text[1] = str_replace('#predyavlenie#', $predyavlenie, $text[1]);
                if($allprinted->serviceid == 1) {
                	if($action != 0) $allprinted->count = 1;

				    for ($i = 0; $i < $allprinted->count; $i++) {
						if($action != 0) {
							$numberser = $keyid;
						} else {
							$numberser = get_numberser($fackid, $allprinted->serviceid, $allprinted->yearid, $allprinted->id, $frm->dolgnost);
						}
                        $sfackid=$fackid;
                        if (strlen($fackid)>4&&substr($fackid,3,2)=='00')
                        $sfackid=substr($fackid,0,3);
                        $numberser = get_string('is', 'block_cdoservice') . '-' . '1'.$sfackid . '-' . $numberser;

						$texttmp = str_replace('#numberser#', $numberser, $text[1]);
						$text1.=$texttmp;

						$div = $save % 2;
                        if($div == 0) {
							$text1.="<span style='font-size:12.0pt;font-family:'Times New Roman','serif';mso-fareast-font-family:
									'Times New Roman';mso-ansi-language:RU;mso-fareast-language:RU;mso-bidi-language:
									AR-SA'><br clear=all style='mso-special-character:line-break;page-break-before:
									always'>
									</span>
									<p class=MsoNormal><span style='font-size:11.0pt;mso-bidi-font-size:12.0pt'><o:p>&nbsp;</o:p></span></p>";
						}
						$save++;
				    }
				}
                }
		    break;
	    }
       $num_rows = mysql_num_rows($users);
        if ($num_rows!=0)
        {
	    $update->id = $allprinted->id;
	    $update->serviceid = $allprinted->serviceid;
	    $update->printedtime = time();
        if($frm->dolgnost == 6) {
            $update->dolg = $dolgnost;
            $update->fio = $iofio;
            
        }
	    update_record('dean_service', $update);
        }        else
        {
           $deanerror=1; 
        }
	}
     if ($deanerror==1)
     {
      // echo '<center><font color=red>'.get_string('errordean', 'block_cdoservice').'</font></center>';
       $breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';
	   $breadcrumbs .= ' -> ' . get_string('service', 'block_cdoservice');
  	   print_header("$site->shortname: ", $site->fullname, $breadcrumbs);
       print_heading('<font color="red">' . get_string('errordean2', 'block_cdoservice') . '</font>', 'center', 4);
     }
	$backup_date_format = "%d-%m-%Y_%H-%M";
	$backup_name = 'svc_';
	$backup_name .= userdate(time(), $backup_date_format, 99, false);
	$backup_name .= ".doc";

    if($text1 != '') {
		$text1 = $header1 . $text1 . $footer;
		$backup_name1 = '1_'.$backup_name;
		$backup_name1 = clean_filename($backup_name1);
		make_upload_directory("1/svc/$fid/$oid2/");
		$filename = "{$CFG->dataroot}/1/svc/$fid/$oid2/$backup_name1";

		if($action != 0) {
		    $fn = '1.doc';
			header("Content-type: application/vnd.ms-word");
			header("Content-Disposition: attachment; filename=\"{$fn}\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
			header("Pragma: public");
			print $text1;
		} else {
			savetoserver($filename, $text1);
		}
    }

    if($text2 != '') {
		$text2 = $header2 . $text2 . $footer;
		$backup_name2 = '2_'.$backup_name;
		$backup_name2 = clean_filename($backup_name2);
		make_upload_directory("1/svc/$fid/$oid2/");
		$filename = "{$CFG->dataroot}/1/svc/$fid/$oid2/$backup_name2";
		if($action != 0) {
		    $fn = '2.doc';
			header("Content-type: application/vnd.ms-word");
			header("Content-Disposition: attachment; filename=\"{$fn}\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
			header("Pragma: public");
			print $text2;
		} else {
			savetoserver($filename, $text2);
		}
    }

    if($text3 != '') {
    	switch($paralel) {
    		case 1:
	    		$text3 = $header03 . $text3 . $footer;
    		break;
			default:
				$text3 = $header03 . $text3 . $footer;
		}
		$backup_name3 = '3_'.$backup_name;
		$backup_name3 = clean_filename($backup_name3);
		make_upload_directory("1/svc/$fid/$oid2/");
		$filename = "{$CFG->dataroot}/1/svc/$fid/$oid2/$backup_name3";
		if($action != 0) {
		    $fn = '3.doc';
			header("Content-type: application/vnd.ms-word");
			header("Content-Disposition: attachment; filename=\"{$fn}\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
			header("Pragma: public");
			print $text3;
		} else {
			savetoserver($filename, $text3);
		}
	}
/**/
?>