<?php
	require_once("config.php");
	require_login();
	$i1 = optional_param('i1', -1);				// start quiz rec
	$min = optional_param('min', 35);			// minimal ball for delete
	$cid = optional_param('id', 0);				// course id
	$data = optional_param('data', '');			// пример 25.12.2011 дата, раньше которой удалять все результаты тестов
	if(!isadmin()) exit();

	if($data != '') {
		$data = explode('.', $data);
		$data = mktime(0,0,0, $data[1], $data[0], $data[2]);
		$datas = " AND timemodified<=$data ";
	}

    $course = '';
    if($cid != 0) $course = " course=$cid AND ";
	$i2 = 1000;
	if($i1 != -1) {
		$quizs = get_records_select('quiz', "$course name NOT LIKE '%экзаменационный%' AND name NOT LIKE '%итоговый%'", '', 'id, sumgrades', $i1, $i2);
	} else {
		$quizs = get_records_select('quiz', "$course name NOT LIKE '%экзаменационный%' AND name NOT LIKE '%итоговый%'", '', 'id, sumgrades');
	}
	if($quizs) {
		$all = 1;
		foreach($quizs as $data) {
			echo 'ID теста: '.$data->id.'<br>';
			$sqls = get_records_select('quiz_attempts', "quiz=$data->id $datas", '', 'id, uniqueid, sumgrades');
			if($sqls) {
				$i1 = 1;
				foreach($sqls as $sql) {
					$sumgrades = 100*$sql->sumgrades/$data->sumgrades;
					if($sumgrades < $min) {
						delete_records_select('question_attempts', "id=$sql->uniqueid");
						delete_records_select('question_states', "attempt=$sql->uniqueid");
						delete_records_select('question_sessions', "attemptid=$sql->uniqueid");
						delete_records_select('quiz_attempts', "uniqueid=$sql->uniqueid");
						echo '<font color="#00FF00">'.$i1.' DELETED attempt='.$sql->uniqueid.'</font><br>';
						$i1++;
						$all++;
					}
				}
			}
		}
		echo'<center><h3>Всего удалено: '.$all.'<br>COMPLETED';
	}

/**/
/*
	$sumgrades = 0;
	if($i1 != -1) {
		$sqls = get_records_select('quiz_attempts', "sumgrades=$sumgrades", '', 'id, uniqueid', $i1, $i2);
	} else {
		$sqls = get_records_select('quiz_attempts', "sumgrades=$sumgrades", '', 'id, uniqueid');
	}

	$quizs = get_records_select('quiz', '', '', 'id, sumgrades', $i1, $i2);

	if($sqls) {
		$i = 1;
  		foreach($sqls as $sql) {
			delete_records_select('question_attempts', "id=$sql->uniqueid");
			delete_records_select('question_states', "attempt=$sql->uniqueid");
			delete_records_select('question_sessions', "attemptid=$sql->uniqueid");
			delete_records_select('quiz_attempts', "uniqueid=$sql->uniqueid");
			echo $i1.' DELETED attempt='.$sql->uniqueid.'<br>';
			$i1++;
  		}
        echo'<h3>COMPLETED';
	}
*/
?>