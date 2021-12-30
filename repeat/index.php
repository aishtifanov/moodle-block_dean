<?php // $Id: form.php,v 1.0 2011/01/12 12:00:50 Zagorodnyuk Exp $
/*
print_r($_POST);
print_r($_);
exit();
/**/

    ini_set('memory_limit', -1);
    ini_set('upload_max_filesize', -1);

    if($_GET['cq'] == 2 || $_POST['cq'] == 2) {
        $DB_HOSTNAME_MY = 'bsu-dekanat.bsu.edu.ru';
        $DB_USERNAME_MY = 'dean';
        $DB_PASSWORD_MY = 'big#psKT';

        $DB_DATABASE_MY = 'dean';
        $DB_PREFIX_MY = 'mdl_';

        $db_my = mysql_connect($DB_HOSTNAME_MY, $DB_USERNAME_MY, $DB_PASSWORD_MY) or die("Could not connect: " . mysql_error());
        mysql_select_db($DB_DATABASE_MY, $db_my);
        mysql_set_charset("utf8");
        mysql_query("SET NAMES 'utf8'");

        $sql = "SELECT brg.name, brd.name FROM dean.mdl_bsu_ref_groups brg
                INNER JOIN dean.mdl_bsu_ref_department brd ON brg.departmentcode=brd.departmentcode
                WHERE brg.yearid=16";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_row($result)) {
            $department[$row[0]] = $row[1];
        }

        $sql = "SELECT brg.name, bt.specyal FROM dean.mdl_bsu_ref_groups brg
                INNER JOIN dean.mdl_bsu_plan_groups bpg ON brg.id=bpg.groupid
                INNER JOIN dean.mdl_bsu_plan bp ON bp.id=bpg.planid
                INNER JOIN dean.mdl_bsu_tsspecyal bt ON bp.specialityid=bt.idspecyal
                WHERE brg.yearid=16";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_row($result)) {
            $specyal[$row[0]] = $row[1];
        }

        $sql = "SELECT id, name FROM dean.mdl_bsu_ref_disciplinename";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_row($result)) {
            $courses[$row[0]] = $row[1];
        }

        $sql = "SELECT bcdwp.umkid, brd.id FROM mdl_bsu_connect_disc_with_pegas bcdwp
                INNER JOIN mdl_bsu_discipline bd ON bd.id=bcdwp.disciplineid
                INNER JOIN mdl_bsu_ref_disciplinename brd ON brd.id=bd.disciplinenameid";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_row($result)) {
            $connect_disc_with_pegas[$row[0]] = $row[1];
        }
    }

	require_once("../../../config.php");
	require_once("../lib.php");
	require_once($CFG->libdir.'/uploadlib.php');
	require_once($CFG->dirroot.'/mod/hotpot/db/update_to_v2.php');
	require_once($CFG->libdir.'/questionlib.php');


    $cq = optional_param('cq', 1);	     		// current tab
    $ct = optional_param('ct', 1);	     		// current tab
    $q  = optional_param('q', 0);	     		// current tab
    $fid  = optional_param('fid', 0);	     	// facultyid id

	$frm = data_submitted(); /// load up any submitted data

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    require_login();

	$admin_is = isadmin();
/*
	if (!$admin_is) {
        error(get_string('adminaccess'));
	}
/**/
    if (!empty($frm->excell)) {
        switch($ct) {
            case 1:
                $table = create_table1($department, $specyal, $fid);
                print_table_to_excel($table);
            break;
            case 2:
                $table = create_table3($department, $specyal, $fid);

                $file = 'report.xls';
                header("Content-type: application/vnd.ms-excel; charset=UTF-8");
                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment; filename=" . $file);
                echo $table;

            break;
            case 3:
//                $table = create_table3($department, $specyal, $fid, 1);
                break;
        }
        exit();
    }

    switch($cq) {
        case 1:
            if ($admin_is)
                switch ($ct) {
                    case 1:
                        $strform = 'Импорт тестов "Проверка знаний"';
                    break;
                    case 2:
                        $strform = 'Импорт тестов "Остаточные знания"';
                    break;
                    case 3:
                        $strform = 'Импорт тестов "1-го курса"';
                    break;
                }
        break;
        case 2:
            switch ($ct) {
                case 1:
                    $strform = 'Отчет по тестам "Проверка знаний"';
                break;
                case 2:
                    $strform = 'Отчет по тестам "Остаточные знания"';
                break;
                case 3:
//                    $strform = 'Отчет по тестам "Осень 2015"';
                break;
            }
        break;
    }

	print_header("$SITE->shortname: $strform ", $SITE->fullname, $strform);
	print_simple_box_start("center", "%100");

	if (!empty($frm) ) {
		$um = new upload_manager('userfile', false, false, null, false, 0);
		$f = 0;
		if ($um->preprocess_files()) {
			$filename = $um->files['userfile']['tmp_name'];
			$text = file($filename);
			echo "<center>";
			if($text == ''){
				error(get_string('errorfile', 'block_cdoadmin'), "$CFG->wwwroot/blocks/cdoadmin/loadtest.php");
			}

			$textlib = textlib_get_instance();
			$size = sizeof($text);

			for($i=0; $i < $size; $i++)  {
				$text[$i] = $textlib->convert($text[$i], 'win1251');
			}

            switch($ct) {
                case 1: $start_line = 7; break;
                case 2: $start_line = 7; break;
                case 3: $start_line = 7; break;
            }

            $data = new stdClass();
            for($i=0; $i < $size; $i++)  {
            	if($i>$start_line) {
	            	$text[$i] = $textlib->convert($text[$i], 'utf-8');
                    switch($ct) {
                        case 1:
                            list($data->speciality, $data->namediscipline, $data->namecourse, $data->courseid, $data->listgroups) = explode(';', $text[$i]);
                        break;
                        case 2:
                            list($data->speciality, $data->namediscipline, $data->namecourse, $data->courseid, $data->listgroups, $data->date, $data->timeopen, $data->timeclose) = explode(';', $text[$i]);
                        break;
                        case 3:
                            list($data->namecourse, $data->courseid, $data->listgroups, $data->date, $data->timeopen, $data->timeclose) = explode(';', $text[$i]);
                        break;
                    }

                    if(!empty($data->courseid)) {
						$groups = explode(',', $data->listgroups);
                        if(empty($groups)) $groups[] = $data->listgroups;
						foreach($groups as $key=>$value) {
                            if($ct == 2 || $ct == 3) {
                                $value = trim($value);

                                $to = explode(':', $data->timeopen);
                                $tc = explode(':', $data->timeclose);
                                $d  = explode('.', $data->date);

                                $timeopen  = mktime($to[0], $to[1], 0, $d[1], $d[0], $d[2]);
                                $timeclose = mktime($tc[0], $tc[1], 0, $d[1], $d[0], $d[2]);
                                if(strlen($value) < 8) $value = '0'.$value;
                                if(strlen($value) < 8) $value = '0'.$value;
                                if(strlen($value) < 8) $value = '0'.$value;
                                enrol_academygroup_to_course(trim($value), $data->courseid);
                                print "<font color='#00aa00'>На курс \"$data->namecourse\" подписана группа $value. ID: $data->courseid</font><br>";

                                if($ct != 3) {
                                    print "<font color='#00aa00'>В курсе \"$data->namecourse\" создан тест остаточные знания для группы $value. ID: $data->courseid</font><br>";
                                    create_quiz($data->courseid, 'Остаточные знания ('.trim($value).')', $timeopen, $timeclose, 30);
                                }
                            }
						}

                        if($ct == 1) {
                            create_quiz($data->courseid, 'Проверка знаний');
                        }
					}
				}
            }
		}
    }

    if (!$admin_is) $cq = 2;
    if ($admin_is) $toprow[] = new tabobject(1, "index.php?cq=1", 'Импорт');
    $toprow[] = new tabobject(2, "index.php?cq=2", 'Отчеты');
    $tabs = array($toprow);
    print_tabs($tabs, $cq, NULL, NULL);

    switch($cq) {
        case 1:
            unset($toprow);
            $toprow[] = new tabobject(1, "index.php?ct=1", 'Проверка знаний');
            $toprow[] = new tabobject(2, "index.php?ct=2", 'Остаточные знания');
            $toprow[] = new tabobject(3, "index.php?ct=3", 'Тестирование 1-го курса');
            $tabs = array($toprow);
            print_tabs($tabs, $ct, NULL, NULL);

            switch ($ct) {
                case 1:
                    print_heading('Выберите файл оформленный по шаблону заявки для тестирования "Проверка знаний"', 'center', 3);
                    echo "<table cellspacing='0' cellpadding='10' align='center' class='generaltable generalbox'><tr><td align=center>";
                    echo '<form method="post" enctype="multipart/form-data" action="index.php">' .
                        '<input type="hidden" name="sesskey" value="' . $USER->sesskey . '">' .
                        '<input type="file" name="userfile" size="30">' .
                        '<input type="hidden" name="ct" value="' . $ct . '">' .
                        '<p><input type="submit" name="load" value="' . get_string('upload', 'block_cdoadmin') . '">';
                    echo '</form></td></tr></table>';
                    break;
                case 2:
                    print_heading('Выберите файл оформленный по шаблону заявки для тестирования "Остаточные знания"', 'center', 3);
                    echo "<table cellspacing='0' cellpadding='10' align='center' class='generaltable generalbox'><tr><td align=center>";
                    echo '<form method="post" enctype="multipart/form-data" action="index.php">' .
                        '<input type="hidden" name="sesskey" value="' . $USER->sesskey . '">' .
                        '<input type="file" name="userfile" size="30">' .
                        '<input type="hidden" name="ct" value="' . $ct . '">' .
                        '<p><input type="submit" name="load" value="' . get_string('upload', 'block_cdoadmin') . '">';
                    echo '</form></td></tr></table>';
                    break;
                case 3:
                    print_heading('Выберите файл оформленный по шаблону заявки для тестирования "Тестирование 1-го курса"', 'center', 3);
                    echo "<table cellspacing='0' cellpadding='10' align='center' class='generaltable generalbox'><tr><td align=center>";
                    echo '<form method="post" enctype="multipart/form-data" action="index.php">' .
                        '<input type="hidden" name="sesskey" value="' . $USER->sesskey . '">' .
                        '<input type="file" name="userfile" size="30">' .
                        '<input type="hidden" name="ct" value="' . $ct . '">' .
                        '<p><input type="submit" name="load" value="' . get_string('upload', 'block_cdoadmin') . '">';
                    echo '</form></td></tr></table>';
                    break;

            }
        break;
        case 2:
            unset($toprow);
            $toprow[] = new tabobject(1, "index.php?ct=1&cq=2", 'Проверка знаний');
            $toprow[] = new tabobject(2, "index.php?ct=2&cq=2", 'Остаточные знания');
//            $toprow[] = new tabobject(3, "index.php?ct=3&cq=2", 'Осень 2015');
            $tabs = array($toprow);
            print_tabs($tabs, $ct, NULL, NULL);

            echo '<br><center>';
            listbox_faculty_repeat("index.php?ct=$ct&cq=$cq&fid=", $fid);
            echo '</center><br>';

            if(isset($frm->month_start)) {
                $date_start = mktime(0, 0, 0, $frm->month_start, $frm->day_start, $frm->year_start);
                $date_end = mktime(0, 0, 0, $frm->month_end, $frm->day_end, $frm->year_end);
            } else {
                $date_start = time();
                $date_end = time();
            }

            echo '<form name="excel" method="post" action="index.php"><center>';
            echo '<table><tr><td>С</td><td>';
            print_date_selector("day_start", "month_start", "year_start", $date_start);
            echo '</td><td>по</td><td>';
            print_date_selector("day_end", "month_end", "year_end", $date_end);
            echo '</td><td><input name="save" type="submit" value="Применить"></td></tr></table>
                 <input name="excell" type="submit" value="' . get_string('downloadexcel') . '" />
                 <input type="hidden" name="ct" value="' . $ct . '">
                 <input type="hidden" name="fid" value="' . $fid . '">
                 <input type="hidden" name="cq" value="' . $cq . '"></center>';

            switch($ct) {
                case 1:
                    $table = create_table1($department, $specyal, $fid);
                    print_table($table);
                break;
                case 2:
                    $table = create_table3($courses, $connect_disc_with_pegas, $fid);
                    echo '<div>'.$table.'</div>';
                break;
                case 3:
//                    $table = create_table3($courses, $connect_disc_with_pegas, $fid);
                break;
            }

            echo '</form><br>';
        break;
    }
//	print_simple_box_end();
    print_footer();

function verify_quiz($groupname, $courseid) {
    GLOBAL $CFG;

    $sql = "SELECT id FROM {$CFG->prefix}quiz WHERE course=$courseid AND (name LIKE '%$groupname%' AND name LIKE '%экзамен%')";

    $id=get_records_sql($sql);
    if($id) {
        return 1;
    } else {
        return 0;
    }
}

function create_quiz($cid, $quizname, $timeopen = 0, $timeclose = 0, $timelimit = 0, $count_question = 30) {
    GLOBAL $CFG, $USER;

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

    $error = 0;
    $count = 0;
    $questions0 = '';
    $coursecontexts = get_context_instance(CONTEXT_COURSE, $cid);

    $sql = "SELECT qc.id, qc.name FROM {$CFG->prefix}question_categories qc INNER JOIN {$CFG->prefix}question q ON qc.id=q.category WHERE qc.contextid=$coursecontexts->id";
    $question_categories = get_records_sql($sql);

    $j = 1;
    $all_count = $count_question;
    unset($sum);
    for ($i = 1; $i <= $all_count; $i++) {
        foreach ($question_categories as $data) {
            if (!isset($sum[$data->id])) $sum[$data->id] = 0;
            if ($j <= $all_count) $sum[$data->id] = $sum[$data->id] + 1;
            $j++;
        }
    }

    $questionsrandom = new stdClass();
    foreach ($question_categories as $data) {
        $questions = get_records_select('question', "category=$data->id AND qtype <> 'random'", '', 'id, qtype');
        if ($questions) {
            $count = $count + $sum[$data->id];
            $questions_random = get_record_select('question', "category=$data->id AND qtype = 'random'", 'count(id) as count');
            $questions_random = $questions_random->count;
            if ($sum[$data->id] > $questions_random) {
                $i = $sum[$data->id] - $questions_random;
                for ($j = 1; $j <= $i; $j++) {
                    $questionsrandom->category = $data->id;
                    $parent = get_record_sql("SELECT max(id) as max FROM {$CFG->prefix}question");
                    $parent = $parent->max + 1;
                    $questionsrandom->parent = $parent;
                    $random = get_string('random', 'quiz');
                    $questionsrandom->name = $random . ' (' . $data->name . ')';

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

        if (isset($sum[$data->id])) {
            if (!empty($sum[$data->id])) {
                $questions_randoms = get_records_select('question', "category=$data->id AND qtype = 'random'", '', 'id', '0', $sum[$data->id]);
                if ($questions_randoms) {
                    foreach ($questions_randoms as $questions_randoms0) {
                        $questions0[] = $questions_randoms0->id;
                    }
                }
            }
        }
    }

    for ($i = 0; $i < 100; $i++) {
        $old_index = rand(0, count($questions0) - 1);
        $new_index = rand(0, count($questions0) - 1);
        $old = $questions0[$old_index];
        $questions0[$old_index] = $questions0[$new_index];
        $questions0[$new_index] = $old;
    }

    for ($i = 0; $i < count($questions0); $i++) {
        $question_new[$questions0[$i]] = 1;
    }

    $questions000 = $questions0;

    $first = $questions0[0];
    $questions0 = implode(',', $questions0);

    if ($error == 0) {
        $quiz = new stdClass();
        $quiz->course = $cid;
        $quiz->name = $quizname;
        $quiz->intro = '';
        $quiz->timeopen = 0;
        $quiz->timaclose = 0;
        $quiz->attempts = 0;
        $quiz->grademethod = 1;
        $quiz->decimalpoints = 2;
        $quiz->questionsperpage = 0;
        $quiz->shufflequestions = 1;
        $quiz->shuffleanswers = 1;
        $quiz->questions = $questions0 . ',0';
        $quiz->sumgrades = $count;
        $quiz->grade = 100;
        $quiz->grades = $question_new;
        $quiz->timemodified = time();
        $quiz->timelimit = $timelimit * 60;
        $quiz->timeopen = $timeopen;
        $quiz->timeclose = $timeclose;
        $quiz->password = '';
        $quiz->popup = 0;
        $quiz->delay1 = 0;
        $quiz->delay2 = 0;
        $quiz->review = 71573634;

        $verify_quiz = get_record_select('quiz', "course=$cid AND name='$quiz->name'", 'id');
        if ($verify_quiz) {
            $idquiz = $verify_quiz->id;
            if ($quiz_attempts = get_records_select('quiz_attempts', "quiz=$idquiz", '', 'id, uniqueid'))
                foreach ($quiz_attempts as $quiz_attempt) {
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
        $modules = new stdClass();
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
        if ($verify) {
            $idmodule = $verify->id;
            $modules->id = $idmodule;
            update_record('course_modules', $modules);
        } else {
            $idmodule = insert_record('course_modules', $modules);
            $quiz_context = get_context_instance(CONTEXT_MODULE, $idmodule);
            unset($section);
            $section = new stdClass();
            $section->id = $sections->id;
            $section->sequence = $sections->sequence . ',' . $idmodule;
            $idsection = update_record('course_sections', $section);
        }

        $categoryid = get_record_select('grade_categories', "courseid=$cid", 'id');
        $categoryid = $categoryid->id;
        $grade_items = new stdClass();
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
        if ($verify) {
            $idgrade = $verify->id;
            $grade_items->id = $idgrade;
            update_record('grade_items', $grade_items);
        } else {
            $idgrade = insert_record('grade_items', $grade_items);
        }

        $grade_items_history = new stdClass();
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
        if ($verify) {
            $idgrade_history = $verify->id;
            $grade_items_history->id = $idgrade_history;
            update_record('grade_items', $idgrade_history);
            delete_records_select('quiz_question_instances', "quiz=$idquiz");
        } else {
            $idgrade_history = insert_record('grade_items_history', $grade_items_history);
            rebuild_course_cache($cid);
        }

        $c = count($questions000);
        $quiz_question_instances = new stdClass();
        for ($i = 0; $i < $c; $i++) {
            $quiz_question_instances->quiz = $idquiz;
            $quiz_question_instances->question = $questions000[$i];
            $quiz_question_instances->grade = 1;
            insert_record('quiz_question_instances', $quiz_question_instances);
        }
    }
}

function create_table1($department, $specyal, $fid) {

    switch($fid) {
        case 0: print_footer(); exit(); break;
        case 1:$fid = '';break;
        default:$fid = " AND bs.idfakultet=$fid ";
    }

    $sql = "SELECT academygroupid, count(userid) as cnt FROM mdl_dean_academygroups_members GROUP BY academygroupid";
    $count_user_in_group = get_records_sql_menu($sql);
    $sql = "SELECT qa.id, qa.userid, q.id as quizid, c.fullname, da.name, da.id as academygroupid, qa.sumgrades as qa_sumgrades, q.sumgrades as q_sumgrades
				FROM mdl_quiz_attempts qa
				INNER JOIN mdl_quiz q ON qa.quiz=q.id
				INNER JOIN mdl_dean_academygroups_members dam ON qa.userid=dam.userid
				INNER JOIN mdl_dean_academygroups da ON dam.academygroupid=da.id
				INNER JOIN mdl_course c ON c.id=q.course
				WHERE q.name='Проверка знаний' AND qa.timefinish>0 AND qa.attempt=1
				GROUP BY c.fullname, da.name
				ORDER BY q.id, da.name, qa.userid, qa.attempt";

    $attempts = get_records_sql($sql);
    $table = new stdClass();
    $table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
    $table->size = array('20%', '20%', '30%', '9%', '9%', '9%', '5%', '5%');
    $table->titles[] = 'Результаты';
    $table->titlesrows = array(20);
    $table->worksheetname = 'results';
    $table->downloadfilename = 'results';
    $table->columnwidth = array(10, 10, 10, 10, 10, 10, 10, 10);
    $table->head = array('Институт/факультет', 'Направление подготовки (специальность) (с кодом)', 'Название дисциплины в соответствие с учебным планом',
        '№ группы', 'Всего чел.', 'Прошли тест чел.', '% участия', 'Ср. балл %');

    foreach($attempts as $attempt) {
        $sql = "SELECT count(distinct(qa.userid)) as id FROM mdl_quiz_attempts qa
								   INNER JOIN mdl_dean_academygroups_members dam ON qa.userid=dam.userid
								   INNER JOIN mdl_dean_academygroups da ON dam.academygroupid=da.id
								   WHERE qa.quiz=$attempt->quizid AND da.name=$attempt->name";
        $count = count_records_sql($sql);
        $procs = round($count*100/$count_user_in_group[$attempt->academygroupid], 2);
        $sumgrades = $attempt->qa_sumgrades/$count;
        $sr_ball = round($sumgrades, 2);

        $sr_ball = round($sr_ball*100/30, 2);

        $table->data[] = array($department[$attempt->name], $specyal[$attempt->name], $attempt->fullname, $attempt->name, $count_user_in_group[$attempt->academygroupid], $count, $procs, $sr_ball);
    }
    return $table;
}
/*
function create_table2($department, $specyal) {

    $table = new stdClass();
    $table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
    $table->size = array('20%', '20%', '30%', '9%', '9%', '9%', '5%', '5%');
    $table->titles[] = 'Результаты';
    $table->titlesrows = array(20);
    $table->worksheetname = 'results';
    $table->downloadfilename = 'results';
    $table->columnwidth = array(10, 10, 10, 10, 10, 10, 10, 10);
    $table->head = array('Институт/<br>факультет',
                         'Направление<br>подготовки/<br>Специальность',
                         'Название дисциплины',
                         '№ группы',
                         'Фамилия И.О.',
                         'Оценка за<br>тестирование<br>на экзамене',
                         'Оценка за<br>тестировани<br>остаточных знаний',
                         'Разница оценок за<br>тестирование (Ост. - Экз)');

    $sql = "SELECT qa.id, q.name AS quizname, qa.userid, q.id as quizid, c.fullname, da.name, da.id as academygroupid,
                   qa.sumgrades as qa_sumgrades, q.sumgrades as q_sumgrades, u.firstname, u.lastname, c.id AS courseid
				FROM mdl_quiz_attempts qa
				INNER JOIN mdl_quiz q ON qa.quiz=q.id
				INNER JOIN mdl_dean_academygroups_members dam ON qa.userid=dam.userid
				INNER JOIN mdl_dean_academygroups da ON dam.academygroupid=da.id
				INNER JOIN mdl_course c ON c.id=q.course
				INNER JOIN mdl_user u ON u.id=qa.userid
				WHERE q.name like '%Остаточные знания (%' AND qa.timefinish>0 AND qa.attempt IN (1,2,3)

				ORDER BY q.id, qa.sumgrades DESC, q.name, da.name, qa.userid, qa.attempt";

//				GROUP BY c.fullname, da.name

//print "$sql<br>";

//    INNER JOIN mdl_log l ON l.course=c.id AND l.userid=u.id AND l.module='quiz'
//    print $sql.'<br>';
    $attempts = get_records_sql($sql);
//print_object($attempts);
    $courseid = array();
    foreach($attempts AS $attempt) {
        $courseid[] = $attempt->courseid;
        $userid[] = $attempt->userid;
    }

    $courseid = array_unique($courseid);
    $courseid = implode(',', $courseid);
    $userid = array_unique($userid);
    $userid = implode(',', $userid);

    $sql = "SELECT qa.id, q.name AS quizname, qa.userid, q.id as quizid, c.fullname, da.name, da.id as academygroupid,
                   qa.sumgrades as qa_sumgrades, q.sumgrades as q_sumgrades, u.firstname, u.lastname, c.id AS courseid
				FROM mdl_quiz_attempts qa
				INNER JOIN mdl_quiz q ON qa.quiz=q.id
				INNER JOIN mdl_dean_academygroups_members dam ON qa.userid=dam.userid
				INNER JOIN mdl_dean_academygroups da ON dam.academygroupid=da.id

				INNER JOIN mdl_course c ON c.id=q.course

				INNER JOIN mdl_user u ON u.id=qa.userid
                WHERE q.name like '%Экзаменационный тест (%' AND qa.timefinish>0 AND qa.attempt IN (1,2,3) AND c.id IN ($courseid) AND qa.userid IN ($userid)

				ORDER BY q.id, qa.sumgrades DESC, q.name, da.name, qa.userid, qa.attempt";

    $attempts_exam = get_records_sql($sql);

    $result = array();
    foreach($attempts_exam as $attempt) {
        $start = strpos($attempt->quizname, '(') + 1;
        $end =   strpos($attempt->quizname, ')');
        $groupname = substr($attempt->quizname, $start, $end - $start);

        $ball = round($attempt->qa_sumgrades*100/$attempt->q_sumgrades, 0);
        if($ball > 100) $ball = 100;

        if(!isset($result["$attempt->courseid~$groupname~$attempt->userid"]))
            $result["$attempt->courseid~$groupname~$attempt->userid"] = $ball;
    }

    foreach($attempts AS $attempt) {
        $start = strpos($attempt->quizname, '(') + 1;
        $end =   strpos($attempt->quizname, ')');
        $groupname = substr($attempt->quizname, $start, $end - $start);
        $ball = round($attempt->qa_sumgrades*100/$attempt->q_sumgrades, 0);

        if($ball > 100) $ball = 100;

        $razn = '';
        $exam = '';
        if(isset($result["$attempt->courseid~$groupname~$attempt->userid"])) {
            $razn = $result["$attempt->courseid~$groupname~$attempt->userid"] - $ball.'%';
            $exam = $result["$attempt->courseid~$groupname~$attempt->userid"].'%';
        }

        if(($department[$attempt->name] != '')&&(!isset($v["$attempt->courseid~$groupname~$attempt->userid"]))) {
            $table->data[] = array(
                $department[$attempt->name],
                $specyal[$attempt->name],
                $attempt->fullname,
                $attempt->name,
                $attempt->lastname . ' ' . $attempt->firstname,
                $exam,
                $ball . '%',
                $razn
            );
        }
        $v["$attempt->courseid~$groupname~$attempt->userid"] = 1;
    }

    return $table;
}
/**/
function create_table3($courses, $connect, $fid)
{
    $table = '<br><table style="text-align: center" border="1"><tr>
                <td style="text-align: center">Институт/<br>факультет</td>
                <td style="text-align: center">Направление<br>подготовки/<br>Специальность</td>
                <td style="text-align: center">Курс</td>
                <td style="text-align: center">Группа</td>
                <td style="text-align: center">Полное имя</td>
                <td style="text-align: center">Гражданство</td>
                <td style="text-align: center">Основа<br>обучения</td>
                <td style="text-align: center">Название дисциплины<br>в соответствии<br>с учебным планом</td>
                <td style="text-align: center">Название дисциплины<br>в СЭО "Пегас"</td>
                <td style="text-align: center">Результат<br>экзаменационного<br>тестирования</td>
                <td style="text-align: center">Результат<br>тестирования<br>остаточных знаний</td>
                <td style="text-align: center">Разница результатов<br>тестирования<br>(Ост. - Экз)</td>
                <td style="text-align: center">Оценка за тестировани<br>остаточных знаний<br>(по 5-ти балльной системе)</td>
                <td style="text-align: center">Оценка за экзамен</td>
                <td style="text-align: center">Разница оценок за<br>(Ост. - Экз)</td>
                </tr>';

    switch($fid) {
        case 0: print_footer(); exit(); break;
        case 1:$fid = '';break;
        default:$fid = " AND bs.idfakultet=$fid ";
    }

//    if($fid > 10) $fid = " AND bs.idfakultet=$fid "; else $fid = '';
    $sql = "
            SELECT qa.id, q.id AS quizidgrade, c.id AS courseid, bs.Otdelenie, brc.country AS KodCountry, bs.FakultetName, bs.Specyal, c.fullname AS course,
                   q.name, q.sumgrades, q.grade, qa.userid, qa.attempt, qa.sumgrades AS q_sumgrades,
                   concat(u.lastname, ' ', u.firstname) AS fio
            FROM mdl_quiz q
            INNER JOIN mdl_quiz_attempts qa ON qa.quiz=q.id
            INNER JOIN mdl_user u ON u.id=qa.userid
            INNER JOIN mdl_course c ON c.id=q.course
            INNER JOIN mdl_bsu_students bs ON u.username=bs.CodePhysPerson
            INNER JOIN mdl_bsu_ref_country brc ON brc.id=bs.KodCountry
            WHERE
                (q.name like '%Остаточные знания (%' OR q.name like '%Экзаменационный тест (%') AND
                q.timeopen>1420070400 AND
                qa.timestart>1420070400 AND
                bs.idKodPrith=1
                $fid
    ";

    $attempts = get_records_sql($sql);

    foreach($attempts AS $attempt) {
        $start = strpos($attempt->name, '(') + 1;
        $end =   strpos($attempt->name, ')');
        $groupname = substr($attempt->name, $start, $end - $start);

        if(!isset($exams["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"])) {
            $exams["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = 0;
            $ostat["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = 0;
        }

        if(mb_strpos($attempt->name, 'кзамен') > 0) {
            if($attempt->q_sumgrades > $exams["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"])
                $exams["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = $attempt->q_sumgrades;
                $grade_exams["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = $attempt->quizidgrade;
        } else {
            if($attempt->q_sumgrades > $ostat["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"]) {
                $ostat["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = $attempt->q_sumgrades;
                $grade_ostats["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = $attempt->quizidgrade;
            }
        }
        $dop["$attempt->course~$groupname~$attempt->userid~$attempt->fio~$attempt->FakultetName~$attempt->Specyal"] = "$attempt->Otdelenie~$attempt->KodCountry~$attempt->courseid";
        $quizidgrade[$attempt->quizidgrade] = "$attempt->sumgrades~$attempt->grade";
    }

    $y = 16;
    $i = 0;
    foreach($exams AS $key=>$value) {
        $temp = explode('~', $key);

        $t = explode('~', $quizidgrade[$grade_exams[$key]]);
        $sumgrades_exam = $t[0];
        $grade_exam = $t[1];

        $t = explode('~', $quizidgrade[$grade_ostats[$key]]);
        $sumgrades_ostat = $t[0];
        $grade_ostat = $t[1];

        $ball_exam_proc = round($grade_exam*$value/$sumgrades_exam, 2);
        $ball_osta_proc = round($grade_ostat*$ostat[$key]/$sumgrades_ostat, 2);
        if($ball_exam_proc>100) $ball_exam_proc = 100;
        if($ball_osta_proc>100) $ball_osta_proc = 100;

        $razn_proc = $ball_osta_proc - $ball_exam_proc;

        $d = explode('~', $dop[$key]);

        $discipline_plan_name = $courses[$connect[$d[2]]];
        if($discipline_plan_name == '') $discipline_plan_name = $d[2];

        $course = substr($temp[1], 4, 2);
        $course = $y - $course;

        $ball_exam = get_ball($ball_exam_proc);
        $ball_osta = get_ball($ball_osta_proc);
        $ball_razn = $ball_osta - $ball_exam;

        $table.= "<tr>
                    <td style='text-align: center'>$temp[4]</td>
                    <td style='text-align: center'>$temp[5]</td>
                    <td style='text-align: center'>$course</td>
                    <td style='text-align: center'>$temp[1]</td>
                    <td style='text-align: center'>$temp[3]</td>
                    <td style='text-align: center'>$d[1]</td>
                    <td style='text-align: center'>$d[0]</td>
                    <td style='text-align: center'>$discipline_plan_name</td>
                    <td style='text-align: center'>$temp[0]</td>
                    <td style='text-align: center'>$ball_exam_proc </td>
                    <td style='text-align: center'>$ball_osta_proc </td>
                    <td style='text-align: center'>$razn_proc </td>
                    <td style='text-align: center'>$ball_osta</td>
                    <td style='text-align: center'>$ball_exam</td>
                    <td style='text-align: center'>$ball_razn</td>
                    </tr>";
        $i++;
    }

    $table.= '</table>';

    return $table;
}

function get_ball($proc) {
    $ball = 2;

    if(($proc >= 49) && ($proc < 70)) $ball = 3;
    if(($proc >= 70) && ($proc < 90)) $ball = 4;
    if($proc >= 90) $ball = 5;

    return $ball;
}


function listbox_faculty_repeat($scriptname, $fid)
{
    global $CFG;

    $facultymenu = array();
    $facultymenu[0] = get_string('selectafaculty', 'block_dean').'...';
    $facultymenu[1] = 'Все факультеты и институты...';

    if($allfacs = get_records_sql("SELECT DISTINCT brd.id, brd.name FROM {$CFG->prefix}bsu_ref_department brd
                                   INNER JOIN {$CFG->prefix}bsu_students bs ON bs.idFakultet=brd.Id
                                   WHERE bs.idKodPrith=1
                                   ORDER BY brd.Id")) {
        foreach ($allfacs as $facultyI) 	{
            $facultymenu[$facultyI->id] =$facultyI->name;
        }
    }

    echo '<tr> <td>'.get_string('ffaculty', 'block_dean').': </td><td>';
    popup_form($scriptname, $facultymenu, 'switchfac', $fid, '', '', '', false);
    echo '</td></tr>';
    return 1;
}

?>