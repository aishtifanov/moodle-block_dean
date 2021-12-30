<?php // $Id: pupil.php,v 1.0 2011/7/7 12:00:56 zagorodnyuk Exp $
    $ido = 0;
//	$yearid = get_current_edyearid();
	if($uid == 0) 
    {
        $uid = $USER->id;
        $user=mysql_query("SELECT id FROM dean.mdl_user WHERE username='".$USER->username."'");
        $user=mysql_fetch_array($user);
        $deanuser=$user['id'];
    }
    else
    {
      $f_oper = get_record_select('user', "id = $uid" , 'username'); 
      $user=mysql_query("SELECT id FROM dean.mdl_user WHERE username='".$f_oper->username."'");
      $user=mysql_fetch_array($user);
      $deanuser=$user['id'];  
    }
    
    $username=mysql_query("SELECT firstname, lastname,username FROM dean.mdl_user WHERE id='".$deanuser."'");
    $username=mysql_fetch_array($username); 
    $unid=$username['username'];

	if(!($methodist_is!=false)) {
        $groups=mysql_query("SELECT id,groupid FROM dean.mdl_bsu_group_members WHERE username='".$unid."' and yearid=$edyearid and deleted=0");  
        if(mysql_num_rows($groups)==0) 
        {
         $groups=mysql_query("SELECT id,groupid FROM dean.mdl_bsu_group_members WHERE username='".$unid."' and deleted=0 and yearid>13");     
        }
	} else {
		$id = get_listgroup_id_faculty($fid);
        $groups=mysql_query("SELECT id,groupid FROM dean.mdl_bsu_group_members WHERE username='".$unid."' AND groupid in ($id) and deleted=0");
	}
	if($gid != 0 && ($admin_is || ($methodist_is!=false)  || $dean_is)) print_heading("<a href='index.php?cs=2&amp;id=$fid&amp;sid=$sid&amp;gid=$gid'>".get_string('return_group', 'block_cdoservice').'</a>', 'center', 5);

    $errorgrcount=0;
    if(mysql_num_rows($groups)==0) 
    {
    $errorgrcount=1;
    echo '<center>'.get_string('errorgroup', 'block_cdoservice').'</center>';
    }
    
	include('tabsgroups.php');
	include('tabsservice.php');

    $user_groupname=mysql_query("SELECT id,name FROM dean.mdl_bsu_ref_groups WHERE id='".$gid."'");
    $user_groupname=mysql_fetch_array($user_groupname); 
    $user_groupname=$user_groupname['name'];

	$table->head = array(get_string('npp', 'block_cdoservice'), get_string('type_service', 'block_cdoservice'), get_string('dop_info', 'block_cdoservice'), get_string('countservice', 'block_cdoservice'), get_string('action'));
	$table->align = array("center", "left", "left", "center", "center");
	$table->width = '100%';
	$table->size = array ('5%', '15%', '60%', '10%', '10%');
	switch($currentservicetab) {
		case 1:
			echo"<form name='form_bkp' method='post' action='index.php'>";
			$verify = get_record_select('dean_service', "userid=$deanuser AND serviceid=1 AND printedtime=0 AND groupid=$currenttab", 'id');
            
			if($verify||$errorgrcount==1) {
				$text = '';
			} else {
				$text = '<input name ="order1" type="submit" value="'. get_string('order', 'block_cdoservice').'">';
			}

			unset($list);
			$list[0] = get_string('select');
			$list[1] = get_string('stip1', 'block_cdoservice');
			$list[2] = get_string('stip2', 'block_cdoservice');

			$error11 = '';
            
            $sex=mysql_query("SELECT distinct sex FROM dean.mdl_bsu_students WHERE codephysperson=".$username['username']);
            $sex=mysql_fetch_array($sex); 
               
			if(!isset($sex['sex'])) {
				    $error11 = get_string('error22', 'block_cdoservice');
                     //  echo $error11;
				    $sex['sex'] = -1;
			}
			if($error11 != '') $text = $error11;

			for($i=1;$i<4;$i++) {
				$dopinfo = '<table><tr><td>'.get_string('stip', 'block_cdoservice').': </td><td>'.choose_from_menu($list, 'stip[]', 0, "", '', '', true).'</td></tr>';
				$dopinfo.= '<tr><td>'.get_string('predyavlenie', 'block_cdoservice').': </td><td>'.
						   '<input name ="predyavlenie[]" size="50" type="text" value="'. get_string('predyavlenie1', 'block_cdoservice').'"></td></tr></table>';
				$select = '<select id="doc11" name="doc1[]">';
				if($i>1) {
					$select.='<option id="0" value="0">0</option>';
				}

				$select.='<option id="1" value="1">1</option>'.
						'<option id="2" value="2">2</option>'.
						'<option id="3" value="3">3</option>'.
						'<option id="4" value="4">4</option>'.
						'<option id="5" value="5">5</option>'.
						'</select>';

				$table->data[] = array( $i,
										get_string('doc1', 'block_cdoservice'),
										$dopinfo,
										$select,
										$text);
				$text = '';

			}
            include($CFG->dirroot.'/2.php');            
		break;
		case 2:
			$error11 = '';
            
            $sex=mysql_query("SELECT distinct sex FROM dean.mdl_bsu_students WHERE codephysperson=".$username['username']);
            $sex=mysql_fetch_array($sex); 
            
            if(!isset($sex)) {			 
			   $error11 = get_string('error22', 'block_cdoservice');
			   $sex['sex'] = -1;
             }
                       
          	$g=mysql_query("SELECT name,idedform FROM dean.mdl_bsu_ref_groups WHERE id='".$gid."'");
            $g=mysql_fetch_assoc($g);  
            $errordisc=0;
			$ido = $g['idedform'];
//print "sdfsdfsdfsdfsdfsd";
			if($error11 == '') {
			 
				$semester = '20'.substr($g['name'], 4, 2);
				$p1 = date('Y', time());
				$semester = $p1 - $semester;
				$s1 = $semester*2;
				$s2 = $s1 - 1;
				$s3 = $s1;
				$s4 = $s1;

				$s3 = $s1 + 1;
				$s4 = $s1 + 2;

				$s5 = $s2 - 2;
				$s6 = $s2 - 1;
                $s7 = $s2;
                $s8 = $s2;

				$semester = "$s5,$s6,$s1,$s2,$s3,$s4,$s7,$s8";
                $semester = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30";
                
                  $dts=mysql_query("SELECT id, planid FROM dean.mdl_bsu_plan_groups WHERE groupid='$gid'");
                
                if(mysql_num_rows($dts)>0) {
					while($data=mysql_fetch_array($dts)) {
						$plans[] = $data['planid'];
					}
                $plans = implode(',', $plans);  
               
                $maxsem = 0;
                $datas=mysql_query("SELECT max(newterm) as maxsem FROM dean.mdl_bsu_discprac_indiv WHERE groupid='$gid' and codephysperson=".$username['username']);
                $individ = 1;
                $data=mysql_fetch_array($datas);
                $maxsem = $data['maxsem'];
                 
                if ($maxsem==0) {
                    $sql = "SELECT max(b.numsemestr) as maxsem FROM dean.mdl_bsu_discipline a
                    inner join dean.mdl_bsu_discipline_semestr b on a.id=b.disciplineid
                    where a.planid IN ($plans)";
                    $datas=mysql_query($sql);     
                    $individ = 0; 
                    $data=mysql_fetch_array($datas);
                    $maxsem = $data['maxsem'];     
                }
                
                if ($individ==0){
                    $sql = "SELECT max(term) as maxsem FROM dean.mdl_bsu_plan_practice 
                    where planid IN ($plans)";
                    $datas=mysql_query($sql);
                    if(mysql_num_rows($datas)>0) {
                        $data=mysql_fetch_array($datas);
                        if ($data['maxsem']>$maxsem) $maxsem = $data['maxsem'];
                    }
                }    
                
                $list[] = get_string('selectsemestr', 'block_cdoservice').'...';
                for ($i=1; $i<=$maxsem; $i++)   {
                    $list[$i] =  $i;
                }
                    
        		$href = "index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;ct=$gid&amp;uid=$uid&amp;gid=$gid&amp;semid=";
    			echo'<center>';
   				popup_form($href, $list, "semid", $semid, "", "", "", false);
   				echo'<br><br></center>';
   				unset($list);
                unset($array);
                $errordisc=0;
                $necesssem=strtoupper(strval(dechex($semid)));
                $edworktypeid=array();

                if($semid > 0) {
                    $datas=mysql_query("SELECT distinct edworkkindid FROM dean.mdl_bsu_discprac_indiv WHERE planid IN ($plans) and newterm=$semid and codephysperson=".$username['username']);
                    if(mysql_num_rows($datas)>0) {
                        while($data=mysql_fetch_array($datas)) {
                            if ($data['edworkkindid']>0) $edworktypeid[]=$data['edworkkindid'];
                        }
                    } else {
                       $datas=mysql_query("SELECT id, planid, disciplinenameid, semestrexamen, semestrzachet, semestrkursovik, semestrkp, semestrdiffzach FROM dean.mdl_bsu_discipline WHERE planid IN ($plans)");
                        while($data=mysql_fetch_array($datas)) {
                            if (strpos(strval($data['semestrexamen']),$necesssem)!==false)
                            {
                            $edworktypeid[]=4;
                            }
                            if (strpos(strval($data['semestrzachet']),$necesssem)!==false)
                            {
                            $edworktypeid[]=5;
                            }
                            if (strpos(strval($data['semestrkursovik']),$necesssem)!==false)
                            {
                            $edworktypeid[]=6;
                            }
                            if (strpos(strval($data['semestrkp']),$necesssem)!==false)
                            {
                            $edworktypeid[]=36;
                            }
                            if (strpos(strval($data['semestrdiffzach']),$necesssem)!==false)
                            {
                            $edworktypeid[]=39;
                            }
    					}                    
                        $datas=mysql_query("SELECT id, term, name, practicetypeid FROM dean.mdl_bsu_plan_practice WHERE planid IN ($plans) AND edworkkindid=13");
                        while($data=mysql_fetch_array($datas)) {
                          if ($data['term']==$semid)   {
                            $edworktypeid[]=13;
                          }
                        } 
                    }
                    if(count($edworktypeid)>0) {
                        $list[] = get_string('selectcontroltype', 'block_cdoservice').'...';
                        
                        foreach($edworktypeid as $edwork) {
                            $description = mysql_query("SELECT description FROM dean.mdl_bsu_ref_edworkkind WHERE id=$edwork");
                            $description=mysql_fetch_assoc($description);
                            $list[$edwork] = $description['description'];
                        }
    
        				$href = "index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;ct=$gid&amp;uid=$uid&amp;gid=$gid&amp;semid=$semid&amp;fic=";
        				echo'<center>';
        				popup_form($href, $list, "fic", $fic, "", "", "", false);
        				echo'<br><br></center>';
        				unset($list);
                    } else {
                      $errordisc=1;
                      echo '<center>'.get_string('errorterm', 'block_cdoservice').'</center>';
                         
                    }
                }
    				if($fic > 0 && $semid > 0) {
    				    unset($arraydisc);
                        unset($list);
                        $list[] = get_string('selectacourses', 'block_dean');
                        
                        $datas=mysql_query("SELECT * FROM dean.mdl_bsu_discprac_indiv WHERE planid IN ($plans) and newterm=$semid and codephysperson=".$username['username']);
                        if(mysql_num_rows($datas)>0) {
                            while($data=mysql_fetch_array($datas)) {
                               if ($fic!=13 && $fic == $data['edworkkindid']) {
                                  $arraydisc[] = $data['disciplineid'];
                               }
                               if ($fic==13 && $fic == $data['edworkkindid']) {
                                  $practice=mysql_query("SELECT id, name, practicetypeid FROM dean.mdl_bsu_plan_practice WHERE id=".$data['practiceid']);
                                  $data=mysql_fetch_assoc($practice);
                                  $practicetype=mysql_query("SELECT id, name FROM dean.mdl_bsu_ref_practice_type WHERE id=".$data['practicetypeid']);
                                  $pract=mysql_fetch_assoc($practicetype);
                                  $list[$data['id']] = $pract['name'].' '.$data['name'];
                               }
                            } 
                        } else  {
                            $datas=mysql_query("SELECT id, planid,disciplinenameid, semestrexamen, semestrzachet, semestrkursovik, semestrdiffzach, semestrkp FROM dean.mdl_bsu_discipline WHERE planid IN ($plans)");
                            while($data=mysql_fetch_array($datas)) {
                                if ($fic==4&&strpos(strval($data['semestrexamen']),$necesssem)!==false) {
                                    $arraydisc[] = $data['id'];
                                }
                                if ($fic==5&&strpos(strval($data['semestrzachet']),$necesssem)!==false) {
                                    $arraydisc[] = $data['id'];
                                }
                                if ($fic==6&&strpos(strval($data['semestrkursovik']),$necesssem)!==false) {
                                    $arraydisc[] = $data['id'];
                                }
                                if ($fic==36&&strpos(strval($data['semestrkp']),$necesssem)!==false) {
                                    $arraydisc[] = $data['id'];
                                }
                                if ($fic==39&&strpos(strval($data['semestrdiffzach']),$necesssem)!==false) {
                                    $arraydisc[] = $data['id'];
                                }
                            } 
                            if ($fic==13) {
                                $practices=mysql_query("SELECT id, term, name, practicetypeid FROM dean.mdl_bsu_plan_practice WHERE planid IN ($plans) AND edworkkindid=13");
                                while($data=mysql_fetch_array($practices)) {
                                   if ($data['term']==$semid)   {
                                       $practicetype=mysql_query("SELECT id, name FROM dean.mdl_bsu_ref_practice_type WHERE id=".$data['practicetypeid']);
                                       $pract=mysql_fetch_assoc($practicetype);
                                       $list[$data['id']] = $pract['name'].' '.$data['name'];
                                   }
                                }
                            }
                        }
                        
                        if ($arraydisc>0) {
                           $arraydisc = implode(',', $arraydisc);
                           $disciplines=mysql_query("SELECT a.id as id, b.name as name 
                                                    FROM dean.mdl_bsu_discipline a 
                                                    INNER JOIN dean.mdl_bsu_ref_disciplinename b ON a.disciplinenameid=b.id
                                                   	WHERE a.id IN ($arraydisc)
                                                    ORDER BY b.name"); 
                           while($discipline=mysql_fetch_array($disciplines)){
								$list[$discipline['id']] = $discipline['name'];
						   }
                        }

                        if(count($list)>0) {
							$href = "index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;ct=$gid&amp;uid=$uid&amp;gid=$gid&amp;semid=$semid&amp;fic=$fic&amp;did=";
							echo'<center>';
							popup_form($href, $list, "discipline", $did, "", "", "", false);
							echo'</center><br>';
    					}

    					if($did) {
							echo"<form name='form_bkp' method='post' action='index.php'>";
							$verify = get_record_select('dean_service', "userid=$deanuser AND serviceid=2 AND printedtime=0 AND groupid=$currenttab AND did=$did AND semid=$semid AND fic=$fic", 'id');
							$counts = get_record_select('dean_service', "userid=$deanuser AND serviceid=2 AND groupid=$currenttab AND did=$did AND semid=$semid AND fic=$fic", 'count(id) as id');
							$a = $counts->id;
							switch($a) {
								case 0: case 1: case 2: $color = '#00AA00';
								break;
								default: $color = '#AA0000';
							}
							print_heading("<font color='$color'>".get_string('count_service2', 'block_cdoservice').$a.'</font>', 'center', 4);
                        

//							if($errordisc==1||$errorgrcount==1) {
//								$text = '';
//							} else {
							 
								$text = '<input name ="order2" type="submit" value="'. get_string('order', 'block_cdoservice').'">';
//							}
                            $max = 2;
                            /*
                            switch($fic) {
                                case 4: 
                                    $max = 1;
                                break;
                                case 5:
                                    $max = 1;
                                break;
                                default:
                                    $max = 1;
                            }
                            */                            

                            $cur_date = date('d.m.Y', time());
                            if($verifys = get_records_select('dean_service', "userid=$deanuser AND serviceid=2 AND groupid=$currenttab AND printedtime=0", '', 'id, timemodified')) {
                                $count_date = 0;
                                foreach($verifys as $data) {
                                    $this_date = date('d.m.Y', $data->timemodified);
                                    if($cur_date == $this_date) $count_date++;     
                                }
                                if($count_date >= $max) $text = '<font color="#AA0000">Вы уже сегодня заказывали '.$max.' экзаменационный(х) лист(а). Завтра сможете заказать снова.</font>';
                            }
                            //echo "count_date=$count_date";
        					$table->data[] = array('1', get_string('doc2', 'block_cdoservice'), '', '1', $text);
                      }
//    					} else {
//    						print_heading(get_string('error_discipline_not_found', 'block_cdoservice'), 'center', 4);
					}
				}
				if($did == 0) $table = '';
			} else {
				print_heading('<font color="#AA0000">'.get_string('error22', 'block_cdoservice').'</font>', 'center', 4);
				echo"<form name='form_bkp' method='post' action='index.php'>";
			}
		break;
		case 3:
			if($reception_inquiry == 0) $reception_inquiry = 1;

			$visible_session = 0;
			if($sesid == 0) $sesid = get_current_sessionid($gid);
			for($i=0;$i<11;$i++) {
//print "edyearid=$edyearid AND numsession=$i AND isset=1 AND groupid=$gid<br>";
				if($verify = get_record_select('dean_journal_session', "edyearid=$edyearid AND numsession=$i AND isset=1 AND groupid=$gid", 'id') || ($i==0)) {
					$list[$i] = get_string('session'.$i, 'block_dean');
					if($i!=0) {
						$visible_session = 1;
						$jjj[$i] = $i;
					}
				}
			}

			$jjjjj = 0;
			for($i=0;$i<7;$i++)
				if(isset($jjj[$sesid])) $jjjjj = 1;

			$jj = 0;
			if($jjjjj == 0) {
				for($i=0;$i<7;$i++)
					if(isset($jjj[$i]) && ($jj == 0)) {
						$jj = $jjj[$i];
						$sesid = $jj;
					}
			}
			echo '<center>';

			$curredyearid = get_current_edyearid();
			$nextedyearid = $curredyearid + 1;
			$prevedyearid = $curredyearid - 1;
			$toprow = array();
			$toprow[] = new tabobject('yid'.$prevedyearid, "index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;gid=$gid&amp;uid=$uid&amp;yid=$prevedyearid", 'Прошлый уч. год');
			$toprow[] = new tabobject('yid'.$curredyearid, "index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;gid=$gid&amp;uid=$uid&amp;yid=$curredyearid", 'Текущий уч. год');
			$toprow[] = new tabobject('yid'.$nextedyearid, "index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;gid=$gid&amp;uid=$uid&amp;yid=$nextedyearid", 'Следующий уч. год');
			$tabs = array($toprow);
			print_tabs($tabs, 'yid'.$edyearid, NULL, NULL);

			if($visible_session) {
				print_heading(get_string('session_text', 'block_cdoservice'), 'center', 4);
				popup_form("index.php?ct=$gid&amp;cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=$gid&amp;cst=$currentservicetab&amp;uid=$uid&amp;riy=$reception_inquiry&amp;yid=$edyearid&amp;sesid=",
							$list, "session", $sesid, "", "", "", false);
			}
			print_heading(get_string('error12', 'block_cdoservice'), 'center', 5);
			echo '</center>';

			print_heading(get_string('reception_inquiry', 'block_cdoservice'), 'center', 4);
			unset($list);
			$list[] = get_string('reception_inquiry00', 'block_cdoservice');
			$list[] = get_string('reception_inquiry1', 'block_cdoservice');
			$list[] = get_string('reception_inquiry2', 'block_cdoservice');
			$list[] = get_string('reception_inquiry3', 'block_cdoservice');
			$list[] = get_string('reception_inquiry4', 'block_cdoservice');

			$href = "index.php?ct=$gid&amp;cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=$gid&amp;uid=$uid";
			echo'<center>';
			popup_form("index.php?ct=$gid&amp;cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=$gid&amp;cst=$currentservicetab&amp;uid=$uid&amp;yid=$edyearid&amp;riy=", $list, "course", $reception_inquiry, "", "", "", false);
			echo'</center>';

			echo"<form name='form_bkp' method='post' action='index.php'>";

			$str = get_string('reception_inquiry_info'.$reception_inquiry, 'block_cdoservice');

            $user=mysql_query("SELECT id, address, email, phone1 FROM dean.mdl_user WHERE id=".$deanuser);
            $user=mysql_fetch_array($user); 

			switch($reception_inquiry) {
				case 2:
					$str.= '<input size="100" name ="reception_inquiry" type="text" value="'.$user['address'].'">';
					print_heading($str, 'center', 4);
				break;
				case 3:
					$str.= '<input size="100" name ="reception_inquiry" type="text" value="'.$user['email'].'">';
					print_heading($str, 'center', 4);
				break;
				case 4:
					$str.= '<input size="100" name ="reception_inquiry" type="text" value="'.$user['phone1'].'">';
					print_heading($str, 'center', 4);
				break;
			}

			print_heading('<font color="#FF0000">'.get_string('reception_inquiry_info', 'block_cdoservice').'</font>', 'center', 4);

			$text = '<input name ="order1" type="submit" value="'. get_string('order', 'block_cdoservice').'">';
			$printedtime = '';

			if($sesid == 6) {
				$printedtime = "AND printedtime=0";
			}

//print "edyearid=$edyearid<br />";            
			$verify = get_record_select('dean_service', "userid=$deanuser AND serviceid=3 AND groupid=$currenttab AND yearid=$edyearid AND sessionid=$sesid $printedtime", 'id, usermodified, printedtime');
//print "userid=$uid AND serviceid=3 AND groupid=$currenttab AND yearid=$edyearid AND sessionid=$sesid $printedtime";
        		$text = '';
			if($verify) {
				if($methodist_is!=false) {
				    if($verify->printedtime == 0)
				        $text = get_string('status_work_notprint', 'block_cdoservice');
				    else
					$text = get_string('error8', 'block_cdoservice').'<br>'.
							"<a href='index.php?cs=4&amp;id=$fid&amp;gid=$currenttab&amp;uid=$uid'>".get_string('service_journal', 'block_cdoservice').'</a>';
					if($admin_is) {
						$text.= "<br><br><a href='index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;gid=$currenttab&amp;uid=$uid&amp;delete=$verify->id&amp;sesid=$sesid&amp;riy=$reception_inquiry'>".get_string('delete').'</a>';
					}
				} else {
					if($student_is && !($methodist_is)!=false) {
						if($USER->id == $verify->usermodified) {
							$text = get_string('error9', 'block_cdoservice');
						} else {
							$text = get_string('error09', 'block_cdoservice');
						}
					}
/*
					if($methodist_is) {
						if($USER->id == $verify->usermodified || $verify->usermodified == 0) {
							$text = get_string('error09', 'block_cdoservice');
						} else {
							$text = get_string('error9', 'block_cdoservice');
						}
					}
*/
				}
			} else {
				if($reception_inquiry != 0) $text = '<input name ="order3" type="submit" value="'. get_string('order', 'block_cdoservice').'">';
			}

			if($visible_session == 0) {
				$text = get_string('error10', 'block_cdoservice');
			}
			$firstname = explode(' ', $username['firstname']);
			$c = count($firstname);
			$error11 = '';
			if(count($firstname) == 2) {
//print "codephysperson='$username->username' AND grup='$user_groupname' AND numprikazotsh=''";			 
                 
                $sex=mysql_query("SELECT distinct sex FROM dean.mdl_bsu_students WHERE codephysperson=".$username['username']);
                $sex=mysql_fetch_array($sex); 
                
                if(!isset($sex['sex'])) {
					$error11 = get_string('error22', 'block_cdoservice');
					$sex['sex'] = -1;
				}
                
	            $fiodatpad = DativeCase($username['lastname'], $firstname[0], $firstname[1], $sex['sex']);
                
			} else {
				$fiodatpad = $username['lastname'].' '.$username['firstname'];
			}
            
			if($error11 != '') $text = $error11;

$list_text1[] = 'Выберите...';
$list_text1[] = 'Для допуска к вступительным испытаниям';
$list_text1[] = 'Для слушателя подготовительного отделения';
$list_text1[] = 'Для обучающегося';

$list_text2[] = 'Выберите...';
$list_text2[] = 'Прохождение вступительных испытаний';
$list_text2[] = 'Промежуточная аттестация';
$list_text2[] = 'Государственная итоговая аттестация';
$list_text2[] = 'Итоговая аттестация';
$list_text2[] = 'Подготовка и защита выпускной квалификационной работы и/или сдача итоговых государственных экзаменов';
//$list_text2[] = 'Завершение диссертации на соискание ученой степени кандидата наук';

$list_text3[] = 'Выберите...'; 
$list_text3[] = 'Основное общее';  
$list_text3[] = 'Cреднее общее';  
$list_text3[] = 'Cреднее профессиональное';  
$list_text3[] = 'Высшее образование'; 
if(!isset($frm->text1)) $frm->text1=0;
if(!isset($frm->text2)) $frm->text2=0; 
if(!isset($frm->text3)) $frm->text3=4;


echo'<table align="center">';
print_row('Тип документа: ', choose_from_menu($list_text1, 'text1', $frm->text1, '', '', '0', true, false),'center');
print_row('Причина вызова: ', choose_from_menu($list_text2, 'text2', $frm->text2, '', '', '0', true, false),'center');
print_row('Уровень образования: ', choose_from_menu($list_text3, 'text3', $frm->text3, '', '', '0', true, false),'center');
echo'</table>';


			$dopinfo = '<table width="100%"><tr><td align="center"><input name ="service3" size="80" type="text" value=""></td></tr>
						<tr><td align="center"><font size="1"><b>'.get_string('service3', 'block_cdoservice').'</b></font></td></tr>
						<tr><td align="center"><input name="fiodatpad" size="80" type="text" value="'.$fiodatpad.'"></td></tr>
						<tr><td align="center"><font size="1"><b>'.get_string('service4', 'block_cdoservice').'</b></font></td></tr></table>';
			
            $table->data[] = array(	'1',
									get_string('doc3', 'block_cdoservice'),
									$dopinfo,
									'1',
									$text);
		break;
		case 4:
			print_heading("<a href='{$CFG->wwwroot}/message/discussion.php?id=67674'>".get_string('send_complaint1', 'block_cdoservice')."</a>", 'center', 4);
			print_heading('<font color="#AA0000">'.get_string('send_complaint2', 'block_cdoservice').'</font>', 'center', 4);
			print_footer();
			exit();
		break;
	}
	print_table($table);

	if((($methodist_is!=false) || $admin_is)&&($currentservicetab != 3)&&($errordisc!=1)&&($errorgrcount!=1))
		echo'<center><input name ="rezervnumber" type="submit" value="'. get_string('rezervnumber', 'block_cdoservice').'"></center>';

	if($currentservicetab != 4)
  		if($notprintservices = get_records_select('dean_service', "userid=$deanuser AND serviceid=$currentservicetab AND yearid=$edyearid and groupid=$currenttab", '',
													'id, groupid, userid, count, predyavlenie, stip, work, fiodatpad, timemodified, reception_inquiry, sessionid, yearid, fic, did, serviceid, printedtime, semid')) {
          unset($table);
			$table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center');
            switch($currentservicetab) {
            	case 1:
					$table->head = array(get_string('npp', 'block_cdoservice'),
										get_string('time_service_zakaz', 'block_cdoservice'),
										get_string('stip', 'block_cdoservice'),
										get_string('predyavlenie', 'block_cdoservice'),
										get_string('countservice', 'block_cdoservice'),
										get_string('status'),
										get_string('action')
										);
            	break;
            	case 2:
					$table->head = array(get_string('npp', 'block_cdoservice'),
										get_string('time_service_zakaz', 'block_cdoservice'),
										get_string('forma', 'block_cdoservice'),
										get_string('discipline', 'block_cdoservice'),
										get_string('countservice', 'block_cdoservice'),
										get_string('status'),
										get_string('action')
										);
            	break;
            	case 3:
					$table->head = array(get_string('npp', 'block_cdoservice'),
										get_string('time_service_zakaz', 'block_cdoservice'),
										get_string('type_session', 'block_cdoservice'),
										get_string('time_session', 'block_cdoservice'),

										get_string('working', 'block_cdoservice'),
										get_string('fiodatpad', 'block_cdoservice'),
										get_string('status'),
										get_string('action')
										);
            	break;
            }

			$i = 1;
			$idgroup = $gid;

			foreach($notprintservices as $data) {
                $user=mysql_query("SELECT id, firstname, lastname FROM dean.mdl_user WHERE id=".$data->userid);
                $user=mysql_fetch_array($user); 

                $gruppa = get_record_select('dean_academygroups_members', "userid=$data->userid AND academygroupid in ($idgroup)", 'academygroupid');
               
                $gruppa=mysql_query("SELECT id,groupid FROM dean.mdl_bsu_group_members WHERE username='".$data->userid."' AND groupid in ($idgroup)");
                if(mysql_num_rows($gruppa)>0) 
                {
                $gruppa=mysql_fetch_array($gruppa); 
                $gruppa=mysql_query("SELECT id,name FROM dean.mdl_bsu_ref_groups WHERE id='".$gruppa['groupid']."'");
                $gruppa=mysql_fetch_array($gruppa); 
		    	$gruppa = $gruppa['name'];
                }
                
		    	$session = '';
				$font_start = '';
				$font_end = '';
				$time_session = '';
		    	if($data->sessionid != 0) {
		    		$session = get_string('session'.$data->sessionid, 'block_dean');
//					$edyearid = get_current_edyearid();
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
						$time_session = get_string('notimesession', 'block_cdoservice');
					}
				}
                if($data->printedtime == 0) {
                	$status = get_string('status_work_notprint', 'block_cdoservice');
                	$href = "<a href='index.php?cs=$servicetab&amp;id=$fid&amp;cst=$currentservicetab&amp;gid=$currenttab&amp;uid=$uid&amp;npdel=$data->id&amp;sesid=$sesid&amp;riy=$reception_inquiry'>".get_string('delete').'</a>';
                } else {
                	$status = get_string('status_work_print', 'block_cdoservice');
                	$href = '';
                }
	            switch($currentservicetab) {
	            	case 1:
					    $table->data[] = array($i,
					                            date('d.m.Y H:i:s', $data->timemodified),
					    						get_string('stip'.$data->stip, 'block_cdoservice'),
					    						$data->predyavlenie,
					    						$data->count,
					    						$status,
					    						$href
					    						);
					break;
					case 2:
						$forma = $data->predyavlenie;
						switch($data->fic) {
						    case 13: $forma = get_string('pract', 'block_cdoservice'); break;
							case 4: $forma = get_string('examination', 'block_dean'); break;
							case 5: $forma = get_string('zaschet', 'block_dean'); break;
						}
                        if ($data->fic!=13)
                        {
                        $discipline_name=mysql_query("SELECT rd.name 
                        FROM dean.mdl_bsu_discipline d
                        INNER JOIN dean.mdl_bsu_ref_disciplinename rd ON rd.id=d.disciplinenameid
                        WHERE d.id='".$data->did."'");
                        $discipline_name=mysql_fetch_array($discipline_name); 
                        if(!mysql_num_rows($discipline_name)>0)
                        {
                         $dname = $discipline_name['name'];   
                        }
                        else
                        {
                        $dname = '';   
                        }
                        }
                        else
                        {
                        $practice=mysql_query("SELECT p.name, pt.name as pname
                        FROM dean.mdl_bsu_plan_practice p
                        INNER JOIN dean.mdl_bsu_ref_practice_type pt ON pt.id=p.practicetypeid
                        WHERE p.id='".$data->did."'");
                        $practice=mysql_fetch_array($practice); 
                        if(!mysql_num_rows($practice)>0)
                        {
                         $dname = $practice['pname'].' '.$practice['name']; 
                        }
                        else
                        {
                         $dname = '';   
                        }   
                        }
					    $table->data[] = array($i,
					                            date('d.m.Y H:i:s', $data->timemodified),
					    						$forma,
					    						$dname,
					    						$data->count,
					    						$status,
					    						$href
					    						);
					break;
					case 3:
					    $table->data[] = array($font_start.$i.$font_end,
					    						$font_start.date('d.m.Y H:i:s', $data->timemodified).$font_end,
					    						$font_start.$session.$font_end,
				    							$font_start.$time_session.$font_end,
					    						$font_start.$data->work.$font_end,
					    						$font_start.$data->fiodatpad.$font_end,
												$font_start.$status.$font_end,
												$href
					    						);
					break;
				}
				$i++;
			}
			print_heading(get_string('list_service', 'block_cdoservice'), 'center', 4);
			print_table($table);
  		}
/*
    if($currentservicetab == 1) {
		print '<center><input name="insertmesto" type="submit" value="'. get_string('insert_mesto', 'block_cdoservice').'">'.'</center>';
	}
*/
//print "edyearid=$edyearid";
	echo'<input type="hidden" name="id" value="'.$fid.'" />
		<input type="hidden" name="ct" value="'.$currenttab.'" />
		<input type="hidden" name="cs" value="'.$servicetab.'" />
		<input type="hidden" name="cst" value="'.$currentservicetab.'" />
		<input type="hidden" name="sid" value="'.$sid.'" />
		<input type="hidden" name="yid" value="'.$edyearid.'" />
		<input type="hidden" name="gid" value="'.$gid.'" />
		<input type="hidden" name="uid" value="'.$uid.'" />
        <input type="hidden" name="puid" value="'.$deanuser.'" />
		<input type="hidden" name="did" value="'.$did.'" />
		<input type="hidden" name="fic" value="'.$fic.'" />
		<input type="hidden" name="sesid" value="'.$sesid.'" />
        <input type="hidden" name="semid" value="'.$semid.'" />
		<input type="hidden" name="ido" value="'.$ido.'" />
		<input type="hidden" name="riy" value="'.$reception_inquiry.'" />
	</form>';
?>
