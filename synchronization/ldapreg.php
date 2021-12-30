<?php // $Id: ldapreg.php,v 1.5 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');    

    $namegroup = optional_param('namegroup', '', PARAM_TEXT);		// Group name (number)
    $firstkurs = optional_param('firstkurs', '', PARAM_TEXT);		// BUTTON

	$strsynchronization = get_string('ldapregistaration','block_dean');
    $strsynchroniz = get_string("ldapreg", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


    $currenttab = 'ldapreg';
    include('ldaptabs.php');

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

    $yid = get_current_edyearid();
    $startyear = get_field_select('bsu_ref_edyear', 'God', "id=$yid");

    if (!empty($firstkurs)) {
        create_group_firstkurs($yid, $startyear);
    }
	// $admin_is = true;
	 
	// echo  $namegroup . '<br>';
    $searchtext = (string)$namegroup;

	// $namegroup = '100459';
    if (isset($namegroup) && !empty($namegroup)) 	{
        registration_agroup_from_bsu_students($namegroup);
    }

	print_heading($strsynchronization, 'center', 2);
	notify (get_string('description_ldapregistaration','block_dean'), 'black');

    print_dean_box_start('center', '80%');


	echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapreg.php">'.
		 get_string('numgroup', 'block_dean'). '&nbsp&nbsp'.
		 '<input type="text" name="namegroup" size="10" value="' . $searchtext. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsynchroniz  . '" /><br><br><hr>'.
         '<input name="firstkurs" id="firstkurs" type="submit" value="Создать группы первого курса" />'.
		 '</form></div>';
		 
   	print_dean_box_end();
   	
    print_footer();

/**
 * 
 * 
 * Добавление специальностей с именем года 
insert into mdl_dean_speciality
SELECT 0 as id, id as facultyid, '2015' as number, concat('2015 первый курс ', number) as name, 
'Бакалавр' as qualification, UNIX_TIMESTAMP() as timemodified, 0 as idspecyal, 2 as idkvalif, 
'2015' as codespeciality, '62' as codekvalif, 0 as codespecii
FROM mdl_dean_faculty
where number>10000;

* Добавление РУП с именем года
insert into mdl_dean_curriculum
SELECT 0 as id, facultyid, id as specialityid, 0 as code, name, '2015/2016' as enrolyear, 
'daytimeformtraining' as formlearning, '' as description, UNIX_TIMESTAMP() as timemodified, '2015' as thisyear
FROM mdl_dean_speciality
where number = '2015';
 *   
 *
 */
function create_group_firstkurs($yid, $startyear)
{
    $time = time();

    $facultys = get_records_select_menu('dean_faculty', " number > 10000", '', 'number, id');

    if ($groups = get_records_select('bsu_ref_groups', "yearid=$yid and  startyear=$startyear", 'name'))   {
        
        foreach ($groups as $group) {
            if (isset($facultys[$group->departmentcode]))    {
                $rec = new stdClass();
                $rec->facultyid = $facultys[$group->departmentcode];
                $rec->oldfacultyid = 0;
                $rec->name = $group->name;
                $rec->oldname = '';
                $rec->startyear = $group->startyear;
                $rec->term = 1;
                $rec->description = '';
                $rec->timemodified = $time;
                $rec->idotdelenie = $group->idedform;
                $rec->begwinter = 0;
                $rec->endwinter = 0;
                $rec->begsummer = 0;
                $rec->endsummer = 0;
                
                $sql = "SELECT id FROM mdl_dean_speciality
                        where facultyid={$rec->facultyid} and number = '$startyear' 
                        order by name";
                if ($specs = get_records_sql ($sql))   {
                                            
                    $spec =reset($specs);
                    $rec->specialityid = $spec->id; 
                } else {
                    $rec->specialityid = 0;
                    notify ('1. rec->specialityid = 0');
                    print_object($rec);
                }
                
                if (!empty($rec->specialityid)) {
                    $sql = "SELECT id FROM mdl_dean_curriculum
                            where specialityid={$rec->specialityid} and thisyear = '$startyear'   
                            order by name";
                    if ($currs = get_records_sql($sql))    {
                                                    
                        $curr = reset($currs );
                        $rec->curriculumid  = $curr->id; 
                    } else {
                        $rec->curriculumid = 0;
                        notify ('2. $rec->curriculumid = 0');
                        print_object($rec);
                    }
                    
                } else {
                    $rec->curriculumid = 0;
                    notify ('3. $rec->curriculumid = 0');
                    print_object($rec);                    
                }
                if ($existgroups = get_records_select('dean_academygroups', "name = '$rec->name'"))    {
                    notify ("Группа $rec->name уже есть в Пегасе!");
                    // print_object($existgroups);
                    // print_object($rec);
                    print '<hr />';
                    registration_agroup_from_bsu_students($rec->name);
                }   else {
                    if (insert_record('dean_academygroups', $rec))  {
                        notify("Группа $rec->name создана!", 'green', 'left');
                        // print_object($rec);
                        registration_agroup_from_bsu_students($rec->name);
                    } else {
                        notify ('ERRROR!!! insert_record dean_academygroups');
                    }    
                } 
                // print_object($rec);
            }
        }
    }
}
?>