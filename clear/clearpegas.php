<?php // $Id: clearpegas.php,v 1.0 2011/12/13 11:28:00 zagorodnyuk Exp $
    require_once("../../../config.php");
    require_once('../lib.php');
    require_once('../lib_quiz.php');
    require_once('lib_clear.php');

	require_login();
	$currenttab  = optional_param('ct', 1);			// id current tab
	$i1 = optional_param('i1', -1);				// start quiz rec

	if (!$site = get_site()) {
	    redirect("$CFG->wwwroot/$CFG->admin/index.php");
	}
	$admin_is = isadmin();
 	if(!$admin_is) {
 		redirect("$CFG->wwwroot/$CFG->admin/index.php");
 	}

    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

 	$min = 0;
    $frm = data_submitted();
	$ball = $min;
	$cid = 0;
	$date = time()-100000000;
	$exception_text = get_string('exception_text', 'block_dean');

	if(isset($frm->course)) {
		$ball = $frm->ball;
		$date = mktime(0, 0, 0, $frm->month, $frm->day, $frm->year);
		$cid = $frm->course;
		$min = $frm->ball;
		$exception_text = $frm->exception_text;
	}

	$strtitle = get_string('clearpegas', 'block_dean');
	$breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';
	$breadcrumbs .= " -> " . $strtitle;
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if(isset($frm->clearquestionstates) || isset($frm->verifystates)) {
    	$count_clearquestionstates_start = $frm->count_clearquestionstates_start;
    	$count_clearquestionstates_end = $frm->count_clearquestionstates_end;
    	$count_give_rec = $count_clearquestionstates_end - $count_clearquestionstates_start + 1;
	} else {
		$count_clearquestionstates_start = 1;
		$count_clearquestionstates_end = 1;
	}
	if(isset($frm->clear)) {
		$users = get_records_select('user', "(deleted=1) or (firstname='' and lastname='')", '', 'id');
		if($users)
			foreach($users as $data) {
				$user777[] = $data->id;
			}
			usercleaner_ondata($user777);
//		}
		echo '<center>';
/*
		for($i=$frm->startid;$i<$frm->endid;$i++) {
        	$user = get_record_select('user', "id=$i", 'id, deleted');
        	if(!$user) $user777[] = $i;
        	if($user)
				if($user->deleted == 1) $user777[] = $i;
        	if(isset($user777)) {
        		usercleaner_ondata($user777);
        		print $user777[0].'<br>';
        	}
        	unset($user777);
		}
*/
	}

    include('tabs.php');

	switch($currenttab) {
		case 1:
			$sqls = get_records_select('course', '', 'fullname', 'id, fullname');
			$courseid[0] = get_string('selectcourse', 'block_dean');
			foreach($sqls as $sql) {
				$courseid[$sql->id] = substr($sql->fullname, 0, 200).' ...';
			}
			$popup = choose_from_menu($courseid, 'course', $cid, '', '', '', true);

			$count_clearquestionstates = 'Укажите промежуток ID, c ';

			echo"<form name='printservice' method='post' action='clearpegas.php'><table align='center'>";

			print_row(get_string('course', 'block_dean').' ', $popup);
			print_row(get_string('ballclear', 'block_dean').' ', "<input name='ball' type='text' value='$ball' size='5'>");
			print_row(get_string('dataclear', 'block_dean').' ', print_date_selector("day", "month", "year", $date, true));
			print_row(get_string('exception', 'block_dean').' ', "<input name='exception_text' type='text' value='$exception_text' size='108'>");
			print_row('<input name="attention" type="checkbox" value="ON">', get_string('attention', 'block_dean'));
			echo'</table>';
			echo'<br><center><input type="submit" name="action" value="'.get_string('clearpegas', 'block_dean').'">';
			echo'<input type="submit" name="clearoneattempt" value="'.get_string('clearoneattempt', 'block_dean').'">';

			echo'<br><br><br>'.$count_clearquestionstates.'<input type="text" name="count_clearquestionstates_start" value="'.$count_clearquestionstates_start.'" size="10">';
			echo ' по <input type="text" name="count_clearquestionstates_end" value="'.$count_clearquestionstates_end.'" size="10"><br><br>';

			echo'<input type="submit" name="clearquestionstates" value="Очистка всех попыток до указанной даты в указанном промежутке">';
			echo'<input type="submit" name="verifystates" value="Проверка несуществующих попыток и их удаление">';
			echo'<input type="hidden" name="ct" value="'.$currenttab.'">';
			echo'</form><br>';

            if(isset($frm->attention)) {
				if(isset($frm->action) || isset($frm->clearoneattempt) || isset($frm->clearquestionstates) || isset($frm->verifystates)) {
					if(isset($frm->verifystates)) {
						$quizs = get_records_select('question_states', '', '', 'distinct(attempt)', $count_clearquestionstates_start, $count_give_rec);
						$i = 1;
						$uniqueid_array[] = 0;
						foreach($quizs as $data) {
							$question_attempts = get_record_select('question_attempts', "id=$data->attempt", 'id');
							$quiz_attempts = get_record_select('quiz_attempts', "uniqueid=$data->attempt", 'id');
							if(!$question_attempts || !$quiz_attempts) {
								$uniqueid_array[] = $data->attempt;
								print " <font color='#FF0000'>deleted attemptid=$data->attempt</font><br>";
							}
							if($i%10000 == 0) echo $i.'<br>';
							$i++;
						}
						$uniqueid = implode(',', $uniqueid_array);
//						delete_records_select('question_attempts', "id IN ($uniqueid)");
						delete_records_select('question_states', "attempt IN ($uniqueid)");
						delete_records_select('question_sessions', "attemptid IN ($uniqueid)");
//						delete_records_select('quiz_attempts', "uniqueid IN ($uniqueid)");
						$count = count($uniqueid_array) - 1;
						echo'<center><br><h3>Всего удалено: '.$count.'<br>COMPLETED';
					} else {
						$datas = " AND timemodified<=$date ";
					    $course = '';
					    if($cid != 0) $course = " course=$cid AND ";
						$i2 = 1000;
						$exam = explode(',', $exception_text);
						if(count($exam) > 0) {
							$examwhere = '';
							for($i=0;$i<count($exam);$i++) {
								$str = trim($exam[$i]);
								$examwhere.= " AND name NOT LIKE '%$str%' ";
							}
							$examwhere = substr($examwhere, 4, strlen($examwhere));
						}

						if($i1 != -1) {
							$quizs = get_records_select('quiz', "$course $examwhere", '', 'id, sumgrades', $i1, $i2);
						} else {
							if(isset($frm->clearquestionstates)) {
								$quizs = get_records_select('quiz', "$course $examwhere", '', 'id', $count_clearquestionstates_start, $count_give_rec);
								$aaa[] = 0;
								foreach($quizs as $data) {
									$aaa[] = $data->id;
								}
								$quizid = implode(', ', $aaa);

//								$quizs = get_records_select('quiz_attempts', "timemodified<$date AND quiz IN ($quizid)", '', 'id, uniqueid', $count_clearquestionstates_start, $count_give_rec);
								$quizs = get_records_select('quiz_attempts', "timemodified<$date AND quiz IN ($quizid)", '', 'id, uniqueid');
//print "timemodified<$date AND quiz IN ($quizid)<br>";
								unset($aaa);
								$aaa[] = 0;
								foreach($quizs as $data) {
									$aaa[] = $data->uniqueid;
								}
//							    SELECT uniqueid FROM mdl_quiz_attempts where timemodified<1322719200 limit 1,10000
								$uniqueid = implode(', ', $aaa);
print "attempt IN ($uniqueid)<br>";
//select * from mdl_quiz_attempts where quiz in (SELECT id FROM mdl_quiz  WHERE name NOT LIKE '%экзаменационный%')
//								delete_records_select('question_attempts', "id IN ($uniqueid)");
								delete_records_select('question_states', "attempt IN ($uniqueid)");
								delete_records_select('question_sessions', "attemptid IN ($uniqueid)");
//								delete_records_select('quiz_attempts', "uniqueid IN ($uniqueid)");

								print_footer();
								exit();
							}
							if(isset($frm->verifystates)) {
								$quizs = get_records_select('quiz', "$course $examwhere", '', 'id, sumgrades', $count_clearquestionstates_start, $count_give_rec);
							} else {
								$quizs = get_records_select('quiz', "$course $examwhere", '', 'id, sumgrades');
							}
						}

						if($quizs) {
							$all = 0;
							if(isset($frm->clearoneattempt)) {
								foreach($quizs as $data) {
									$i1 = 1;
									echo 'ID теста: '.$data->id.'<br>';
									$sqls = get_records_select('quiz_attempts', "quiz=$data->id $datas", '', 'id, uniqueid, sumgrades, timemodified, userid');
									if($sqls) {
										foreach($sqls as $dat) {
											if($rec = get_record_select('quiz_attempts', "quiz=$data->id $datas AND userid=$dat->userid", "max(sumgrades) as sumgrades")) {
												$max = 0;
												$max = intval($rec->sumgrades*1000);
												if(strlen($max) > 7) $max = substr($max, -8);
												$recs = get_records_select('quiz_attempts', "quiz=$data->id $datas AND userid=$dat->userid", '', "id, uniqueid, sumgrades");
												if(count($recs)>1) {
													$maxid = 0;
													foreach($recs as $rec0) {
														$cur = intval($rec0->sumgrades*1000);
														if($max != $cur) {
															$a[] = $rec0->id;
														} else {
															$maxid = $rec0->id;
														}
													}
													$s = '';
													if(isset($a)) $s = implode(',', $a);
													if($s == '') $s = '0';
													unset($a);
													$recs = get_records_select('quiz_attempts', "quiz=$data->id AND id <> $maxid $datas AND userid=$dat->userid", '', 'id, uniqueid, sumgrades, timemodified, userid');
													if($recs) {
														foreach($recs as $rec) {
															delete_quiz_attempt($rec->uniqueid);
															$time = date("d.m.Y H:i:s", $rec->timemodified);
															echo '<font color="#0000FF">'.$i1.' DELETED attempt='.$rec->uniqueid.' Время окончания тестирования='.$time.'</font><br>';
															$all++;
															$i1++;
														}
													}
												}
											}
										}
									}
								}
							} else {
								foreach($quizs as $data) {
									echo 'ID теста: '.$data->id.'<br>';
									$sqls = get_records_select('quiz_attempts', "quiz=$data->id $datas", '', 'id, uniqueid, sumgrades, timemodified');
									if($sqls) {
										$i1 = 1;
										foreach($sqls as $sql) {
											$sumgrades = 100*$sql->sumgrades/$data->sumgrades;
											if($sumgrades < $min) {
	//											delete_quiz_attempt($sql->uniqueid);
												$all++;
												$time = date("d.m.Y H:i:s", $sql->timemodified);
												echo '<font color="#0000FF">'.$i1.' DELETED attempt='.$sql->uniqueid.' Время окончания тестирования='.$time.'</font><br>';
												$i1++;
											}
										}
									}
								}
							}
							echo'<center><br><h3>Всего удалено: '.$all.'<br>COMPLETED';
						} else {
							echo'<center><br><h3>Нечего удалять<br>COMPLETED';
						}
					}
				}

			} else {
				if(isset($frm->action) || isset($frm->clearoneattempt) || isset($frm->clearquestionstates) || isset($frm->verifystates)) print_string('attention0', 'block_dean');
			}
		break;
		case 2:
			$startid = $endid = 0;
			if(isset($frm->startid)) {
				$startid = $frm->startid;
				$endid = $frm->endid;
			}
			$user = get_record_select('user', "(deleted=1) or (firstname='' and lastname='')", 'count(id) as count');
			if($user->count>0) {
				print_heading(get_string('count_delete_user', 'block_dean').': '.$user->count, 'center', 4);
				echo"<center><form name='clearuserinfo' method='post' action='clearpegas.php'>";
				print"start <input name='startid' type='text' value='$startid' size='5'>";
				print" end <input name='endid' type='text' value='$endid' size='5'><br>";
				echo'<br><center><input type="submit" name="clear" value="'.get_string('clearpegas', 'block_dean').'">';
				echo'<input type="hidden" name="ct" value="'.$currenttab.'">';
				echo'</form><br>';
			}
		break;

	}
	print_footer();
?>
