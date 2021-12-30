<?php // $Id: import_gift.php,v 1.1 2011/01/13 14:16:26 shtifanov Exp $

	require_once('../../config.php');
    require_once($CFG->libdir.'/uploadlib.php');
    require_once($CFG->dirroot.'/backup/restorelib.php');
    require_once($CFG->dirroot.'/mod/quiz/lib.php');
    require_once($CFG->libdir.'/questionlib.php');
    require_once($CFG->dirroot.'/question/format.php'); // Parent class
	require_once($CFG->dirroot.'/question/format/giftwithimg/format.php'); // Child class
	require_once($CFG->dirroot.'/question/editlib.php');
	include_once("$CFG->libdir/pclzip/pclzip.lib.php");
	
	require_login();
    
	$strtitle = get_string('dean','block_dean');
	$strscript = get_string('import_ct', 'block_dean');   
	$strcourse = get_string('lcourse', 'block_dean');   //1
	$strquiz= get_string('lquiz', 'block_dean'); //2
	$strcategory = get_string('lcat', 'block_dean'); //3

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/dean/index.php", 'type' => 'misc');
	$navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
	$navigation = build_navigation($navlinks);
	print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

	$redirlink = $CFG->wwwroot. "/blocks/dean/index.php";
	
	$courseid = 3402;
	$qcategoryid = 23720;
	$zipfile = 'd1_t5.zip';
	
	import_from_giftwithimage ($courseid, $qcategoryid, $zipfile);
	
    print_footer();	
	
	
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

?>