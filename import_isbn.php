<?php // $Id: import_ct.php,v 1.3 2012/08/29 13:11:18 shtifanov Exp $

	require_once('../../config.php');
    require_once($CFG->libdir.'/uploadlib.php');
    require_once($CFG->dirroot.'/backup/restorelib.php');
    require_once($CFG->dirroot.'/course/lib.php');

    require_login();
    
	define("MAX_ZIP_SIZE", 16777216);
    
    $courseid = optional_param('courseid', 0, PARAM_INT);   // course id
    $action = optional_param('action', '');      
    
    $MODULEID = 11;

    $csv_delimiter = ';';
    $coursesnew = 0;

	$strtitle = get_string('dean','block_dean');
	$strscript = 'Импорт ISBN';   
	$strcourse = get_string('lcourse', 'block_dean');   //1


    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/dean/index.php", 'type' => 'misc');
	$navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
	$navigation = build_navigation($navlinks);
	print_header($SITE->shortname . ': '. $strscript, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("256M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
   	}    

 	$redirlink = $CFG->wwwroot. "/blocks/dean/index.php";
	if ($rec = data_submitted())  {
		
	   if (!empty($_FILES['newfile']['name']))	{

			$dir = "1/ISBN/CSV";
       		$um = new upload_manager('newfile', true, false, 1, false, MAX_ZIP_SIZE);
       		// print_r($um);  echo '<hr>';
	        if ($um->process_file_uploads($dir))  {
		          $file = $um->get_new_filename();
        	      print_heading(get_string('uploadedfile'), 'center', 4);
        	      // echo $newfile_name;
        	      
        	      $fullname = $CFG->dataroot."/".$dir."/".basename($file);
        	      echo $fullname . '<br />';
	              // echo "<li>".get_string("unzippingbackup").'</li>';
	              // $ctcsvname = $CFG->dataroot."/".$dir."/ct.csv";
	              $status = import_isbn($fullname);
	              if ($status == 0)	{
						echo '<hr><br>';
						notify("Файл $fullname успешно обработан.", 'green');
						echo '<br><hr>';	
	              }	
            }
	   }
	}   
   
	print_heading($strscript, 'center', 2);

    $CFG->maxbytes = MAX_ZIP_SIZE; 

    $struploadafile = get_string('zipimportct', 'block_dean');
    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

 	echo '<center>';
	echo "<p>Импорт CSV-файла с ID-шниками курсов ($strmaxsize):</p>";
    echo '<form enctype="multipart/form-data" name=form method=post action=import_isbn.php>';	
    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);

    echo '<br><input type="submit" name="analys" value="'.$strscript.'">'.
         '</form><p>';
    echo '</center>';


    print_footer();
    

    
function import_isbn($filename)
{
	global $CFG, $MODULEID, $csv_delimiter;
	
    $courseserrors = 0;
	
	$redirlink = 'import_isbn.php';
	
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

	for($i=0; $i < $size; $i++)  {
		
		$courseid = trim($text[$i]);
        echo "Line $i. Course ID: " . $courseid . '<br />';
        if (record_exists_select('course', "id = $courseid"))   {
            insert_resource_isbn($courseid);
        } else {
            notify("Курс $courseid не найден!");
        }    
        
        /*
	    $linenum++;
	    if ($linenum > 1000)	 {
			error(get_string('verybiglinenum', 'block_dean'), $redirlink);
	    }
        */
	}    


	return $courseserrors;
}	



function insert_resource_isbn($courseid) 
{
    global $CFG, $MODULEID, $USER;

    $rec = new stdClass();
    $rec->course = $courseid;
    $rec->name = 'Титульный лист с ISBN';
    $rec->type = 'file'; 
    $rec->reference = "http://pegas.bsu.edu.ru/file.php/1/ISBN/ISBN_{$courseid}.pdf";
    $rec->summary = 'Титульный лист с ISBN';
    $rec->alltext = '';
    $rec->popup = '';
    $rec->options = 'forcedownload';
    $rec->timemodified = time();

	$verify = get_record_select('resource', "course=$courseid AND name='$rec->name'", 'id');
	if(!$verify) {
		$idresource = insert_record('resource', $rec);

        if ($section1 = get_record_select('course_sections', "course=$courseid AND section=1", 'id, sequence')) {
            
            $modules = new stdClass();
            $modules->course = $courseid;
            $modules->module = $MODULEID;
            $modules->instance = $idresource;
            $modules->section = $section1->id;
            $modules->added = $rec->timemodified;
            $modules->score = 0;
	   		$modules->indent = 0;
	   		$modules->visible = 1;
	   		$modules->visibleold = 1;
	   		$modules->groupmode = 2;
	   		$modules->groupingid = 0;
	   		$modules->groupmembersonly = 0;

			$verify = get_record_select('course_modules', "course=$courseid AND module=$MODULEID AND instance=$idresource", 'id');
			if(!$verify) {
		        $idmodule = insert_record('course_modules', $modules);
			     // $quiz_context = get_context_instance(CONTEXT_MODULE, $idmodule);
			    $section1->sequence = $idmodule.','.$section1->sequence;
			    $idsection = update_record('course_sections', $section1);
                rebuild_course_cache($courseid);
			} else {
                notify("Модуль с instance=$idresource уже существуе в курсе $courseid.");
            }  
        }  else {
            notify("Не найдена 1-ая секция в курсе $courseid.");
        }  
	} else {
	   notify("Ресурс '$rec->name' уже существует в курсе $courseid.");
	}
}

?>