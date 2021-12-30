<?php // $Id: import_ct.php,v 1.4 2013/02/25 07:17:50 shtifanov Exp $

	require_once('../../config.php');
    require_once($CFG->libdir.'/uploadlib.php');
    require_once($CFG->dirroot.'/backup/restorelib.php');
    require_once($CFG->dirroot.'/mod/quiz/lib.php');
    require_once($CFG->libdir.'/questionlib.php');
    require_once($CFG->dirroot.'/question/format.php'); // Parent class
	require_once($CFG->dirroot.'/question/format/giftwithimg/format.php'); // Child class
	require_once($CFG->dirroot.'/question/editlib.php');
	include_once($CFG->libdir.'/pclzip/pclzip.lib.php');

    require_login();
    
	define("MAX_ZIP_SIZE", 16777216);
    define("MAX_SYMBOLS_LISTBOX", 120);
	
    $courseid = optional_param('courseid', 0, PARAM_INT);   // course id
    $quizid = optional_param('quizid', 0, PARAM_INT);   // quiz id
    $analys = optional_param('analys', '');   // quiz id
    $action = optional_param('action', '');      

    $acourses= array();
	$aquizs = array();
	$acategorys = array(); 
	// $acategoryshour = array();

    $csv_delimiter = ';';
    $coursesnew = 0;

	$strtitle = get_string('dean','block_dean');
	$strscript = get_string('import_ct', 'block_dean');   
	$strcourse = get_string('lcourse', 'block_dean');   //1
	$strquiz= get_string('lquiz', 'block_dean'); //2
	$strcategory = get_string('lcat', 'block_dean'); //3
	// $stranalys = get_string('importcoursewithanalys', 'block_dean');
	// print_r($USER);
	

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/dean/index.php", 'type' => 'misc');
	$navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
	$navigation = build_navigation($navlinks);
	print_header($SITE->shortname . ': '. $strscript, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

    if ($action == 'clear') {
        ignore_user_abort(false); 
        @set_time_limit(0);
        @ob_implicit_flush(true);
        @ob_end_flush();
    	@raise_memory_limit("256M");
     	if (function_exists('apache_child_terminate')) {
    	    @apache_child_terminate();
       	}    
        clear_questions_answers();
        notice("Очистка вопросов проведена успешно.", 'import_ct.php');        
    }

	$redirlink = $CFG->wwwroot. "/blocks/dean/index.php";
//	if (!empty($frm) ) {
	if ($rec = data_submitted())  {
		
	   if (!empty($_FILES['newfile']['name']))	{

			$dir = "1/ct/".$USER->username;
       		
       		$um = new upload_manager('newfile', true, false, 1, false, MAX_ZIP_SIZE);
       		// print_r($um);  echo '<hr>';
	        if ($um->process_file_uploads($dir))  {
		          $file = $um->get_new_filename();
        	      print_heading(get_string('uploadedfile'), 'center', 4);
        	      // echo $newfile_name;
        	      
        	      $fullname = $CFG->dataroot."/".$dir."/".basename($file);
        	      // echo $fullname;
	              // echo "<li>".get_string("unzippingbackup").'</li>';
            	  if (!$status = restore_unzip ($fullname)) {
                       notify("Error unzipping backup file. Invalid zip file.");
	              }
	              
	              $ctcsvname = $CFG->dataroot."/".$dir."/ct.csv";
	              $status = analys_ct_csv($ctcsvname);
	              if ($status == 0)	{
					    // print_r($acourses);	 echo '<hr>';
						// print_r($aquizs);	 echo '<hr>';
						// print_r($acategorys);  echo '<hr>';
						

                        if ($courseid == 0) {
    						$course_header = new object(); 
    						if (!$status = create_new_course_сt($acourses, $course_header))	{
    							print_r($course_header); echo '<hr>';
    							error('Ошибка при создании курса ЦТ. Курс не создан.', $redirlink); 
    						}
    						echo '<hr><br>';
    						notify("Курс ЦТ '$course_header->fullname' создан успешно.", 'green');
    						echo '<br><hr>';	
                            
                        } else {
                            if ($course_header = get_record('course', 'id', $courseid)) {
    						      echo '<hr><br>';
    						      notify("Дли импорта выбран курс '$course_header->fullname'.", 'green');
    						      echo '<br><hr>';
                            } else {
    						      echo '<hr><br>';
    						      notify("Произошла ошибка при выборе курса.");
    						      echo '<br><hr>';
                                  exit();
                            }      	
                        }

						// $course_header = get_record('course', 'id', 3251);
						
						
						
						foreach ($aquizs as $aquiz)	{
							if ($aquizid = create_quiz_in_course_сt($aquiz, $course_header))	{
								echo '<hr><br>';
								notify("Тест '$aquiz' в курсе ЦТ создан успешно", 'green');
								echo '<br><hr>';	
							}	else {
								print_r($aquiz); echo '<hr>';
								error("Ошибка при создании теста '$aquiz' в курсе ЦТ. Тест не создан.", $redirlink);	
							}	
						}
						

					    $coursecontext = get_context_instance(CONTEXT_COURSE, $course_header->id);
						$newcategory = question_make_default_categories(array($coursecontext));
        				// $form->category = $newcategory->id . ',1';
						// print_r($newcategory); echo '<hr>';
					    
					    $categoriescourse = get_record_select('question_categories', "contextid = $coursecontext->id AND parent=0", 'id, parent, name');
					    if (!$categoriescourse) {
						   	error("Категория тестов по умолчанию для курса не обнаружена!", $redirlink);
						}
						// print_r($categoriescourse); echo '<hr>';

						$acategoryzips = array();
						foreach ($aquizs as $number => $aquiz)	{
							foreach ($acategorys[$number] as $acategory)	{
								// $massiv = explode('.', $acategory);
								// $namecategory = mb_strtoupper($massiv[0]);
                                $namecategory = $acategory->name;
								
						        $cat = new object();
						        $cat->parent = $categoriescourse->id;
						        $cat->contextid = $coursecontext->id;
						        $cat->name = $namecategory;
						        $cat->info = '';
						        $cat->sortorder = 999;
						        $cat->stamp = make_unique_id_code();
						        if (!$newid = insert_record("question_categories", $cat)) {
						            error("Could not insert the new question category '$namecategory'");
						        } else {
									echo '<hr><br>';
									notify("Категория теста '$namecategory' в курсе ЦТ создана успешно", 'green');
									echo '<br><hr>';
									$acategoryzips[$newid] = trim($acategory->zip); 
						        }
						    }    
						}
						
						// print_r($acategoryzips); echo '<hr>';
						
						foreach ($acategoryzips as $qcategoryid => $zipfile)	{
							import_from_giftwithimage ($course_header->id, $qcategoryid, $zipfile);
						}	
	              }	
            }
	   }
	}   
    
	$coursemenu[0] = "Импорт в новый курс";
	$allcourses = get_records_sql ("SELECT id, fullname  FROM {$CFG->prefix}course ORDER BY fullname");
	if ($allcourses)  {
		foreach ($allcourses as $course1) 	{
		    $len = mb_strlen($course1->fullname, "UTF-8");
			if ($len > MAX_SYMBOLS_LISTBOX)  {
		    	$course1->fullname = mb_substr($course1->fullname,  0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
		    }
			$coursemenu[$course1->id] = $course1->fullname;
		}
	}    
   
	print_heading($strscript, 'center', 2);

    $CFG->maxbytes = MAX_ZIP_SIZE; 

    $struploadafile = get_string('zipimportct', 'block_dean');
    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

 	echo '<center>';
    echo '<form enctype="multipart/form-data" name=form method=post action=import_ct.php>';
	echo "<p>$struploadafile ($strmaxsize):</p>";
    print_string('course'); echo ': ';
    choose_from_menu ($coursemenu, 'courseid', $courseid, false);
    // echo 'ID курса (если 0, то будет создан новый курс): <input name="cid"  type="text" value="0" align="LEFT" size="6" maxlength="6" />';
    echo '<br><br>';    	
    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);

/*
    
    $maxuploadsize  = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose"). ':';
		
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="import_ct.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="courseid" value="'.$courseid.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">';
		
		
    echo '<input type="file" name="userfile" size="50">'.
*/    
    echo '<br><input type="submit" name="analys" value="'.$strscript.'">'.
         '</form><p>';
    echo '</center>';

    print_simple_box_start('center', '50%', 'white');
	echo '<center><form name="staffform1" id="staffform1" method="post" action="import_ct.php">'.
         '<input type="hidden" name="action" value="clear">'.
	     '<input name="clear" id="clear" type="submit" value="Очистить тестовые вопросы от %" />'.
		 '</form></center>';
    print_simple_box_end();
					    

    print_footer();
    

    
function analys_ct_csv($filename)
{
	global $CFG, $csv_delimiter, $acourses, $aquizs, $acategorys; // $acategoryshour = array();
	
	$courseserrors  = 0;	
	
	$redirlink = 'import_ct.php';
	
	if (!file_exists($filename)) {
	    error("The file $filename does not exist!", $redirlink);
	}

	$text = file($filename);
	if($text == FALSE)	{
		error(get_string('errorfile', 'block_dean'), $redirlink);
	}
	
	$size = sizeof($text);
	$textlib = textlib_get_instance();
	for($i=0; $i < $size; $i++)  {
		$text[$i] = $textlib->convert($text[$i], 'win1251');
    }

	$strcourse = get_string('lcourse', 'block_dean');   //1
	$strquiz= get_string('lquiz', 'block_dean'); //2
	$strcategory = get_string('lcat', 'block_dean'); //3

    // --- get course
    $header = split($csv_delimiter, $text[0]);
    $header0 = trim($header[0]);
    

	if ($header0 === $strcourse)	{
		// if ($analys == '') 
		echo '<b>'.$header[2] .  '<br>' . '</b>';
		$acourses[] = $header[2]; 
	} else {
		error('Название курса ЦТ не найдено.', $redirlink);
	}
	
    $line = split($csv_delimiter, $text[1]);
    $line0 = trim($line[0]);
    if ($line0 != $strquiz) {
    	error('Во второй строке необходимо указать название теста.', $redirlink);
    }	

	$linenum = 0;		 
	for($i=1; $i < $size; $i++)  {

		// echo "Line $i. " . $text[$i];
		$atexti = trim($text[$i]);
		if (empty($atexti)) continue;
	    $line = split($csv_delimiter, $text[$i]);
	    $line0 = trim($line[0]);
	    if ($line0 === $strquiz) {
	    	$j = $line[1];
			// if ($analys == '')  
			echo '<b><i>' . $j . '. ' . $line[2] . '</i></b>' . '<br>';
			$aquizs[$j] = $line[2];
			$acategorys[$j] = array();
			
		} else if ($line0 === $strcategory) {
			// if ($analys == '')  
			echo $line[1] . '=> ' . $line[2] . '=> ' . $line[3] . '<br>';
       		$line2 = trim($line[2]);
   			if ($line2 != '-') {
   				$acategorys[$j][$line[1]]->zip = $line2;
   			}
            if (!empty($line[3])) {
     		    $line3 = trim($line[3]);
                $acategorys[$j][$line[1]]->name = $line3;
            } else {
                $acategorys[$j][$line[1]]->name = $line2;
            }
  	
			// $acategoryshour[$j][$line[1]] = $line[3];
		} else {
			error('Не найдено название теста или категории  в строке ' . $i+1, $redirlink);
		}

	    $linenum++;
	    if ($linenum > 1000)	 {
			error(get_string('verybiglinenum', 'block_dean'), $redirlink);
	    }
	}    


	/*
    print_object($acourses);	 echo '<hr>';
	print_object($aquizs);	 echo '<hr>';
	print_object($acategorys);  echo '<hr>';
	print_object($acategoryshour);  echo '<hr>';
	exit(1);
	*/
    
	if (empty($acourses)) {
		notify ('Внимание! ОШИБКА: Не указано название курса ЦТ.');
		$courseserrors++;
	} 

	if (empty($aquizs)) {
		notify ('Внимание! ОШИБКА: Отсутствует тест. Необходимо наличие хотя бы одного теста.');
		$courseserrors++;
	} 

	if (empty($acategorys)) {
		notify ('Внимание! ОШИБКА: Нет ни одной категории теста.');
		$courseserrors++;
	} 
	
	foreach ($aquizs as $number => $aquiz)	{
		if (empty($number))	{
			notify ('Внимание! ОШИБКА: Не указан номер теста для ' . $aquiz);
			$courseserrors++;
		}
		if (empty($aquiz))	{
			notify ('Внимание! ОШИБКА: Не указано название теста для теста с №' . $number );
			$courseserrors++;
		}

		foreach ($acategorys[$number] as $numles => $acategory)	{
			if (empty($numles))	{
				notify ('Внимание! ОШИБКА: Не указан номер категории для ' . $acategory);
				$courseserrors++;
			}
			if (empty($acategory))	{
				notify ('Внимание! ОШИБКА: Не указано название категории для категории №' . $numles );
				$courseserrors++;
			} 
		}
	}							

	if ($courseserrors == 0)	{
		echo '<hr><br>';
		notify('Анализ завершен успешно. Ошибок: 0.', 'green');
	} else {
		notify("Анализ завершен. Ошибок: $courseserrors.");
	}
	echo '<br><hr>';				

	return $courseserrors;
}	



function create_new_course_сt($acourses, &$course_header) 
{
    global $CFG, $USER;

    $status = true;

    $fullname = $acourses[0];
    $shortname = mb_substr($acourses[0], 0, 30) . '_ЦТ';
    $currentfullname = "";
    $currentshortname = "";
    $counter = 0;
    //Iteratere while the name exists
    do {
        if ($counter) {
            $suffixfull = " ".get_string("copyasnoun")." ".$counter;
            $suffixshort = "_".$counter;
        } else {
            $suffixfull = "";
            $suffixshort = "";
        }
        $currentfullname = $fullname.$suffixfull;
        // Limit the size of shortname - database column accepts <= 100 chars
        $currentshortname = substr($shortname, 0, 100 - strlen($suffixshort)).$suffixshort;
        $coursefull  = get_record("course","fullname",addslashes($currentfullname));
        $courseshort = get_record("course","shortname",addslashes($currentshortname));
        $counter++;
    } while ($coursefull || $courseshort);

    //New name = currentname
    $course_header->course_fullname = $currentfullname;
    $course_header->course_shortname = $currentshortname;
    $course_header->course_password = generate_password(20); 
    $course_header->category->id = 64;
    $course_header->category->name = 'Центр тестирования';

    $course = new object();
    $course->category = addslashes($course_header->category->id);
    $course->password = addslashes($course_header->course_password);
    $course->fullname = addslashes($course_header->course_fullname);
    $course->shortname = addslashes($course_header->course_shortname);
    $course->idnumber = ''; 
    $course->summary = '';
    $course->format = 'topics';
    $course->showgrades = 1;
    $course->newsitems = 5;
    $course->teacher = 'Учитель';
    $course->teachers = 'Учителя';
    $course->student = 'Студент';
    $course->students = 'Студенты';
    $course->guest = 0;
    $course->startdate = time();
    $course->numsections = 1;
    $course->maxbytes = 16777216;
    $course->showreports = 0;
    $course->groupmode = 2;
    $course->groupmodeforce = 0;
    $course->defaultgroupingid = 0;
    $course->lang = '';
    $course->theme = '';
    $course->cost = '';
    $course->currency = 'USD';
    $course->marker = 0;
    $course->visible = 1;
    $course->hiddensections = 0;
    $course->timecreated = $course->startdate;
    $course->timemodified = $course->startdate;
    $course->metacourse = 0;
    $course->expirynotify = 0;
    $course->notifystudents = 0;
    $course->expirythreshold = 864000;
    $course->enrollable = 0;
    $course->enrolstartdate = 0;
    $course->enrolenddate = 0;
    $course->enrolperiod = 0;

    fix_course_sortorder();
    $course->sortorder = get_field_sql("SELECT min(sortorder)-1 FROM {$CFG->prefix}course WHERE category=$course->category");
    if (empty($course->sortorder)) {
        $course->sortorder = 100;
    }
 
    
    if ($newcourseid = insert_record('course', $course)) {  // Set up new course

        $course_header = get_record('course', 'id', $newcourseid);

        // Setup the blocks
        $page = page_create_object(PAGE_COURSE_VIEW, $course_header->id);
        blocks_repopulate_page($page); // Return value not checked because you can always edit later

		$allowedmods =  array();
        update_restricted_mods($course_header, $allowedmods);

        $section = new object();
        $section->course = $course_header->id;   // Create a default section.
        $section->section = 0;
        $section->id = insert_record('course_sections', $section);

        fix_course_sortorder();

        add_to_log(SITEID, 'course', 'new', 'view.php?id='.$course_header->id, $USER->username.' (ID '.$course_header->id.')');

        // Trigger events
        events_trigger('course_created', $course_header);
    } else {
    	
        $status = false;
        
    }

    return $status;
}


function create_quiz_in_course_сt($aquiz, $course) 
{
    global $CFG, $USER;
   
    $add     = 'quiz';
    $section = 1;
    if (!$module = get_record("modules", "name", $add)) {
          error("This module type doesn't exist");
    }
    $cw = get_course_section($section, $course->id);

    if (!course_allowed_module($course, $module->id)) {
        error("This module has been disabled for this particular course");
    }

    $cm = null;

    $form->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
    $form->visible          = $cw->visible;
    $form->course           = $course->id;
    $form->module           = $module->id;
    $form->modulename       = $module->name;
    $form->groupmode        = $course->groupmode;
    $form->groupingid       = $course->defaultgroupingid;
    $form->groupmembersonly = 0;
    $form->instance         = '';
    $form->coursemodule     = '';
    $form->add              = $add;
    $form->return           = 0; //must be false if this is an add, go back to course view on cancel

    $sectionname = get_section_name($course->format);
    $fullmodulename = get_string("modulename", $module->name);

    if ($form->section && $course->format != 'site') {
        $heading->what = $fullmodulename;
        $heading->to   = "$sectionname $form->section";
        $pageheading = get_string("addinganewto", "moodle", $heading);
    } else {
        $pageheading = get_string("addinganew", "moodle", $fullmodulename);
    }

    $CFG->pagepath = 'mod/'.$module->name;
    if (!empty($type)) {
        $CFG->pagepath .= '/'.$type;
    } else {
        $CFG->pagepath .= '/mod';
    }

    $navlinksinstancename = '';
    
    $cm = null;
        
	$fromform = new stdClass();
	$fromform->course = $course->id;
	$fromform->name = $aquiz;
	$fromform->intro = "";
	$fromform->timeopen = 0;
	$fromform->timeclose = 0;
	$fromform->timelimit = 0;
	$fromform->timelimitenable = 1;	
	$fromform->delay1 = 0;
	$fromform->delay2 = 0;
	$fromform->questionsperpage = 0;
	$fromform->shufflequestions = 1;
	$fromform->shuffleanswers = 1;
	$fromform->attempts = 3;	
	$fromform->attemptonlast = 0;
	$fromform->adaptive = 0;	
	$fromform->grademethod = 1;
	$fromform->penaltyscheme  = 0;
	$fromform->decimalpoints = 2;
	$fromform->feedbackimmediately = 1;					
	$fromform->optionflags = 0;
	$fromform->generalfeedbackimmediately = 1; 
	$fromform->scoreimmediately = 1; 
	$fromform->overallfeedbackimmediately = 1; 
	$fromform->feedbackopen = 1; 
	$fromform->generalfeedbackopen = 1; 
	$fromform->scoreopen = 1; 
	$fromform->overallfeedbackopen = 1; 
	$fromform->feedbackclosed = 1; 
	$fromform->generalfeedbackclosed = 1; 
	$fromform->scoreclosed = 1; 
	$fromform->overallfeedbackclosed = 1; 
	$fromform->popup = 0;
	$fromform->subnet = '';
	$fromform->quizpassword = '';	        
	$fromform->groupmode = 2;	
	$fromform->boundary_repeats = 4;
	$fromform->feedbacktext = array ( '', '', '', '', ''); 
	$fromform->feedbackboundaries = array ('', '', '', ''); 
    $fromform->visible          = 1;
    $fromform->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
	$fromform->cmidnumber 		= '';
	$fromform->gradecat			= 2824;		
    $fromform->course           = $course->id;
    $fromform->module           = $module->id; // [module] => 10
    $fromform->modulename       = $module->name; //  [modulename] => quiz
    $fromform->groupmode        = $course->groupmode;
    $fromform->groupingid       = $course->defaultgroupingid;
    $fromform->groupmembersonly = 0;
    $fromform->instance         = '';
    $fromform->coursemodule     = '';
    $fromform->add              = 'quiz';
    $fromform->return           = 0; //must be false if this is an add, go back to course view on cancel
	$fromform->grade = 100;
	$fromform->update = 0;	
 	$fromform->instance = '';
    $fromform->coursemodule = '';
        
    $addinstancefunction    = $fromform->modulename."_add_instance";

    // print_r($fromform);
    
    $returnfromfunc = $addinstancefunction($fromform);

    if (!$returnfromfunc) {
        error("Could not add a new instance of $fromform->modulename", "view.php?id=$course->id");
    }
    if (is_string($returnfromfunc)) {
        error($returnfromfunc, "view.php?id=$course->id");
    }

   $fromform->instance = $returnfromfunc;

    // course_modules and course_sections each contain a reference
    // to each other, so we have to update one of them twice.

    if (! $fromform->coursemodule = add_course_module($fromform) ) {
        error("Could not add a new course module");
    }
    if (! $sectionid = add_mod_to_section($fromform) ) {
        error("Could not add the new course module to that section");
    }

    if (! set_field("course_modules", "section", $sectionid, "id", $fromform->coursemodule)) {
        error("Could not update the course module with the correct section");
    }

    // make sure visibility is set correctly (in particular in calendar)
    set_coursemodule_visible($fromform->coursemodule, $fromform->visible);

    if (isset($fromform->cmidnumber)) { //label
        // set cm idnumber
        set_coursemodule_idnumber($fromform->coursemodule, $fromform->cmidnumber);
    }

    add_to_log($course->id, "course", "add mod",
               "../mod/$fromform->modulename/view.php?id=$fromform->coursemodule",
               "$fromform->modulename $fromform->instance");
    add_to_log($course->id, $fromform->modulename, "add",
               "view.php?id=$fromform->coursemodule",
               "$fromform->instance", $fromform->coursemodule);
    

        // sync idnumber with grade_item
        if ($grade_item = grade_item::fetch(array('itemtype'=>'mod', 'itemmodule'=>$fromform->modulename,
                     'iteminstance'=>$fromform->instance, 'itemnumber'=>0, 'courseid'=>$course->id))) {
            if ($grade_item->idnumber != $fromform->cmidnumber) {
                $grade_item->idnumber = $fromform->cmidnumber;
                $grade_item->update();
            }
        }

        $items = grade_item::fetch_all(array('itemtype'=>'mod', 'itemmodule'=>$fromform->modulename,
                                             'iteminstance'=>$fromform->instance, 'courseid'=>$course->id));

        // create parent category if requested and move to correct parent category
        if ($items and isset($fromform->gradecat)) {
            if ($fromform->gradecat == -1) {
                $grade_category = new grade_category();
                $grade_category->courseid = $COURSE->id;
                $grade_category->fullname = stripslashes($fromform->name);
                $grade_category->insert();
                if ($grade_item) {
                    $parent = $grade_item->get_parent_category();
                    $grade_category->set_parent($parent->id);
                }
                $fromform->gradecat = $grade_category->id;
            }
            foreach ($items as $itemid=>$unused) {
                $items[$itemid]->set_parent($fromform->gradecat);
                if ($itemid == $grade_item->id) {
                    // use updated grade_item
                    $grade_item = $items[$itemid];
                }
            }
        }

        // add outcomes if requested
        if ($outcomes = grade_outcome::fetch_all_available($course->id)) {
            $grade_items = array();

            // Outcome grade_item.itemnumber start at 1000, there is nothing above outcomes
            $max_itemnumber = 999;
            if ($items) {
                foreach($items as $item) {
                    if ($item->itemnumber > $max_itemnumber) {
                        $max_itemnumber = $item->itemnumber;
                    }
                }
            }

            foreach($outcomes as $outcome) {
                $elname = 'outcome_'.$outcome->id;

                if (array_key_exists($elname, $fromform) and $fromform->$elname) {
                    // so we have a request for new outcome grade item?
                    if ($items) {
                        foreach($items as $item) {
                            if ($item->outcomeid == $outcome->id) {
                                //outcome aready exists
                                continue 2;
                            }
                        }
                    }

                    $max_itemnumber++;

                    $outcome_item = new grade_item();
                    $outcome_item->courseid     = $COURSE->id;
                    $outcome_item->itemtype     = 'mod';
                    $outcome_item->itemmodule   = $fromform->modulename;
                    $outcome_item->iteminstance = $fromform->instance;
                    $outcome_item->itemnumber   = $max_itemnumber;
                    $outcome_item->itemname     = $outcome->fullname;
                    $outcome_item->outcomeid    = $outcome->id;
                    $outcome_item->gradetype    = GRADE_TYPE_SCALE;
                    $outcome_item->scaleid      = $outcome->scaleid;
                    $outcome_item->insert();

                    // move the new outcome into correct category and fix sortorder if needed
                    if ($grade_item) {
                        $outcome_item->set_parent($grade_item->categoryid);
                        $outcome_item->move_after_sortorder($grade_item->sortorder);

                    } else if (isset($fromform->gradecat)) {
                        $outcome_item->set_parent($fromform->gradecat);
                    }
                }
            }
        }

        rebuild_course_cache($course->id);
        grade_regrade_final_grades($course->id);

	return $fromform->instance;
}    



function import_from_giftwithimage ($courseid, $qcategoryid, $zipfile)	
{
	global $CFG, $USER;
	
	$course = get_record('course', 'id', $courseid);
	
	$category = get_record_select('question_categories', "id = $qcategoryid", 'id, name, contextid, info, stamp, parent, sortorder');
	
	$dir = "1/ct/".$USER->username;
	
	list($zipname, $zipext)  = explode ('.', $zipfile);  
	// $fullname = $CFG->dataroot.'/'.$dir.'/'.$zipfile;	

    if (!$basedir = make_upload_directory("$course->id")) {
        error("The site administrator needs to fix the file permissions");
    } 
	// echo $basedir; 

	$name = 'testimg';		
	if (!$testimgdir = make_upload_directory("$course->id/$name")) {
         echo "Error: could not create $name";
    }		
    // echo $testimgdir;

	$file =  $CFG->dataroot.'/'.$dir.'/'.$zipfile;	

    $archive = new PclZip(cleardoubleslashes($file));
    if (!$list = $archive->listContent(cleardoubleslashes($file))) {
        error('Файл не найден! '.' ('.$archive->errorInfo(true).')');
   }
    
    $form = new stdClass(); 
	$form->MAX_FILE_SIZE = 16777216; 
	$form->format = 'giftwithimg'; 
	$form->category = "$category->id,$category->contextid"; 
	$form->matchgrades = 'nearest'; 
	$form->stoponerror = 0; 
	$form->fao_dir = 'testimg'; 
	$form->fao_fileprefix = $zipname . '_'; 
	$form->fao_relativepath = 1; 
	$form->fao_shuflebydefault = 1; 
	$form->fao_answernumbering = 'none'; 
	
	$contexts = new question_edit_contexts(get_context_instance_by_id($category->contextid));
	
	$importfile = $file;
	
	$realfilename = $zipfile;	

	$fileoptions['name'] = $zipfile; 
	$fileoptions['type'] = 'application/x-zip-compressed'; 
	$fileoptions['tmp_name'] = $importfile; 
	$fileoptions['error'] = 0; 
	$fileoptions['size'] = 1; 
	$fileoptions['originalname'] = $zipfile; 
	$fileoptions['clear'] = 1;

	/*
	print_r($form); echo '<hr>'; 
	print_r($category); echo '<hr>';
	print_r($contexts); echo '<hr>';
	print_r($course); echo '<hr>';
	print_r($importfile); echo '<hr>';
	print_r($realfilename); echo '<hr>';
	print_r($fileoptions); echo '<hr>';
	*/
	// exit();


    $classname = "qformat_giftwithimg"; 
    $qformat = new $classname();

    // load data into class
    $qformat->setCategory($category);
    $qformat->setContexts($contexts->having_one_edit_tab_cap('import'));
    $qformat->setCourse($course);
    $qformat->setFilename($importfile);
    $qformat->setRealfilename($realfilename);
    $qformat->setFileOptions($fileoptions); //dlnsk  %%18%%
    $qformat->setMatchgrades($form->matchgrades);
    $qformat->setCatfromfile(!empty($form->catfromfile));
    $qformat->setContextfromfile(!empty($form->contextfromfile));
    $qformat->setStoponerror($form->stoponerror);

    $arr_form = (array)$form;
    $options = array();
    foreach($arr_form as $key => $value) { // getting additional parameters  (dlnsk %%39%%)
        if(substr($key,0,4) == 'fao_') {
            $options[substr($key,4)] = $value;
        }
    }
    $qformat->setOptions($options);

    // Do anything before that we need to
    if (! $qformat->importpreprocess()) {
        print_error('importerror', 'quiz', $thispageurl->out());
    }

    // Process the uploaded file
    if (! $qformat->importprocess()) {
        print_error('importerror', 'quiz', $thispageurl->out());
    }

    // In case anything needs to be done after
    if (! $qformat->importpostprocess()) {
        print_error('importerror', 'quiz', $thispageurl->out());
    }

    echo "<hr />";
    
    return true;
}



function clear_questions_answers()
{
    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	$currtime = time();
	
	$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			where answer like'\\%<sub>%' ";	
	if ($subs = get_records_sql($strsql))	{
	
		foreach ($subs as $sub) {
			echo '<hr>'; print_r($sub); echo '<br>';  
			
			$str30 = substr($sub->answer, 0, 30);
			
			echo "str30 = $str30<br>";
			
			$findme   = '%</sub>';
			$pos = strpos($str30, $findme);
			if ($pos === false) {
				notify ('&lt;sub&gt; not found');
				continue;
			}	
			
			echo "pos = $pos<br>";
			
			$strnormal = substr($sub->answer, $pos+7);
			
			echo "strnormal = $strnormal<br>";
			
			$number = '';
			
			
			for ($i=0; $i<30; $i++) {
				
				if ($str30[$i] == '-' || $str30[$i] == '.' || ctype_digit($str30[$i]))	{
					$number .= $str30[$i]; 
				}
			}			
			
			echo "number= $number<br>";
			
			$number /= 100;
			
			echo "number= $number<br>";
			
			$rec->id = $sub->id;
			$rec->answer = $strnormal;  
			$rec->fraction = $number;
			
			if (!update_record('question_answers', $rec))	{
				print_r($rec);
				error("Error in update question_answers");
			} 
		}
 	} else {
 		// notice ('Records not found!', 'index.php');
 		notify ("Вопросы, содержащие %&lt;sub&gt;% не обнаружены!", 'green');
 	}
 	
 	$procents = array ('16.667', '33,3333', '33,333', '33,333', '33,33', '33,3', '33,4', '14,2', '12,5', '16,667', '16,6', 
	 					'100', '70', '50', '40', '25', '20', '30', '35');
 	
 	
 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\%$procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% $procent");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% $procent", '', $sub->answer);
						}	
					} else {
						$strnormal = str_replace ("%$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= 100;
				echo "number= $number<br>";	
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
			}	  
		} else {
		      notify ("Вопросы, содержащие  %&lt;{$procent}&gt;% не обнаружены!", 'green');
		}
	}		
 	
 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\%-$procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%-$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%-$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% -$procent");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% -$procent", '', $sub->answer);
						}	
						
					} else {
						$strnormal = str_replace ("%-$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%-$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= -100;
				echo "number= $number<br>";		
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
						
			}	  
		} else {
              notify ("Вопросы, содержащие  %&lt;-{$procent}&gt;% не обнаружены!", 'green');
		}
	}		


 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\% $procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% $procent%");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% $procent%", '', $sub->answer);
						}	
					} else {
						$strnormal = str_replace ("%$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= 100;
				echo "number= $number<br>";	
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
			}	  
		} else {
              notify ("Вопросы, содержащие  %&lt; {$procent}&gt;% не обнаружены!", 'green');
		}
	}		
 	
 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\% -$procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%-$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%-$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% -$procent%");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% -$procent%", '', $sub->answer);
						}	
						
					} else {
						$strnormal = str_replace ("%-$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%-$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= -100;
				echo "number= $number<br>";		
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
						
			}	  
		} else {
              notify ("Вопросы, содержащие  %&lt; -{$procent}&gt;% не обнаружены!", 'green');
		}
	}    
}

?>