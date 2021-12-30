<?php // $Id: index.php,v 1.0 2011/03/28 16:00:00 zagorodnyuk Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    require_once("../../../mod/quiz/editlib.php");
    require_once('../lib_quiz.php');
    require_once("../../../lib/questionlib.php");
	require_once("../service/lib.php");

	$id  = optional_param('id', 0);	     		// id
	$uid = optional_param('uid', 0);	 		// User id
	$fid = optional_param('fid', 0);	 		// Faculty id
	$did = optional_param('did', 0);	 		// Discipline id
	$sid = optional_param('sid', 0);	 		// Speciality id
	$gid = optional_param('gid', 0);	 		// Group id
	$kid = optional_param('kid', 0);	 		// Kafedra id
	$cid = optional_param('cid', 0);	 		// Course id
	$ed = optional_param('ed', ''); 			// data exam
	$bdid = optional_param('bdid', ''); 			// id course ct
	$createlink = optional_param('cl', 0); 		// статус отображения настроек с связями дисциплин
	$stats = optional_param('stats', 0); 		// статус отображения статистики
	$currenttab = optional_param('ct', 1); 		// id tabs
	$subid = optional_param('subid', 0); 		// id выбранной кафедры

    require_login();
    ignore_user_abort(false);
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @raise_memory_limit("256M");

    $link=mysql_connect('bsu-dekanat.bsu.edu.ru','ADMIN','big#psKT');
    $db_selected = mysql_select_db('dean', $link);
    mysql_query('SET NAMES utf8');
    $user=mysql_query("SELECT id, email FROM mdl_user WHERE email='".$USER->email."'");
    $user=mysql_fetch_array($user);      
    $deanuser=$user['id'];   

    $user='';
 	$action = '';
	if($currenttab == 2) $action = 'all';
	if($currenttab == 3) $action = 'alld';

	$frm = data_submitted();
	$notworktest = 0;
	if(isset($frm->notworktest)) $notworktest = 1;
	if(isset($frm->saveexcel)) {
		$action = 'all';
		$table = table_examschedule('', $action, $fid, $did, $sid, $gid, $kid, $subid);
		print_table_to_excel($table);
		exit();
	}

	if(isset($frm->saveexcelstatistics)) {
		$table = create_statistic();
		print_table_to_excel($table);
		exit();
	}

	if(isset($frm->removeselect)) {
		foreach ($frm->removeselect as $add) {
			$rec = get_record_sql("DELETE FROM {$CFG->prefix}dean_course_discipline WHERE disciplineid = $id AND courseid = $add AND departmentid = $subid AND specialityid = $sid");
		}
	}
	if(isset($frm->addselect)) {
		foreach ($frm->addselect as $add) {
    					$rec->disciplineid = $id;
    					$rec->courseid = $add;
    					$rec->departmentid = $subid;
    					$rec->specialityid = $sid;
    					$rec->usermodified = $deanuser;
                        $rec->timemodified = time();

                        insert_record('dean_course_discipline', $rec);
         }
	}

	$strtitle = get_string('examschedule', 'block_dean');
	$breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';

	$edittest = get_string('quiztab'.$currenttab, 'block_dean');

	$breadcrumbs .= " -> <a href=\"$CFG->wwwroot/blocks/dean/quiz/index.php\">" . $strtitle . '</a>';
	$breadcrumbs .= " -> $edittest";

	print_header("$SITE->shortname: $edittest", $SITE->fullname, $breadcrumbs);
    
	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();
	$teacher_is = true;
	$headdepartment_is = isheaddepartment();
	$dean_is = false;//isdean();
    
	$local = substr($CFG->wwwroot, 7);
	$question_types[] = 'multichoice';
	$question_types[] = 'truefalse';
	$question_types[] = 'match';
	$question_types[] = 'numerical';
	$question_types[] = 'shortanswer';
	$question_types[] = 'calculated';
	$question_types[] = 'description';
	$question_types[] = 'randomsamatch';
	$question_types[] = 'multianswer';
	$question_types[] = 'essay';
	$allselectquestion = 0;
	if($frm){
		if(isset($frm->savechangesct)) {
			$data->disciplineid = $id;
			$data->courseid = $cid;
			if(!$admin_is) {
				$data->departmentid = $headdepartment_is;
			} else {
				$headdepartment_is = $subid;
				$data->departmentid = $subid;
			}
			$data->specialityid = $sid;
			$data->usermodified = $deanuser;
			$data->timemodified = time();

			if($verify = get_record_select('dean_course_discipline', "disciplineid=$id AND departmentid=$headdepartment_is AND specialityid=$sid", 'id')) {
    			$data->id = $verify->id;
    			update_record('dean_course_discipline', $data);
			} else {
				insert_record('dean_course_discipline', $data);
			}
			redirect("index.php?action=alld", get_string('changessaved'), 2);
		}

		if(isset($frm->count)) {
			$coursecontexts = get_context_instance(CONTEXT_COURSE, $cid);
			$question_categories = get_records_select('question_categories', "contextid=$coursecontexts->id", '', 'id, name');
			$allselectquestion = 0;
			foreach($question_categories as $data) {
				for($j=0;$j<count($question_types);$j++) {
					$name_element = $question_types[$j];
					if(isset($frm->{$name_element}[$data->id])) {
						if(!isset($sum[$data->id])) $sum[$data->id] = 0;
						$s = $name_element.'['.$data->id.']';
						$answers_count[$s] = $frm->{$name_element}[$data->id];
						$allselectquestion = $allselectquestion + $frm->{$name_element}[$data->id];
					}
				}
			}
		}
		if(isset($frm->save)) {
			$error = 0;
			$count = 0;
			$questions0 = '';
			$coursecontexts = get_context_instance(CONTEXT_COURSE, $cid);
			$question_categories = get_records_select('question_categories', "contextid=$coursecontexts->id", '', 'id, name');
//print_object($question_categories);
			foreach($question_categories as $data) {
				for($j=0;$j<count($question_types);$j++) {
					$name_element = $question_types[$j];
					if(isset($frm->{$name_element}[$data->id])) {
						if(!isset($sum[$data->id])) $sum[$data->id] = 0;
						$sum[$data->id] = $sum[$data->id] + $frm->{$name_element}[$data->id];
					}
				}
				
/**/
				
				$questions = get_records_select('question', "category=$data->id AND qtype <> 'random'", '', 'id, qtype');
				if($questions) {
					$count = $count + $sum[$data->id];
					$questions_random = get_record_select('question', "category=$data->id AND qtype = 'random'", 'count(id) as count');
					$questions_random = $questions_random->count;
					if($sum[$data->id] > $questions_random) {
						$i = $sum[$data->id] - $questions_random;
						for($j=1; $j<=$i; $j++) {
							$questionsrandom->category = $data->id;
							$parent = get_record_sql("SELECT max(id) as max FROM {$CFG->prefix}question");
							$parent = $parent->max + 1;
							$questionsrandom->parent = $parent;
							$random = get_string('random', 'quiz');
							$questionsrandom->name = $random.' ('.$data->name.')';

							$questionsrandom->questiontext = 1;
							$questionsrandom->questiontextformat = 0;
							$questionsrandom->image = '';
							$questionsrandom->generalfeedback = '';
							$questionsrandom->defaultgrade = 1;
							$questionsrandom->penalty = 0.1;
							$questionsrandom->qtype = 'random';
							$questionsrandom->length = 1;
							$questionsrandom->hidden = 0;
							$questionsrandom->timecreated = time();
							$questionsrandom->timemodified = time();
							$questionsrandom->createdby = $uid;
							$questionsrandom->modifiedby = $uid;
							$time = date('ymdHis', time());
							$questionsrandom->stamp = $time;
							$questionsrandom->version = $time;
							insert_record('question', $questionsrandom);
							unset($questionsrandom);
						}
					}
				}
				if(isset($sum[$data->id])) {
					if(!empty($sum[$data->id])) {
						$questions_randoms = get_records_select('question', "category=$data->id AND qtype = 'random'", '', 'id', '0', $sum[$data->id]);
						if($questions_randoms) {
							foreach($questions_randoms as $questions_randoms0) {
					                  $questions0[] = $questions_randoms0->id;
							}
						}
					}
				}
			}
			
//print_object($sum);
			for($i=0;$i<100;$i++) {
				$old_index=rand(0, count($questions0)-1);
				$new_index=rand(0, count($questions0)-1);
				$old = $questions0[$old_index];
				$questions0[$old_index] = $questions0[$new_index];
				$questions0[$new_index] = $old;
			}

//print_object($questions0);
//exit();

			for($i=0;$i<count($questions0);$i++) {
				$question_new[$questions0[$i]] = 1;
			}

			$questions000 = $questions0;

			$first = $questions0[0];
			$questions0 = implode(',', $questions0);

			if($count > 40 || $count < 20) {
				print_heading(get_string('error_count_questions', 'block_dean'), 'center', 4);
				$error=1;
			}

			if($error == 0) {
				$ret = role_assign(4, $uid, 0, $coursecontexts->id);

                $strsql = "SELECT s.id, s.groupid, g.name
                FROM  mdl_bsu_schedule s
                INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
                WHERE s.id=$id";
                if ($agroups =  mysql_query($strsql))
                $agroup=mysql_fetch_assoc($agroups);
                $group = $agroup['name'];
                
				$quiz->course = $cid;
	   			$quiz->name = get_string('examination_test', 'block_dean').' ('.$group.')';
	   			$quiz->intro ='';
	   			$quiz->timeopen = 0;
	   			$quiz->timaclose = 0;
	   			$quiz->attempts = 2;
	   			$quiz->grademethod = 1;
	   			$quiz->decimalpoints = 2;
	   			$quiz->questionsperpage = 0;
	   			$quiz->shufflequestions = 1;
	   			$quiz->shuffleanswers = 1;
	   			$quiz->questions = $questions0.',0';
	   			$quiz->sumgrades = $count;
	   			$quiz->grade = 100;
	   			$quiz->grades = $question_new;
	   			$quiz->timemodified = time();
	   			$quiz->timelimit = 40;
	   			$quiz->password = '';
	   			$quiz->popup = 0;
	   			$quiz->delay1 = 0;
	   			$quiz->delay2 = 0;
	   			$quiz->review = 71573634;

				$verify_quiz = get_record_select('quiz', "course=$cid AND name='$quiz->name'", 'id');
				if($verify_quiz) {
					$idquiz = $verify_quiz->id;
                    $quiz_attempts = get_records_select('quiz_attempts', "quiz=$idquiz", '', 'id, uniqueid');
                    foreach($quiz_attempts as $quiz_attempt) {
                    	delete_attempt($quiz_attempt->uniqueid);
                    }
					delete_records("quiz_attempts", "quiz", $idquiz);
					$quiz->id = $idquiz;
					update_record('quiz', $quiz);
				} else {
		   			$idquiz = insert_record('quiz', $quiz);
				}

	   			$sections = get_records_select('course_sections', "course=$cid AND visible=1", 'id', 'id, sequence');
	   			$sections = end($sections);
	   			$section = $sections->id;
	   			$modules->course = $cid;
	   			$modules->module = 10;
	   			$modules->instance = $idquiz;
	   			$modules->section = $section;
	   			$modules->added = time();
	   			$modules->score = 0;
	   			$modules->indent = 0;
	   			$modules->visible = 1;
	   			$modules->visibleold = 1;
	   			$modules->groupmode = 2;
	   			$modules->groupingid = 0;
	   			$modules->groupmembersonly = 0;

				$verify = get_record_select('course_modules', "course=$cid AND module=10 AND instance=$idquiz", 'id');
				if($verify) {
		   			$idmodule = $verify->id;
		   			$modules->id = $idmodule;
					update_record('course_modules', $modules);
				} else {
		   			$idmodule = insert_record('course_modules', $modules);
					$quiz_context = get_context_instance(CONTEXT_MODULE, $idmodule);
					unset($section);
					$section->id = $sections->id;
					$section->sequence = $sections->sequence.','.$idmodule;
					$idsection = update_record('course_sections', $section);
				}

				$categoryid = get_record_select('grade_categories', "courseid=$cid", 'id');
				$categoryid = $categoryid->id;
				$grade_items->courseid = $cid;
				$grade_items->categoryid = $categoryid;
				$grade_items->itemname = $quiz->name;
				$grade_items->itemtype = 'mod';
				$grade_items->itemmodule = 'quiz';
				$grade_items->iteminstance = $idquiz;
				$grade_items->itemnumber = 0;
				$grade_items->idnumber = '';
				$grade_items->gradetype = 1;
				$grade_items->grademax = 100.0000;
				$grade_items->grademin = 0.000;
				$grade_items->gradepass = 0.000;
				$grade_items->multfactor = 1.000;
				$grade_items->plusfactor = 0.000;
				$grade_items->aggregationcoef = 0.000;
				$sortorder = get_record_sql("SELECT max(sortorder) as max FROM {$CFG->prefix}grade_items WHERE courseid=$cid");
				$sortorder = $sortorder->max + 1;
				$grade_items->sortorder = $sortorder;
				$grade_items->display = 0;
				$grade_items->timecreated = time();
				$grade_items->timemodified = time();
				$grade_items->hidden = 0;
				$grade_items->locked = 0;
				$grade_items->locktime = 0;
				$grade_items->needsupdate = 0;

				$verify = get_record_select('grade_items', "courseid=$cid AND categoryid=$categoryid AND iteminstance=$idquiz", 'id');
				if($verify) {
					$idgrade = $verify->id;
					$grade_items->id = $idgrade;
					update_record('grade_items', $grade_items);
				} else {
					$idgrade = insert_record('grade_items', $grade_items);
				}


				$grade_items_history->action = 1;
				$grade_items_history->oldid = $idgrade;
				$grade_items_history->timemodified = time();
				$grade_items_history->courseid = $cid;
				$grade_items_history->categoryid = $categoryid;
				$grade_items_history->itemname = $quiz->name;
				$grade_items_history->itemtype = 'mod';
				$grade_items_history->itemmodule = 'quiz';
				$grade_items_history->iteminstance = $idquiz;
				$grade_items_history->itemnumber = 0;
				$grade_items_history->gradetype = 1;
				$grade_items_history->grademax = 100.0000;
				$grade_items_history->grademin = 0.000;
				$grade_items_history->gradepass = 0.000;
				$grade_items_history->multfactor = 1.000;
				$grade_items_history->plusfactor = 0.000;
				$grade_items_history->aggregationcoef = 0.000;
				$grade_items_history->sortorder = $sortorder;
				$grade_items_history->display = 0;
				$grade_items_history->timecreated = time();
				$grade_items_history->timemodified = time();
				$grade_items_history->hidden = 0;
				$grade_items_history->locked = 0;
				$grade_items_history->locktime = 0;
				$grade_items_history->needsupdate = 0;

				$verify = get_record_select('grade_items_history', "courseid=$cid AND categoryid=$categoryid AND iteminstance=$idquiz", 'id');
				if($verify) {
					$idgrade_history = $verify->id;
					$grade_items_history->id = $idgrade_history;
					update_record('grade_items', $idgrade_history);
     				delete_records_select ('quiz_question_instances', "quiz=$idquiz");

				} else {
					$idgrade_history = insert_record('grade_items_history', $grade_items_history);
					rebuild_course_cache($cid);
				}

	            $c = count($questions000);
				for($i=0;$i<$c;$i++) {
					$quiz_question_instances->quiz = $idquiz;
					$quiz_question_instances->question = $questions000[$i];
					$quiz_question_instances->grade = 1;
					insert_record('quiz_question_instances', $quiz_question_instances);

				}
                $sql = "SELECT s.id, s.groupid, g.name, s.roomid, s.datestart, s.timestart, s.timeend
                FROM  mdl_bsu_schedule s
                INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
                WHERE s.id=$id";
                if ($auditories =  mysql_query($sql))
                $auditorie=mysql_fetch_assoc($auditories);

			  //  $sql = get_record_select('dean_schedule', "id=$id", 'groupno, edworkid');       
              //  $auditories = get_records_select('bsu_schedule', "edworkid=$sql->edworkid AND groupno='$sql->groupno'", '', 'id, roomid, datestart, timestart, timeend');

				if($auditories) {
					for($i=0; $i<3; $i++) {
						$idauditorium[$i] = 0;
					}
					$i = 0;
					$groups = $auditorie['name'];
				//	foreach($auditories as $auditorie) {
						$aud0 = $auditorie['roomid'];
						$data_1 = '';
						$data_1 = date("Y-m-d",$auditorie['datestart']);
						$timeopen = 0;
						$timeclose = 0;

						if($data_1!='') {
							$data_t = explode('-', $data_1);
							$timeopen = mktime(date("H",$auditorie['timestart']), date("i",$auditorie['timestart']), 0, $data_t[1], $data_t[2], $data_t[0]);
							$timeclose = mktime(date("H",$auditorie['timeend']), date("i",$auditorie['timeend']), 0, $data_t[1], $data_t[2], $data_t[0]);
						}
                        
                        $aud1 = $aud2 = 0;
						$quiz_time->id = $idquiz;
						$quiz_time->timeopen = $timeopen;
						$quiz_time->timeclose = $timeclose;
						if($i == 0) update_record('quiz', $quiz_time);

						$quiz_schedule->idquiz = $idquiz;
						$quiz_schedule->timeopen = $timeopen;
						$quiz_schedule->timeclose = $timeclose;
						$quiz_schedule->onid = $i;
						$quiz_schedule->visib = 1;
						$quiz_schedule->timemodified = time();
						$quiz_schedule->groups = $groups;
						if($aud1 == $aud0) $aud1 = 0;
						if($aud2 == $aud1 || $aud2 == $aud0) $aud2 = 0;

						$quiz_schedule->idauditorium = $aud0;
						$quiz_schedule->idauditorium1 = 0;
						$quiz_schedule->idauditorium2 = 0;

						$sql = get_record_select('quiz_schedule', "idquiz=$idquiz AND onid=$i", 'id');
						if($sql) {
							$quiz_schedule->id = $auditorie['id'];
							update_record('quiz_schedule', $quiz_schedule);
						} else {
							insert_record('quiz_schedule', $quiz_schedule);
						}
						$i++;
			//		}
					enrol_academygroup_to_course($group, $cid);
                    $sqls = get_records_select('quiz_attempts', "quiz=$idquiz", '', 'id, uniqueid, sumgrades, timemodified, userid');
                    if($sqls) {
                        foreach($sqls as $sql) {
                            delete_quiz_attempt($sql->uniqueid);
                            }
                        }
					redirect("index.php",  get_string('create_test_complete', 'block_dean'));
					print_footer();
					exit();
				}
			}
		}
	}

	include('tabs.php');

	if($createlink == 1) {
		if($admin_is) $headdepartment_is = $subid;
        print_simple_box_start();
        $strsql = "SELECT d.id, d.disciplinenameid, rd.id, rd.name
        FROM mdl_bsu_discipline d
        INNER JOIN mdl_bsu_ref_disciplinename rd on rd.id=d.disciplinenameid
        WHERE d.id=$id"; 
        $discip =  mysql_query($strsql);
        $discipid = mysql_fetch_assoc($discip);
		//$name = get_record_select('bsu_ref_disciplinename', "id=$id", 'name');
		echo '<center><table>';
		print_row(get_string('discipline', 'block_dean').':     ',  $discipid['name']);
		echo '</table></center>';
        
        include('page3.htm');

		print_footer();
		exit();
	}

	if($currenttab == 4) {
		$table = create_statistic();
        print_table($table);
		echo'<form name="excel" id="savexcel" method="post" action="index.php">';
		echo'<center><input name="saveexcelstatistics" type="submit" value="'. get_string('saveexcel', 'block_dean').'">
			<input type="hidden" name="ct" value="'.$currenttab.'" />
			</center>
			</form>';
		print_footer();
		exit();
	}

	if($id == 0) {
		if($action == 'alld' && ($dean_is || $admin_is)) {
			echo '<center><table>';
			$scriptname = "index.php?action=alld&amp;ct=$currenttab&amp;subid=";
			listbox_subdepartment2($scriptname, $subid, $dean_is);
			echo '</table></center>';


			if($subid != 0) {
				$table = table_examschedule('', $action, $fid, $did, $sid, $gid, $subid);
				echo'<br>';
				print_table($table);
				print_footer();
				exit();
			}
		}
           
		if($currenttab == 2) {
			print_header_examshedule($fid, $did, $sid, $gid, $kid);
		}

		if($teacher_is && $action != 'all') {

			$table = table_examschedule('', $action, $fid, $did, $sid, $gid, $kid);
			print_table($table);
			print_footer();
			exit();
		}

		if($fid == 0 && $did == 0 && $sid == 0 && $gid == 0 && $kid == 0 && $ed == 0) {
			print_footer();
			exit();
		}
		$table = table_examschedule('', $action, $fid, $did, $sid, $gid, $kid, $notworktest);


		echo'<form name="excel" id="savexcel" method="post" action="index.php">';
		if($admin_is && $action == 'all' && $notworktest == 0) echo'<center><input name="notworktest" type="submit" value="'. get_string('notworktest', 'block_cdoservice').'"></center><br>';
		if($admin_is && $action == 'all' && $notworktest == 1) echo'<center><input name="visalldisc" type="submit" value="'. get_string('visalldisc', 'block_cdoservice').'"></center><br>';
		print_table($table);
		echo'<center><input name="saveexcel" type="submit" value="'. get_string('saveexcel', 'block_dean').'">
			<input type="hidden" name="id" value="'.$id.'" />
			<input type="hidden" name="fid" value="'.$fid.'" />
			<input type="hidden" name="did" value="'.$did.'" />
			<input type="hidden" name="cid" value="'.$sid.'" />
			<input type="hidden" name="gid" value="'.$gid.'" />
			<input type="hidden" name="kid" value="'.$kid.'" />
			<input type="hidden" name="cid" value="'.$cid.'" />
			<input type="hidden" name="action" value="'.$action.'" />
			<input type="hidden" name="ed" value="'.$ed.'" />
			</center>
			</form>';
	} else {
	   
       $courseids = get_records_select('dean_course_discipline', "disciplineid=$did AND specialityid=$sid AND departmentid<>0 AND departmentid=$subid", '', 'courseid');
		if($courseids) {
			foreach($courseids as $courseid) {
	   			$ids[] = $courseid->courseid;
			}
           
			$ids = implode(',', $ids);
            $category = '161, 64, 108, 81, 155, 111, 114, 140, 149, 159, 160, 77, 103';
	        $courses = get_records_select('course', "id in ($ids)", '', 'id, fullname');
	        if($courses) {
	            $strsql = "SELECT s.id, s.groupid, s.disciplinenameid, s.disciplineid, g.name
                FROM  mdl_bsu_schedule s
                INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
                WHERE s.id=$id"; 
                if ($agroups =  mysql_query($strsql))
                $b=mysql_fetch_assoc($agroups);
				//$b = get_record_select('dean_schedule', "id=$id", 'groupno, disciplinenameid');
				$a->group = $b['name'];
                $strsql = "SELECT id, name
                FROM mdl_bsu_ref_disciplinename 
                WHERE id=".$b['disciplinenameid'];  
                $bs =  mysql_query($strsql);
                $b = mysql_fetch_assoc($bs);
				//$b = get_record_select('bsu_ref_disciplinename', "id=$b->disciplinenameid", 'name');
				$a->disc = $b['name'];
				print_heading(get_string('select_discipline', 'block_dean', $a), 'center', 4);
		        $list[] = get_string('select');
		        foreach($courses as $course) {
		        	$list[$course->id] = $course->fullname;
		        }
				echo'<center>';
				popup_form("index.php?id=$id&amp;uid=$uid&amp;did=$did&amp;sid=$sid&amp;ed=$ed&amp;ct=$currenttab&amp;subid=$subid&amp;cid=", $list, "course", $cid, "", "", "", false);
				echo'</center>';
				$disabled = 'disabled';
				if($allselectquestion < 20 || $allselectquestion > 40) $a->allselectquestion = "<font color=#FF0000>$allselectquestion</font>";
				if($allselectquestion > 19 && $allselectquestion < 41) {
					$a->allselectquestion = "<font color=#00AA00>$allselectquestion</font>";
					$disabled = '';
				}

				if($allselectquestion != 0)	print_heading(get_string('allquestioncount', 'block_dean', $a->allselectquestion), 'center', 4);

				if($cid != 0) {
					$coursecontexts = get_context_instance(CONTEXT_COURSE, $cid);
					$question_categories = get_records_select('question_categories', "contextid=$coursecontexts->id", 'name', 'id, name');
					if($question_categories) {
						echo"<form name='form' method='post' action='index.php'>
							<center><input name='save' type='submit' ".$disabled." value='". get_string('savechanges')."'>
							<input name='count' type='submit' value='". get_string('count', 'block_dean')."'>".
							'<input type="hidden" name="subid" value="'.$subid.'" />';

						foreach($question_categories as $data) {
							$i=1;
							$table->head = array(get_string('npp', 'block_cdoservice'), get_string('type', 'block_dean'), get_string('count1', 'block_dean'), get_string('count2', 'block_dean'));
							$table->align = array("center", "left", "center", "center");
							$table->width = '90%';
							$table->size = array ('5%', '45%', '25%', '25%');

							$questions = get_records_select('question', "category=$data->id AND qtype <> 'random'", '', 'id, qtype');
							if($questions) {
								print_heading(get_string('nametopics').': '.$data->name.' ('.count($questions).')', 'center', 4);
								$dop = '';
								if(strpos($data->name, 'T1'))	{
									$dop = '(один правильный ответ)';
								}
								if(strpos($data->name, 'T2'))	{
									$dop = '(несколько правильных ответов)';
								}

								for($j=0;$j<count($question_types);$j++) {
									$qtype = get_records_select('question', "category=$data->id AND qtype='$question_types[$j]'", '', 'id, qtype');
									if($qtype) {
										$options = "<select name='$question_types[$j][$data->id]'>";
										for($k=0; $k<=count($qtype); $k++){
											$selected = '';
											$s = $question_types[$j].'['.$data->id.']';
											if(isset($answers_count[$s]))
												if($k == $answers_count[$s]) $selected = ' selected="selected" ';
											$options.="<option $selected>$k</option>";
										}
										$options.= '</select>';

										if($dop == '') {
											$table->data[] = array($i, get_string($question_types[$j], 'quiz'), count($qtype), $options);
										} else {
											$table->data[] = array($i, get_string($question_types[$j], 'quiz').'<br>'.$dop, count($qtype), $options);
										}
										$i++;
									}
								}

								print_table($table);
								unset($table);
							}
						}
						if($allselectquestion != 0)	print_heading(get_string('allquestioncount', 'block_dean', $a->allselectquestion), 'center', 4);
						print_heading(get_string('count_questions', 'block_dean'), 'center', 4);

						echo'<center><input name="save" type="submit" '.$disabled.' value="'. get_string('savechanges').'">
							<input name="count" type="submit" value="'. get_string('count', 'block_dean').'">
							<input type="hidden" name="id" value="'.$id.'" />
							<input type="hidden" name="uid" value="'.$uid.'" />
							<input type="hidden" name="sid" value="'.$sid.'" />
							<input type="hidden" name="did" value="'.$did.'" />
							<input type="hidden" name="cid" value="'.$cid.'" /></center>
						</form>';
					}
				}
			} else {
				print_heading(get_string('noexistdiscipline', 'block_dean'), 'center', 4);
				redirect("index.php?action=$action&amp;fid=$fid&amp;sid=$sid&amp;gid=$gid&amp;ed=$ed", '', 300);
			}
		} else {
			print_heading(get_string('noexistdiscipline', 'block_dean'), 'center', 4);
			redirect("index.php?action=$action&amp;fid=$fid&amp;sid=$sid&amp;gid=$gid&amp;ed=$ed", '', 300);
		}
	}
    mysql_close($link);
	print_footer();
?>