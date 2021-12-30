<?php // $Id: __zaoch.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    require_once($CFG->libdir.'/uploadlib.php');
    require_once('lib.php');

    define("MAX_ZIP_SIZE", 16777216);
    
    $strtitle = get_string('dean','block_dean');
    $strscript = 'Создание ресурсов для БРС';
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/dean/index.php", 'type' => 'misc');
	$navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
	$navigation = build_navigation($navlinks);
	print_header($SITE->shortname . ': '. $strscript, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

	if ($rec = data_submitted())  {
	   if (!empty($_FILES['newfile']['name']))	{
	       $brc = import_brc();
           // print_object($brc);
           if (!empty($brc))    {
                ignore_user_abort(false); 
                @set_time_limit(0);
                @ob_implicit_flush(true);
                @ob_end_flush();
                create_brc_courses($brc);
           }
           
	   }
    }
   
	print_heading($strscript, 'center', 2);

    print_import_form();
    
	print_footer();
	

function print_import_form()
{
    global $CFG, $strscript;
    
    $CFG->maxbytes = MAX_ZIP_SIZE; 

    $struploadafile = 'CSV-файл со списком дисциплин, преподавателей и групп.';// get_string('zipimportct', 'block_dean');
    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

 	echo '<center>';
	echo "<p>$struploadafile ($strmaxsize):</p>";
    echo '<form enctype="multipart/form-data" name=form method=post action=import_brc.php>';	
    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);
    echo '<br><input type="submit" name="analys" value="'.$strscript.'">'.
         '</form><p>';
    echo '</center>';
    
    return;
}


function import_brc()
{
    $csv_delimiter = ';';
    $recs = array();
   	$um = new upload_manager('newfile', true, false, 1, false, MAX_ZIP_SIZE);
	if ($um->preprocess_files()) {
		$filename = $um->files['newfile']['tmp_name'];
       	$fp = fopen($filename, "r");
       	
		$textlib = textlib_get_instance();

		$shapka = fgets($fp,1024);
		$shapka = $textlib->convert($shapka, 'win1251');
            
        list($n1, $n2, $n3, $n4) = split($csv_delimiter, $shapka);
        
        if ($n1 != 'Название дисциплины')   {
           error(get_string('unknownlabel', 'block_dean', $n1), "brc.php"); 
        }
        if ($n2!= 'логин')   {
           error(get_string('unknownlabel', 'block_dean', $n2), "brc.php"); 
        }
        if ($n3!= 'ФИО преподавателя')   {
           error(get_string('unknownlabel', 'block_dean', $n2), "brc.php"); 
        }      
        if ($n4!= 'Номер группы')   {
           error(get_string('unknownlabel', 'block_dean', $n2), "brc.php"); 
        }
		$RUProw = array();
	    $i = 0;
		while (!feof ($fp)) {
		   if ($RUProw = xfgetcsv ($fp, 1000, $csv_delimiter))  {
		        if ($RUProw[0] == '') break;
                foreach ($RUProw as $indx => $rup)	{
                	$RUProw[$indx] = $textlib->convert($rup, 'win1251');		   			
                }
                // print_object($RUProw); echo '<hr>';
                
                $recs[$i]->fullname = trim($RUProw[0]);
                $recs[$i]->username = trim($RUProw[1]);
                $recs[$i]->fio = trim($RUProw[2]);
                $recs[$i]->groups = trim($RUProw[3]);
                $i++;
		   }
	    }
   }     
    
   return $recs; 
}
	
	
function create_brc_courses($brcs) 
{
	global $CFG, $db;
    
    $a = get_record_sql("SELECT max(id) as lastid FROM mdl_course");
    
    $i = 1;
    foreach ($brcs as $brc)    {
        
        if ($user = get_record_select ('user', "username = '$brc->username'", 'id'))    {
            
            $data->MAX_FILE_SIZE=16777216;
            $data->category=108;
            $data->fullname = 'БРС' . ' ' . $brc->fullname;
            $data->shortname='БРС_' . ($a->lastid + $i);
            $data->idnumber='';
            $data->summary='';
            $data->format='topics';
            $data->numsections=2;
            $data->startdate=time();
            $data->hiddensections=0;
            $data->newsitems=5;
            $data->showgrades=1;
            $data->showreports=0;
            $data->maxbytes=16777216;
            $data->metacourse=0;
            $data->enrol='';
            $data->defaultrole=0;
            $data->enrollable=1;
            $data->enrolstartdate=0;
            $data->enrolstartdisabled=1;
            $data->enrolenddate=0;
            $data->enrolenddisabled=1;
            $data->enrolperiod=0;
            $data->expirynotify=0;
            $data->notifystudents=0;
            $data->expirythreshold=864000;
            $data->groupmode=2;
            $data->groupmodeforce=0;
            $data->visible=1;
            $data->enrolpassword='';
            $data->guest=0;
            $data->lang='';
            $data->restrictmodules=0;
            $data->role_1='';
            $data->role_2='';
            $data->role_3='';
            $data->role_4='';
            $data->role_5='';
            $data->role_6='';
            $data->role_7='';
            $data->role_8='';
            $data->role_9='';
            $data->role_10='';
            $data->role_11='';
            $data->role_12='';
            $data->role_13='';
            $data->role_14='';
            $data->role_15='';
            $data->role_16='';
            $data->submitbutton='Сохранить';
            $data->id=0;
            $data->teacher='Учитель';
            $data->teachers='Учителя';
            $data->student='Студент';
            $data->students='Студенты';
            $data->password='';
            $data->timemodified=1318511954;
            
            if (!$course = create_course($data)) {
                print_object($data);
                print_error('coursenotcreated');
            } else {
                notify ("Курс $data->fullname  создан.", 'green'); 
            }
            
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
            role_assign(3, $user->id, 0, $context->id);
            notify ("Преподаватель $brc->fio  подписан на курс.", 'green');
            
                
            $groupsnames = explode(',', $brc->groups);
            foreach ($groupsnames as $agroupname)   {
                  enrol_academygroup_to_course($agroupname, $course->id);
                  notify ("Группа  $agroupname подписана на курс.", 'green');
            }
            $i++;
        } else {
            error('Пользователь с логином ' . $brc->username . 'не найден.'); 
        }     
    }   
	return true;
}   
 
?>