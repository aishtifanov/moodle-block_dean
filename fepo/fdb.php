<?php // $Id: fdb.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

/* Инструкция по использованию:
1. Открыть таблицу mdl_course_sections и выбрать секции, относящиеся к нужному курсу
    SELECT * FROM `moodle`.`mdl_course_sections` where course=1718

2. Определить номер (id) секции, в которую надо занести тесты

3. В файле files.xls заменить номер секции для курса в столбце D.

4. Скопировать строки, относящиеся к курсу, в столбце G в файл fepo_tests_files.php

5. Скопировать файл fepo_tests_files.php на сервер.

6. На сервере запустить скрипт с параметром, указывающим номер кусра. Например,
   http://pegas.bsu.edu.ru/blocks/dean/fdb.php?c=1713

*/

    require_once("../../../config.php");
    require_once($CFG->dirroot.'/course/lib.php');

    include('fepo_tests_files.php');

   // require_login();

    if (!$site = get_site())  {
        redirect('index.php');
    }

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


	$courseid = required_param('c', PARAM_INT);			// Course id
	//$numsection = required_param('s', PARAM_INT);
	//$courseid = 1700;

	// $numsection = 2814;
    $pathtopicture = 'img src = "http://pegas.bsu.edu.ru/file.php/' . $courseid . '/testimgs';

    $strmonit = 'Test convertation ...';

    print_header("$site->shortname: $strmonit", $site->fullname, $strmonit);

    print_heading($strmonit);

	$path_dir = $CFG->dataroot . '/1/t/';

	// rebuild_course_cache($courseid); 	exit();
	// $dirlist = list_directories_and_files (trim($path_dir));
    // print_r($dirlist);
	// foreach ($dirlist as $file1) {

	foreach ($string_files as $file1 => $numsection)	{

	   $fn = $path_dir . $file1;

		$path_parts = pathinfo($fn);
		$path_parts_name = $path_parts["basename"];
		$path_parts_ext = $path_parts["extension"];

		if ($path_parts_ext != "htm") {
		    continue;
		}

	   if (!file_exists($fn))  {
	       notify ("Файл $fn не найден.");
	       // break;
	       continue;
	   }
	   // Получить содержимое файла в виде массива
	   // $pages = file ($fn);
	   $line = file_get_contents ($fn);

	   if ($line == FALSE)  {
	       notify ("Файл $fn!");
		   break;
	   }

	    $nameoftest = get_string($path_parts_name, 'fepo_tests_new');

        notify($path_parts_name, 'black');

        $cat = NULL;
        $cat->parent = 0;
        $cat->name = $nameoftest;
        $cat->info = '-';
        $cat->publish = 0;
        $cat->course = $courseid;
        $cat->sortorder = 999;
        $cat->stamp = make_unique_id_code();
        if (!$cat->id = insert_record("quiz_categories", $cat)) {
            print_r($cat);
            error("Could not insert the new quiz category '$newcategory'", "category.php?id={$courseid}");
        } else {
            // notify(get_string("categoryadded", "quiz", $nameoftest), 'green');
        }

       // echo $line;
       // file_put_contents ( $path_dir . '1.txt',  $line);

       $clearline1 = strip_tags ($line , '<img><hr/><sub><sup>');

       $clearline = str_ireplace('img src="pic', $pathtopicture, $clearline1);

       // $clearline = addslashes($clearline);

       // $clearline = str_replace('IMG SRC="pic', 'img src = "http://cdoc06/pegas/file/1/pic', $clearline1);

       // echo $clearline;

       $voposes = explode ('Vopros', $clearline);

       // print_r($voposes);
       $v=0;
	   $strquizquestions = '';
       $quizquestionid = array ();

       foreach ($voposes as $vopos)	{

         if ($v == 0) {
            $v++;
            continue;
         }

         $qanswers = explode ('<hr/>', $vopos);

       	 $cv = 0;

       	 foreach($qanswers as $qans)	{
	      	 if (isset($qans)) {
	      	   $trimqans = trim($qans);
      	 		if (!empty($trimqans))	{
		      	 	$cv++;
		      	}
		     }
	     }

		 // print_r($qanswers); echo '<hr>'; echo $cv; continue;


       	 // foreach ($answers as $ans)	{

       	 if (isset($qanswers[0]))	{

	       	 $qanswers0 = trim($qanswers[0]);
       	 	 if(!empty($qanswers0))	{

	       	 	$question = NULL;
	       	 	$question->category  = $cat->id;
	       	 	if (strlen($qanswers0) > 240)	{
		        	$question->name = substr($qanswers0, 0, 240) . ' ...';
		        } else {
		        	$question->name = $qanswers0;
		        }

                $question->name = addslashes($question->name);

		        $question->questiontext = addslashes($qanswers0);
		        $question->questiontextformat = 0;
		        $question->image = "";
		        $question->defaultgrade = 1;
		        $question->parent = 0;
		        $question->qtype = 3;
		        $question->length = 1;
		        $question->stamp = make_unique_id_code();  // Set the unique code (not to be changed)
		        $question->version = 1;
		        if (!$question->id = insert_record("quiz_questions", $question)) {
		             print_r($question);
		             error("Could not insert new question!!!!!");
		        }
		        $quizquestionid[] = $question->id;

	            $strquizquestions .=  $question->id . ',';

				$strquizanswers = '';
		      	 if (isset($qanswers[1]))	{

		      	 	 $qanswers1 = trim($qanswers[1]);
		      	 	 if (!empty($qanswers1))	{
		      	        $answer=NULL;
	                    $answer->question = $question->id;
	                    $answer->answer   = addslashes($qanswers1);
	                    $answer->fraction = 1;
	                    $answer->feedback = '';
	                    if (!$answer->id = insert_record("quiz_answers", $answer)) {
			                print_r($answer);
	                        error ("Could not insert quiz answer!!!!! ");
	                    }
	                    $strquizanswers .= $answer->id . ',';
	                    for ($i = 2; $i < $cv; $i++)	{

					      	 if (isset($qanswers[$i]))	{
					      	 	$qanswersi = trim($qanswers[$i]);

					      	 	if (!empty($qanswersi))  {
							      	$answer=NULL;
				                    $answer->question = $question->id;
				                    $answer->answer   = addslashes($qanswersi);
				                    $answer->fraction = 0;
				                    $answer->feedback = '';

				                    if (!$answer->id = insert_record("quiz_answers", $answer)) {
						                print_r($answer);
				                        error ("Could not insert quiz answer!!!!!!!! ");
				                    }
				                    $strquizanswers .= $answer->id . ',';
				                }
					      	 }
				      	}
				     }
				 }

				 $quizmultichoice->question = $question->id;
				 $quizmultichoice->layout = 0;
				 $quizmultichoice->answers =  substr($strquizanswers, 0, strlen($strquizanswers) - 	1);
				 $quizmultichoice->single = 1;
	             if (!$quizmultichoice->id = insert_record("quiz_multichoice", $quizmultichoice)) {
	                print_r($quizmultichoice);
                    error ("Could not insert quiz multichoice!!!!!!");
	             }
	         }
         }

   	   }
	   $strquizquestions .= '0';

		$quiz->course = $courseid;
		$quiz->name = $nameoftest;
		$quiz->intro = 	get_string($path_parts_name, 'fepo_tests');
		$quiz->timeopen = 1221111300;
		$quiz->timeclose = 1260423300;
		$quiz->optionflags = 0;
	    $quiz->penaltyscheme = 1;
		$quiz->attempts = 6;
  		$quiz->review = 62415; // 61440
  		// $quiz->questionsperpage = 0;
	    $quiz->shufflequestions  = 1;
	  	$quiz->shuffleanswers = 1;
	    $quiz->questions = $strquizquestions;
		$quiz->sumgrades = 10;
		$quiz->grade = 10;
		$quiz->timelimit = 0;
		$quiz->password = '';
		$quiz->subnet = '';

        if (!$quiz->id = insert_record('quiz', $quiz))	{
	        print_r($quiz);
        	error("Could not insert the new quiz category '$nameoftest'", "category.php?id={$courseid}");
        } else {
            notify('QUIZ added !!! => '.  $nameoftest, 'green');
        }


        foreach ($quizquestionid as $questionid)	{
	        unset($instance);
	        $instance->quiz = $quiz->id;
	        $instance->question = $questionid;
	        $instance->grade = 1;
	        if (!insert_record("quiz_question_instances", $instance))	{
                print_r($instance);
	        	error ("Could not insert quiz question instances!!!!!");
	        } else {
	        	// notify('Quiz question instances !!! => '.  $questionid, 'green');
	        }
	    }

	    $mod->course = $courseid;
	    $mod->module = 10;
	    $mod->instance = $quiz->id;
	    $mod->section = $numsection;
	    $mod->added = time();
	    $mod->score = 0;
	    $mod->indent = 0;
	    $mod->visible = 1;
	    $mod->groupmode = 0;

        if (!$mod->id = insert_record('course_modules', $mod))	{
        	print_r($mod);
        	error("Could not insert the new module in course!");
        } else {
           // notify('New module in course added !!! => ' . $mod->id, 'green');
        }


 		if ($section = get_record("course_sections", "id", $numsection))   {
	        $section->sequence = trim($section->sequence);
            $newsequence = $section->sequence. ',' . $mod->id;
	        if(set_field("course_sections", "sequence", $newsequence, "id", $section->id))	{
	        	// notify('Course sections updated !!!', 'green');
	        } else {
		        print_r($section);
	        	error("Could not course sections updated!");
	        }
		} else {
        	error("Course sections not found!");
	    }

		rebuild_course_cache($courseid);

	    flush();

		unset($quizmultichoice);
		unset($quizquestionid);
        unset($mod);
	    unset($qanswers);
	    unset($voposes);
	    unset($quiz);
	    unset($line);
	}

    print_footer($site);

    //This function return the names of all directories and files under a give directory
    //Not recursive
function list_directories_and_files ($rootdir)
{

        $results = "";

        $dir = opendir($rootdir);
        while ($file=readdir($dir)) {
            if ($file=="." || $file=="..") {
                continue;
            }
            $results[$file] = $file;
        }
        closedir($dir);
        return $results;
}

/*

SELECT cm.id as coursemodule, m.*,cw.section,cm.visible as visible,cm.groupmode
FROM mdl_course_modules cm, mdl_course_sections cw, mdl_modules md, mdl_quiz m
WHERE cm.course = '1711' AND cm.instance = m.id AND cm.section = cw.id AND md.name = 'quiz' AND md.id = cm.module

*/

?>
