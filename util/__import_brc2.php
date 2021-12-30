<?php // $Id: __zaoch.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $


    require_once('../../config.php');
    // require_once('lib.php');

    $strtitle = 'Создание ресурсов для БРС';
    
    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
    
    $coursenames = array(
'Рисунок _1 семестр',
'Живопись_1 семестр',
'Живопись_2 семестр',
'Живопись_4 семестр',
'Графический дизайн_3 семестр',
'Композиция_3 семестр',
'Рисунок _4 семестр',
'Графический дизайн_3 семестр',
'Композиция_3 семестр',
' Пластическая анатомия',
'Скульптура',
'Основы черчения и начертательной геометрии',
'Введение в мировую художественную культуру',
'Живопись (для спец. 071001)_1 семестр',
'Рисунок (для спец. 071001)_1 семестр',
'Общий курс композиции (для спец. 071001)_1 семестр',
'Техника живописи, технология живописных материалов  (для спец. 071001)_2 семестр',
'Основы ДПИ_4 семестр',
'Методика обучения ИЗО'
);

  	create_courses_brs($coursenames);
 
	print_footer();
	
	
	
function create_courses_brs($coursenames) 
{
	global $CFG, $db;
	
    $a = get_record_sql("SELECT max(id) as lastid FROM mdl_course");
    
    $i = 1;

    
    foreach ($coursenames as $coursename)    {
        
                $data->MAX_FILE_SIZE=16777216;
                $data->category=108;
                $data->fullname=  $coursename;
                $data->shortname='БРС_' . ($a->lastid + $i);
                $data->idnumber='';
                $data->summary='';
                $data->format='topics';
                $data->numsections=10;
                $data->startdate=1318536000;
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
                $data->timemodified=time();
                
            if (!$course = create_course($data)) {
                print_object($data);
                print_error('coursenotcreated');
            } else {
                notify ("Курс $data->fullname  создан.", 'green'); 
            }    
    }   
   

	return true;
}	 

?>


