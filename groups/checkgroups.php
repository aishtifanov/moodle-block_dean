<?PHP // $Id: checkgroups.php,v 1.2 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

	$cid = optional_param('cid', 0, PARAM_INT);	// # KOL_CURRICULUM
	$yid = optional_param('yid', 0, PARAM_INT);	// # учебный год
	$action   = optional_param('action', 'no');

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('checkgroups', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$site->shortname: $strsearchgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    if ($action == 'check') 	{

        if ($check_recs = get_records('dean_temp_check')) 	{

            print_heading ('Проверка началась ...', 'center', 2);

            foreach ($check_recs as $agroup)	{

                $agroup_arr = array();
                $agroup_arr = explode(';', $agroup->userids);

   				if ($mgroups = get_records_sql("SELECT id, courseid, name FROM {$CFG->prefix}groups
												 WHERE courseid = {$agroup->courseid} AND name = '{$agroup->groupname}'"))   {
                                // print_r($agroupsw);  exit();
								$numgroups = count($mgroups);
								if ($numgroups == 1)  {
								    $mgroups0 = current($mgroups);
									if ($memgr = get_records('groups_members', 'groupid', $mgroups0->id, 'userid'))	{

                                        $mgroup_arr = array();
									    foreach ($memgr as $m)	{
									    	$mgroup_arr[] =  $m->userid;
									    }

                                        $agroup_diff = array_diff ($agroup_arr, $mgroup_arr);
                                        // print_r($agroup_diff); echo '!<hr>';
                                        // print_r($mgroup_diff); echo '#<hr>';
                                        // continue;
                                        if (!empty($agroup_diff))   {

                                            foreach ($agroup_diff as $addid)	{

												 if (!enrol_student_dean($addid, $agroup->courseid))  {
					    		                      notify("Could not add student with id $addid to the course {$agroup->courseid}!");
	    					 			         } else {
									                  print("<br>Enrol student with id $addid to the course {$agroup->courseid}.");
									                  echo '<br>';
	    					 			         }

						                         $strsql = "SELECT g.id, g.name, g.courseid, m.userid
															   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id = m.groupid
															   WHERE (g.courseid={$agroup->courseid}) AND (m.userid=$addid)";
	   										     if ($duplgroups = get_records_sql($strsql))	{
														foreach ($duplgroups as $duplgroup)	 {
									 	                	delete_records('groups_members', 'groupid', $duplgroup->id, 'userid', $addid);
														}
											     }

							                	 if (!$newmemberwas = get_record('groups_members', 'groupid', $mgroups0->id, 'userid', $addid))	 {
												     $newmember->groupid = $mgroups0->id;
								    	             $newmember->userid = $addid;
								        	         $newmember->timeadded = time();
								            	     if (!insert_record('groups_members', $newmember)) {
								                	    notify("ERROR occurred while adding user $addid to group $agroup->groupname!!!!!!!!");
									                 } else {
									                    print("Adding user $addid to group $agroup->groupname.<br>");
									                    echo '<br>';
									                 }
									             }
	   					 			        }
	   					 			    }

                                        $mgroup_diff = array_diff ($mgroup_arr, $agroup_arr);
                                        if (!empty($mgroup_diff))   {
                                             foreach ($mgroup_diff as $mddid)	{

												 if (!unenrol_student_dean($mddid, $agroup->courseid))  {
					    		                      notify("ERROR. COULD NOT UNENROL student with id $mddid to the course {$agroup->courseid}!!!!!!!");
	    					 			         } else {
									                  print("UnEnrol student with id $mddid from the course {$agroup->courseid}.");
									                  echo '<br>';
	    					 			         }
                                             }
                                        }

                                        unset($agroup_diff);
                                        unset($mgroup_diff);
                                    }
                                }  else {

                                }
                  }

            }

            print_heading ('Проверка закончена!', 'center', 2);
        }
	}


    if ($curriculums = get_records ('dean_curriculum', 'formlearning', 'correspondenceformtraining', 'facultyid'))		{

		foreach ($curriculums as $curriculum)	{
			$numag=0;
			if ($academygroups = get_records('dean_academygroups',  'curriculumid', $curriculum->id, 'name'))		{
			    foreach ($academygroups as $academygroup)	{
			        $len = strlen($academygroup->name); 
			        if (($len == 6 || $len == 8) && is_numeric($academygroup->name))	$numag++;
			    }
			}
			if ($numag > 0)	{
	            $sortidarray[$curriculum->id] = $numag;
	            $currnamearray[$curriculum->id] = $curriculum->name;
	        }
        }
        arsort ($sortidarray);

		$currmenu = array();
	    $currmenu[0] = get_string('selectacurr', 'block_dean') . ' ...';

        $cc = 0;
		// foreach ($sortidarray as $currid => $numag )	{
		foreach ($currnamearray as $currid => $name )	{
			$cc++;
			$len = strlen ($currnamearray[$currid]);
			if ($len > MAX_SYMBOLS_LISTBOX)  {
				$currnamearray[$currid] = mb_substr($currnamearray[$currid],  0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
			}

			// $currmenu[$currid] = $cc . '. ' . $currnamearray[$currid] . " ($numag)";
			$currmenu[$currid] = $cc . '. ' . $currnamearray[$currid] . " ($sortidarray[$currid])";
        }


		$yearmenu = array();
	    $yearmenu[0] = get_string('selectayear', 'block_dean') . ' ...';
	    $curryear = current_edu_year_number();
	    for ($i=$curryear-6; $i<=$curryear; $i++) {
	        $yearmenu[$i] = '200'.$i;
	    }

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    echo '<tr><td>'.get_string('curriculum', 'block_dean') . ':</td><td>';
		popup_form("checkgroups.php?cid=", $currmenu, 'switchcurr', $cid, '', '', '', false);
	    echo '</td></tr>';
	    echo '<tr><td>'.get_string('startyear', 'block_dean') . ':</td><td>';
		popup_form("checkgroups.php?cid=$cid&amp;yid=", $yearmenu, 'switchyear', $yid, '', '', '', false);
	    echo '</td></tr>';

		echo '</table>';
    }

    $n_a = 0;
    $all_dean_amembers = 0;
    $n_insert = 0;
    if ($cid != 0 && $yid !=0)	{

        delete_records('dean_temp_check');

	    if ($curriculum = get_record ('dean_curriculum', 'id', $cid))		{

            $startterm = 1;
            $endterm = ($curryear - $yid + 1)*2; // 8 - 8; 8 - 7; 8 - 6

		    $strsql = "SELECT DISTINCT courseid, name
					  FROM mdl_dean_discipline
					  where curriculumid = $cid AND courseid != 1 AND term>= $startterm AND term <= $endterm";
			//echo $strsql.'<br>';


			if ($disciplines = get_records_sql ($strsql))	{

                // print_r($disciplines); echo '<hr>';

				$table->head  = array ('#', get_string('groups'), get_string("numofstudents","block_dean"));
				$table->align = array ("left", "left", "center");

	   			// $table->data[] = array ($cc . '. ' . $curriculum->name);

                if ($yid < 10)	{
                	$name_shablon = '__0'.$yid.'__';
                } else {
               		$name_shablon = '__'.$yid.'__';
                }
				if ($academygroups = get_records_sql("SELECT id, name FROM mdl_dean_academygroups
				 									  WHERE curriculumid=$cid AND name LIKE '$name_shablon'"))		{
                    $n_a=0;
                    $all_dean_amembers = 0;
				    foreach ($academygroups as $academygroup)	{
				        // if (strlen($academygroup->name) != 6)	continue;

			   			if ($damem = get_records_sql("SELECT id, userid FROM  mdl_dean_academygroups_members
													  WHERE academygroupid = {$academygroup->id}")) 	{
							$dean_amembers = count($damem);
						} else {
							$dean_amembers = 0;
						}
                        $table->data[] = array (++$n_a, $academygroup->name, $dean_amembers);
                        $all_dean_amembers += $dean_amembers;

                        $amembersarray = array();
	    				foreach ($damem as $da)  {
	        				$amembersarray[] = $da->userid;
	   					}
	    				$amemberslist= implode(';', $amembersarray);

	    				foreach ($disciplines as $discipline)  {
	    				    $rec->courseid = $discipline->courseid;
	    				    $rec->groupname =  $academygroup->name;
	    				    $rec->userids = $amemberslist;
	    					if (!insert_record('dean_temp_check', $rec))	{
	    						error('ERROR insert temporary record in dean_temp_check');
	    					} else {
	    						++$n_insert;
	    					}
	    				}

                    }

                    print_table($table);
                }

                unset($table);

                $table->head  = array ('#', get_string('discipline','block_dean'));
				$table->align = array ("left", "left");
                $n_d=0;
				foreach ($disciplines as $discipline)	 {
				    $table->data[] = array (++$n_d, $discipline->name);
				}
				print_table($table);
				print_heading('Семестры: '.$startterm.' ... '.$endterm, 'center', 4);
				print_heading('Количество записей: '.$n_a*$n_d . '(' . $n_insert . ')', 'center', 4);
				print_heading('Количество проверок: '.$all_dean_amembers*$n_d, 'center', 4);

				?><table align="center">
				<tr>
				<td>
				<form name="check" method="post" action="checkgroups.php?action=check&amp;cid=<?php echo $cid; ?>">
				    <div align="center">
					<input type="submit" name="startcheck" value="<?php print_string("continue")?>">
				    </div>
			  </form>
				</td>
				</tr>
			  </table>
			  <?php
            }
        }
    }


    print_footer();

function current_edu_year_number()
{
    $year = date("y");
    $m = date("n");
    if(($m >= 1) && ($m <= 8 )) {
		$y = $year-1;
    } else {
		$y = $year;
    }
	return $y;
}


?>