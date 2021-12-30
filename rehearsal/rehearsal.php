<?php //by zagorodnyuk 02.04.2014 11:48

    require_once("../../../config.php");
    require_once('../lib.php');
    require_once("../../../mod/quiz/editlib.php");
    require_once('../lib_quiz.php');
    require_once("../../../lib/questionlib.php");
    
	$cid = optional_param('cid', 0);	 		// Course id    


	$strtitle = get_string('examschedule', 'block_dean');
	$breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';
	$breadcrumbs .= " -> Репетиция";
	print_header("$SITE->shortname: Репетиция", $SITE->fullname, $breadcrumbs);

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

    $id = '1742,2887,2934,5869,333,2862,3227,3227,3233,3499,3508,3660,3685,4246,4565,4565,4822,4822,4852,5255,5383,5383,5401,5637,6018,6510,1455,1700,1742,2016,2486,2851,2852,2853,2854,2855,2856,2857,2860,2861,2863,2864,2869,2887,2907,2908,2934,2935,2962,3083,3189,3201,3202,3223,3224,3250,3270,3279,3280,3298,3300,3311,3343,3358,3365,3372,3376,3405,3443,3456,3459,3466,3492,3515,3645,3661,3671,3676,3682,3698,3739,3769,3771,3773,3775,3800,3811,3817,3840,3859,3871,3875,3879,3881,3897,3902,3952,3974,4007,4025,4044,4070,4180,4200,4461,4491,4492,4508,4517,4519,4520,4537,4543,4576,4581,4584,4823,4847,4875,5267,5279,5315,5366,5391,5418,5420,5425,5431,5436,5443,5445,5453,5484,5494,5506,5508,5513,5517,5570,5577,5593,5595,5687,5726,5876,5898,5905,5909,5959,5970,5972,6007,6010,6013,6017,6027,6028,607,6150,6282,6297,6354,6357,6387,6401,6404,6445,6460,6475,6637,6645';
    
    if($frm = data_submitted()) {
		if(isset($frm->save)) {
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
          
			$error = 0;
			$count = 0;
			$questions0 = '';
			$coursecontexts = get_context_instance(CONTEXT_COURSE, $cid);
			$question_categories = get_records_select('question_categories', "contextid=$coursecontexts->id", '', 'id, name');
			foreach($question_categories as $data) {
				for($j=0;$j<count($question_types);$j++) {
					$name_element = $question_types[$j];
					if(isset($frm->{$name_element}[$data->id])) {
						if(!isset($sum[$data->id])) $sum[$data->id] = 0;
						$sum[$data->id] = $sum[$data->id] + $frm->{$name_element}[$data->id];
					}
				}
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
							$questionsrandom->createdby = $USER->id;
							$questionsrandom->modifiedby = $USER->id;
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
			for($i=0;$i<100;$i++) {
				$old_index=rand(0, count($questions0)-1);
				$new_index=rand(0, count($questions0)-1);
				$old = $questions0[$old_index];
				$questions0[$old_index] = $questions0[$new_index];
				$questions0[$new_index] = $old;
			}

			for($i=0;$i<count($questions0);$i++) {
				$question_new[$questions0[$i]] = 1;
			}

			$questions000 = $questions0;

			$first = $questions0[0];
			$questions0 = implode(',', $questions0);

//			if($count > 40 || $count < 20) {
            if($count != 30) {			 
				print_heading('Вы выбрали <font color="#ff0000"><b>'.$count.'</b></font> вопроса(ов), количество выбранных вопросов должно быть 30.', 'center', 4);
				$error=1;
			}

			if($error == 0) {
				$ret = role_assign(4, $USER->id, 0, $coursecontexts->id);

                $strsql = "SELECT s.id, s.groupid, g.name
                FROM  mdl_bsu_schedule s
                INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
                WHERE s.id=$id";
//                if ($agroups =  mysql_query($strsql))
//                $agroup=mysql_fetch_assoc($agroups);
//                $group = $agroup['name'];
                
				$quiz->course = $cid;
//	   			$quiz->name = get_string('examination_test', 'block_dean').' ('.$group.')';
	   			$quiz->name = 'Репетиция';
	   			$quiz->intro ='';
	   			$quiz->timeopen = 1397534400;
	   			$quiz->timeclose = 1398801300;
	   			$quiz->attempts = 3;
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
	   			$quiz->timelimit = 30;
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
                FROM mdl_bsu_schedule s
                INNER JOIN mdl_bsu_ref_groups g ON g.id=s.groupid
                WHERE s.id=$id";
                if ($auditories =  mysql_query($sql))
                $auditorie=mysql_fetch_assoc($auditories);

                $sqls = get_records_select('quiz_attempts', "quiz=$idquiz", '', 'id, uniqueid, sumgrades, timemodified, userid');
                if($sqls) {
                    foreach($sqls as $sql) {
                        delete_quiz_attempt($sql->uniqueid);
                    }
                }

			  //  $sql = get_record_select('dean_schedule', "id=$id", 'groupno, edworkid');       
              //  $auditories = get_records_select('bsu_schedule', "edworkid=$sql->edworkid AND groupno='$sql->groupno'", '', 'id, roomid, datestart, timestart, timeend');
/*
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
//					enrol_academygroup_to_course($group, $cid);
                    $sqls = get_records_select('quiz_attempts', "quiz=$idquiz", '', 'id, uniqueid, sumgrades, timemodified, userid');
                    if($sqls) {
                        foreach($sqls as $sql) {
                            delete_quiz_attempt($sql->uniqueid);
                        }
                    }
					redirect("rehearsal.php",  get_string('create_test_complete', 'block_dean'));
					print_footer();
					exit();
				}
/**/                
			}
		}
    }

    $lists = get_records_sql_menu("SELECT id, fullname FROM {$CFG->prefix}course WHERE id IN ($id) ORDER BY fullname");
//print_object($list1);    
    $list[0] = 'Выберите курс...';
    foreach($lists as $key=>$value) {
        $list[$key] = $value;
    }
//    $list = array_merge($list2, $list1);
//print_object($list);        

    echo '<center><table>';
	print_row(get_string('course').': ',  popup_form('rehearsal.php?cid=', $list, 'switchcourse', $cid, '', '', '', true));
	echo '</table></center>';
    if($verify_quiz = get_record_select('quiz', "course=$cid AND name='Репетиция'", 'id')) {
        echo '<center><font color="#ff5500">В выбранном Вами курсе уже есть репетиционный тест.</font></center>';
    }


	if($cid != 0) {
		$coursecontexts = get_context_instance(CONTEXT_COURSE, $cid);

        $question_categories = get_records_sql_menu("SELECT id, id as id1 FROM {$CFG->prefix}question_categories WHERE contextid=$coursecontexts->id");
        $question_categories = implode(',', $question_categories);
        $count_question_in_course = get_field_sql("SELECT count(id) as id FROM {$CFG->prefix}question WHERE category IN ($question_categories) AND parent=0");
        $count_category_quiz_in_course = get_field_sql("SELECT count(distinct(category)) as id FROM {$CFG->prefix}question WHERE category IN ($question_categories) AND parent=0");
        if($count_question_in_course < 30) {
            echo '<br /><center><font color="#dd0000">Не хватает вопросов...</font></center>';
        }
        
        $c = round(30/$count_category_quiz_in_course);
//print "count_question_in_course = $count_question_in_course count_category_quiz_in_course=$count_category_quiz_in_course<br />";
        
		$question_categories = get_records_select('question_categories', "contextid=$coursecontexts->id", 'name', 'id, name');
		if($question_categories) {
			echo"<br /><form name='form' method='post' action='rehearsal.php'>
				<center><input name='save' type='submit' value='". get_string('savechanges')."'>";

			$sum_question = 0;
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
								if(isset($answers_count[$s])) {
									if($k == $answers_count[$s]) $selected = ' selected="selected" ';
                                } else {
                                    if($k == $c) {
                                        $selected = ' selected="selected" ';
                                        $sum_question = $sum_question + $k;
                                    }
//print "sum_question=$sum_question<br />";                                      
                                }
                                    
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
            print_heading('<font color="#55FF55">Сейчас выбрано: '.$sum_question.'</font>', 'center', 4);
			print_heading(get_string('count_questions', 'block_dean'), 'center', 4);

			echo'<center><input name="save" type="submit" value="'. get_string('savechanges').'">
				<input type="hidden" name="id" value="'.$id.'" />
				<input type="hidden" name="cid" value="'.$cid.'" /></center>
			</form>';
		}
	}

	print_footer();
?>