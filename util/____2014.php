<?PHP // $Id: 
	require_once('../../config.php');
    require_once('lib.php');
    require_once('synchronization/lib_ldap.php');

    $yid = 16;
    $startyear = 2015;
	$strtitle = '__ Регистрация групп 2015 года набора в Пегасе';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> " . $strtitle;
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}
    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
    
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
                
                if ($specs = get_records_sql ("SELECT id FROM mdl_dean_speciality
                                          where facultyid={$rec->facultyid} order by name"))   {
                                            
                    $spec =reset($specs);
                    $rec->specialityid = $spec->id; 
                } else {
                    $rec->specialityid = 0;
                    notify ('1. rec->specialityid = 0');
                    print_object($rec);
                }
                
                if (!empty($rec->specialityid)) {
                    if ($currs = get_records_sql("SELECT id FROM mdl_dean_curriculum
                                                  where specialityid={$rec->specialityid} order by name"))    {
                                                    
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
                    print_object($existgroups);
                    print_object($rec);
                    print '<hr />';
                    registration_agroup_from_bsu_students($rec->name);
                }   else {
                    if (insert_record('dean_academygroups', $rec))  {
                        print_object($rec);
                        registration_agroup_from_bsu_students($rec->name);
                    } else {
                        notify ('ERRROR!!! insert_record dean_academygroups');
                    }    
                } 
                // print_object($rec);
            }
        }
    }
    
    notify ('Complete!!');
    print_footer();
    
?>