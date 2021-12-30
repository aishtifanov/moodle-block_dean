<?php // $Id: enrallstud.php,v 1.6 2013/09/03 14:01:08 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');
    $struser = get_string("user");
    $strimportfac = 'Регистрация всего первого курса на дисциплинах Вводного тестирования';// get_string('enrolmentnew') . ' ТОЗ';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> $strimportfac";
    print_header("$SITE->shortname: $strimportfac", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    // $frm->kurs1 = 1;
    $frm->kurs2 = 1;
    // $frm->kurs3 = 1;
    
    // $courses = array(5189, 5177, 3113, 4155, 4156, 4157, 4158, 4160, 4204, 4205, 4219, 6199);
    $courses = array(6136);

    $sql = "SELECT * FROM {$CFG->prefix}dean_faculty where number>10100 ORDER BY number";
    // $sql = "SELECT * FROM {$CFG->prefix}dean_faculty where number in (18100) ORDER BY number";
	if ($allfacs = get_records_sql($sql))	{
	   foreach ($allfacs as $faculty) 	{
	       print_heading($faculty->name, "center");
           $fid = $faculty->id; 
            
           foreach ($courses as $courseid)  { 

                // формируем шаблоны имен групп
                $templategrnames = get_name_groups_templates($faculty);
                // print_object($templategrnames);  exit ();
                           
        	    $currtime = time();
        
                $strsql = "SELECT id, facultyid FROM {$CFG->prefix}dean_curriculum
                           WHERE facultyid=$fid AND formlearning = 'daytimeformtraining'";
        
        
        	    if ($curriculums = get_records_sql ($strsql))		{
	  		        print_heading( 'Количество рабочих учебных планов ' . count($curriculums), 'center', 4);

        			foreach ($curriculums as $curriculum)	{
        			  foreach($templategrnames as $kurs => $grname)    {
        			    
                        $startyear = '20'.substr($grname, 4, 2);
                        //print $startyear;  
        				$numag=0;
                        $strsql = "SELECT id, name FROM {$CFG->prefix}dean_academygroups
                        		  WHERE  curriculumid = {$curriculum->id} AND (name like '$grname%' or startyear=$startyear)";
                                  
                        print $strsql . '<br>'; 
        				if ($academygroups = get_records_sql($strsql))		{
        			        // $academygroupsarray = array();
           			        //$academygroupslist = implode(',', $academygroupsarray);
        	                // $strsql = "SELECT id, userid FROM {$CFG->prefix}dean_academygroups_members WHERE  academygroupid in $academygroupslist";
        					print_heading( "Количество академических групп для РУП = $curriculum->id с шаблоном '$grname' = " . count($academygroups), 'center', 5);
                            // echo '<pre>'; print_r($academygroups); echo '</pre>'; 
                            // continue;
        
        				    foreach ($academygroups as $academygroup)	{
        
        				        if (check_dean_group_name($academygroup->name))	{
        
        			                $strsql = "SELECT id, userid FROM {$CFG->prefix}dean_academygroups_members
        			                		  WHERE  academygroupid = {$academygroup->id}";
        
        							if ($academygroup_members = get_records_sql($strsql))	{
        
        				                unset($newgroup);
        
          	                            if ($mgroupa = get_record('groups', 'courseid', $courseid, 'name', $academygroup->name))	{
          	                            	$newgroup->id = $mgroupa->id;
          	                            } else {
        			                        $newgroup->name = $academygroup->name;
        					                $newgroup->courseid = $courseid;
        					                $newgroup->description = $academygroup->name;
        					                $newgroup->lang = 'ru_utf8';
        					                $newgroup->timecreated = $currtime;
        					                if ($newgroup->id = insert_record("groups", $newgroup)) {
        					                    notify("Группа '$newgroup->name' успешно добавлена в курс $courseid", 'green');
        					                }  else {
        					                    error("Could not insert the new group '$newgroup->name'");
        					                }
        					            }
        
        								foreach ($academygroup_members as $amem)	{
        
        					           	 if (!$newmemberwas = get_record('groups_members', 'groupid', $newgroup->id, 'userid', $amem->userid))	 {
        
        			                       if (enrol_student_dean($amem->userid, $courseid)) {
        						  		          $record->groupid = $newgroup->id;
        				 	      	              $record->userid = $amem->userid;
        					   	    	          $record->timeadded = $currtime;
        				 	      	    	      if (!insert_record('groups_members', $record)) {
        			            	    	  		  error('!!> '. get_string('erroraddingusertogroup', 'block_dean', $academygroup->name));
        					               	      }
        			 	                   } else {
        									     if (!$usr = get_record('user', 'id', $amem->userid))	{
        							   		 	      	delete_records('dean_academygroups_members', 'userid', $amem->userid);
        	   									 } else {
        	   									 	if ($usr->deleted == 1)	 {
        							   		 	      	delete_records('dean_academygroups_members', 'userid', $amem->userid);
        							   		 	    } else {
        				   	                      	    error('!> '.get_string('enrolledincoursenot'). ' ' . $amem->userid );
        				   	                      	}
        			   	                      	 }
        			   	                   }
        			   	                 }
        
        						        }
        						    }
        
        				    	}  else {
        				    	    notify("Wrong group '$academygroup->name'!!!");
        				    	}
        					}
        
                			/// Insert discipline in curriculum
                            if (!$disc = get_record('dean_discipline', 'curriculumid', $curriculum->id, 'courseid', $courseid))	{
                
                				$rec->curriculumid = $curriculum->id;
                				$rec->courseid = $courseid;
                				$rec->cipher = '-';
                				$rec->auditoriumhours = 0;
                				$rec->selfinstructionhours = 0;
                				$rec->term = $kurs*2 - 1;
                				$rec->termpaperhours = 0;
                				$rec->controltype = 'zaschet';
                				if ($course = get_record('course', 'id', $courseid))	{
                					$rec->name = $course->fullname;
                				} else {
                					$rec->name = '---';
                				}
                				if (insert_record('dean_discipline', $rec))	{
                					 notify(get_string('disciplineadded','block_dean'), 'green');
                				} else {
                					error(get_string('errorinaddingdisc','block_dean'));
                                }
                            }
        
        				}
                      } // foreach $templategrnames
        	        } // foreach $curriculums
                }  else {
                	print_heading( 'Рабочие учебные планы не найдены!', 'center', 4);
                }
            }
        }    
    }       
	print_footer();


function check_dean_group_name($academygroupname)
{
 	$notzero = substr ($academygroupname, 2, 4);
    // if ($notzero == '0000')	return false;

	$len = strlen($academygroupname);
	$lastsym = substr ($academygroupname, $len - 1, 1);
    if ($len == 7 && ($lastsym == 'a' || $lastsym == 's'))	return true;

    if ($len == 6 && is_numeric($academygroupname))	return true;
    
    if ($len == 8 && is_numeric($academygroupname))	return true;

	return false;
}


function combobox_courses_TOZ($scriptname, $courseid)
{
  global $CFG;

    $courseids = array(1700, 1711);

    $categorys = array(64, 54);
    
    $childs = get_records_select_menu('course_categories', "parent=64", '', 'id as id1,  id as id2');
    foreach ($childs as $child)   {
        $categorys[] = $child;
    }
    
    foreach ($categorys as $category)   {
    	$courseTOZ = get_courses ($category); // , 'c.fullname'); // 64
        foreach ($courseTOZ as $ct)	{
        	$courseids[] = 	$ct->id;
        }
    }    

  	$coursemenu = array();
  	// $coursemenu[0] = get_string('selectadiscipline', 'block_dean') . ' ...';
    
    $strids = implode (',', $courseids);
    // $tozcourses = get_records_sql("SELECT id, fullname FROM {$CFG->prefix}course WHERE id in ($strids) ORDER BY fullname");
    $coursemenu = get_records_sql_menu("SELECT id, fullname FROM {$CFG->prefix}course WHERE id in ($strids) ORDER BY fullname");
    $coursemenu[0] = get_string('selectadiscipline', 'block_dean') . ' ...';

/*
	foreach ($courseids as $crsid) 	{
		if($course = get_record_sql("SELECT id, fullname FROM {$CFG->prefix}course WHERE id = $crsid"))   {
			$coursemenu[$crsid] = $course->fullname;
		}
	}
*/

    // $scriptname
	return choose_from_menu($coursemenu, 'did', $courseid, '', '', '', true);
}

function current_year_twodigit()
{
    $year = date("Y");
    $m = date("n");
    if(($m >= 1) && ($m <=8)) {  /// !!!!!!!!
		$y = $year-1;
    } else {
		$y = $year;
    }

	return $y-2000;
}

function get_name_groups_templates($faculty)
{
    global $frm;
    
    $arrnamegroups = array();
    
    $numfaculty = substr($faculty->number, 1, 4);
    // echo $numfaculty . '<br />';
    $curryear = current_year_twodigit();
    // echo $curryear . '<br />';
    for ($i=1; $i<=6; $i++) {
        $name = 'kurs'.$i;
        if (isset($frm->{$name}))   {
            $groupyear = $curryear - ($i - 1);
            // echo $groupyear . '<br />';
            if ($groupyear < 10) {
                $nul = '0';
            } else {
                $nul = '';
            }
            $arrnamegroups[$i] = $numfaculty.$nul.$groupyear;
        }
    }

    // print_object($arrnamegroups);
    
    return $arrnamegroups;
}    

?>
