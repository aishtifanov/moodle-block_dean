<?php // $Id: jodb.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/course/lib.php');


   // require_login();

    if (!$site = get_site())  {
        redirect('index.php');
    }

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


	// $courseid = required_param('c', PARAM_INT);			// Course id
	//$numsection = required_param('s', PARAM_INT);

	$courseid = 1738;
  	$numsection = 12632;
    $pathtopicture = 'img src = "http://pegas.bsu.edu.ru/file.php/' . $courseid . '/testimgs/';
    // $pathtopicture = 'img src = "http://cdoc06/pegas/file.php/' . $courseid . '/testimgs/';

    $strmonit = 'Test convertation ... (HTML)';

    print_header("$site->shortname: $strmonit", $site->fullname, $strmonit);

    print_heading($strmonit);

	$path_dir = $CFG->dataroot . '/1/h/';

	// rebuild_course_cache($courseid); 	exit();
	$dirlist = list_directories_and_files (trim($path_dir));
    // print_r($dirlist);
	foreach ($dirlist as $file1) {

	// foreach ($string_files as $file1 => $numsection)	{

	   $fn = $path_dir . $file1;

		$path_parts = pathinfo($fn);
		$path_parts_name = $path_parts["basename"];
		$path_parts_ext  = $path_parts["extension"];
		$path_parts_shortname = substr ($path_parts_name, 0, strlen($path_parts_name)- 4);

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


		$pos = strpos($line, '<body');

		if ($pos === false) {
		    error("Строка '<body' не найдена в строке '$line'");
		}

	    $line = substr ( $line, $pos);

	    $line = str_replace("<p ", "<z><p ", $line);

		$line = str_replace("\n", " ", $line);
		$line = str_replace("\r", "", $line);

	    $clearline = strip_tags ($line , '<z><u><b><i><img><hr/><sub><sup><br/>');

	    $clearline = str_replace('<b>+</b>', '+', $clearline);

	    $clearline = str_replace('<i>+</i>', '+', $clearline);

	    $clearline = str_replace('<u>+</u>', '+', $clearline);

		$clearline = str_replace('src="', $pathtopicture, $clearline);

		$lines = explode ('<z>', $clearline);

		// print_r($lines);

        // foreach ($lines as $key => $line)	{

   	    $nameoftest = $lines[2];

        print_heading($nameoftest);
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
            // print_r($cat);
            error("Could not insert the new quiz category '$newcategory'", "category.php?id={$courseid}");
        } else {
            notify(get_string('categoryadded', 'quiz', $nameoftest), 'green');
        }


        $num = count ($lines);
        $i = 10;
        $numansw = 0;
        $strtest = '';
	    $strquizquestions = '';
        $quizquestionid = array ();

        while ($i < $num)	{

        	$shablon = substr(ltrim($lines[$i]), 0, 6);

        	if ($shablon == '##time')	{

				$shablon = substr(ltrim($lines[$i+1]), 0, 6);
				if ($shablon == '##them')  {
					$i++;
					continue;
				}

        	    // echo "<br><br>// Вопрос: $numansw<br>";
        	    // $strtest .= "\n\n// Вопрос: $numansw\n";
        	    $numansw++;
        	    // echo $lines[$i+1] . '  {';
        	    // $strtest .= trim($lines[$i+1]) . '  {';
	       	 	$textquestion = trim($lines[$i+1]);
        	    $i += 2;

	     	 	if(!empty($textquestion))	{

		       	 	$question = NULL;
		       	 	$question->category  = $cat->id;
		       	 	if (strlen($textquestion) > 240)	{
			        	$question->name = substr($textquestion, 0, 240) . ' ...';
			        } else {
			        	$question->name = $textquestion;
			        }

	                $question->name = addslashes($question->name);

			        $question->questiontext = addslashes($textquestion);
			        $question->questiontextformat = 0;
			        $question->image = "";
			        $question->defaultgrade = 1;
			        $question->parent = 0;
			        $question->qtype = 3;
			        $question->length = 1;
			        $question->stamp = make_unique_id_code();  // Set the unique code (not to be changed)
			        $question->version = 1;
			        // print_r($question); echo '<hr><hr>';

			        if (!$question->id = insert_record("quiz_questions", $question)) {
			             print_r($question);
			             error("Could not insert new question!!!!!");
			        }

			        $quizquestionid[] = $question->id;

	            	$strquizquestions .=  $question->id . ',';
	         	}

        	    $flag = true;
				$strquizanswers = '';
        	    $arrqanswer = array();     $numtrue  = 0;
        	    $arrqanswerbin = array();  $numfalse = 0;
        	    while ($flag && $i<$num)	{
        	        $qanswer = trim($lines[$i]);

 	      	 	    if (!empty($qanswer))	{

	                    $clearline = strip_tags($qanswer);
	    				$shablon = substr($clearline, 0, 6);
	    				$sym = substr($clearline, 0, 1);

	    				if ($shablon == '##them')	{
							$i++;
							$flag = false;
							break;
						}

	                    if ($sym == '+')	{
 		                   	 $sym4 = substr($qanswer, 0, 4);
                    		 if ($sym4 == '<b>+')	{
	                    	 	$arrqanswer[] = substr($qanswer, 5);
 		                   	 } else {
	                     	 	$arrqanswer[] = substr($qanswer, 1);
			                 }
	   	 	        	    $arrqanswerbin[] = true;
 	  	 	        	    $numtrue++;
                   		 } else  if ($shablon != '&nbsp;')  {
	                   	 	$arrqanswer[] = $qanswer;
	   	 	        	    $arrqanswerbin[] = false;
	   	 	        	    $numfalse++;
	                    }
	                }
                    $i++;
        	    }

                // print_r($arrqanswer); echo '<hr>';
                // print_r($arrqanswerbin); echo '<hr>';

                if ($numtrue == 1)	{
                    $is_single = 1;
	                foreach ($arrqanswer as $key => $textanswer)	{		      	        $answer = NULL;
	                    $answer->question = $question->id;
	                    $answer->answer   = addslashes($textanswer);
	                    $answer->feedback = '';

	                	if ($arrqanswerbin[$key])	{		                    $answer->fraction = 1;
	                	} else {
		                    $answer->fraction = 0;
	                	}

	                    if (!$answer->id = insert_record("quiz_answers", $answer)) {
			                print_r($answer);
	                        error ("Could not insert quiz answer!!!!! ");
	                    }

                    	$strquizanswers .= $answer->id . ',';
	                }
	            } else {

                    if ($numtrue == 0 ||  $numfalse  == 0)	{
	                    print_r($arrqanswer); echo '<hr>';
	                	print_r($arrqanswerbin); echo '<hr>';
	                }

                    $is_single = 0;
	            	$vestrue  = get_ves($numtrue);
	            	$vesfalse = get_ves($numfalse);

	                foreach ($arrqanswer as $key => $textanswer)	{

		      	        $answer = NULL;
	                    $answer->question = $question->id;
	                    $answer->answer   = addslashes($textanswer);
	                    $answer->feedback = '';

	                	if ($arrqanswerbin[$key])	{
	                		// $strtest .= "\n~%$vestrue%".$question;
		                    $answer->fraction = $vestrue;
	                	} else {
		                	// $strtest .= "\n~%$vesfalse%". $question;
		                    $answer->fraction = '-'.$vesfalse;
	                	}

	                    if (!$answer->id = insert_record("quiz_answers", $answer)) {
			                print_r($answer);
	                        error ("Could not insert quiz answer!!!!! ");
	                    }

                    	$strquizanswers .= $answer->id . ',';
	                }
	            }

        	    // echo '<br>}';
        	    // $strtest .= "\n}";

        	     $quizmultichoice = NULL;
				 $quizmultichoice->question = $question->id;
				 $quizmultichoice->layout = 0;
				 $quizmultichoice->answers =  substr($strquizanswers, 0, strlen($strquizanswers) - 	1);
				 $quizmultichoice->single = $is_single;

	             if (!$quizmultichoice->id = insert_record("quiz_multichoice", $quizmultichoice)) {
	                print_r($quizmultichoice);
                    error ("Could not insert quiz multichoice!!!!!!");
	             }


        	} else {
        		$i++;
        	}
        }

        // exit();
 	    // echo $strtest;
	    /* if (!$handle = fopen($path_dir . $path_parts_shortname . '_gift.txt', 'w')) {
	         echo "Не могу открыть файл ($filename)"; exit;
  		}
	    // if (fwrite($handle, $clearline) === FALSE) {
		if (fwrite($handle, $strtest) === FALSE) {
 	       echo "Не могу произвести запись в файл ($filename)";  exit;
   		}
	    fclose($handle);
	    */

	    $strquizquestions .= '0';

		$quiz = NULL;
		$quiz->course = $courseid;
		$quiz->name = $nameoftest;
		$quiz->intro = 	$nameoftest;
		$quiz->timeopen = 1234567890;
		$quiz->timeclose = 1260423300;
		$quiz->optionflags = 0;
	    $quiz->penaltyscheme = 1;
		$quiz->attempts = 99;
		$quiz->review = 62415;
	    $quiz->shufflequestions  = 1;
	  	$quiz->shuffleanswers = 1;
	    $quiz->questions = $strquizquestions;
		$quiz->sumgrades = $numansw;
		$quiz->grade = $numansw;
		$quiz->timelimit = 0; // $numansw*2;
		$quiz->password = '';
		$quiz->subnet = '';

  		if ($numansw > 20) {
  			$quiz->questionsperpage = 20;
  		}

  		if ($numansw > 400) {
  			$quiz->questionsperpage = 40;
  		}

/*
  		if ($quiz->timelimit > 80) {
			$quiz->timelimit = 0;
  		}
*/
        if (!$quiz->id = insert_record('quiz', $quiz))	{
	        print_r($quiz);
        	error("Could not insert the new quiz category '$nameoftest'", "category.php?id={$courseid}");
        } else {
            notify('QUIZ added !!! => '.  $nameoftest, 'green');
        }


        foreach ($quizquestionid as $questionid)	{
	        $instance = NULL;
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

		$mod = NULL;
	    $mod->course = $courseid;
	    $mod->module = 10;
	    $mod->instance = $quiz->id;
	    $mod->section = $numsection;
	    $mod->added = time();
	    $mod->score = 0;
	    $mod->indent = 0;
	    $mod->visible = 1;
	    $mod->groupmode = 2;

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
	}

    print_footer($site);


// This function return the names of all directories and files under a give directory
// Not recursive
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


function get_ves($num)
{    if ($num == 0)	return 0;

   	$ves  = round(1.0 / $num, 5);

   	if ($ves > 0.33 && $ves < 0.34)	{
   		$ves = '0.33333';
   	} else 	if ($ves > 0.16 && $ves < 0.17)	{
   		$ves = '0.16666';
   	} else 	if ($ves > 0.14 && $ves < 0.15)	{
   		$ves = '0.14286';
   	}

   	return $ves;
}

/*

SELECT cm.id as coursemodule, m.*,cw.section,cm.visible as visible,cm.groupmode
FROM mdl_course_modules cm, mdl_course_sections cw, mdl_modules md, mdl_quiz m
WHERE cm.course = '1711' AND cm.instance = m.id AND cm.section = cw.id AND md.name = 'quiz' AND md.id = cm.module

*/

?>