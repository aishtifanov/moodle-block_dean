<?php // $Id: __uuu.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 

    $strtitle = "Rename med college"; 
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

    // id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
    $strsql = "SELECT * FROM mdl_dean_academygroups  where name like '______мк' ORDER BY name"; 
	if ($agroups = get_records_sql ($strsql)) {
	    // синхронизируем все имеющиеся в деканате группы
 		foreach ($agroups as $agroup) {
			$namegroup = trim($agroup->name);
			$numgroup = 'mc' . substr($namegroup, 0, 6);                                 
            echo $namegroup . " : " . $numgroup . "<br>";
            set_field('dean_academygroups', 'name', $numgroup, 'id', $agroup->id);  
        }        
    }  

    // id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
    $strsql = "SELECT * FROM mdl_groups  where name like '______мк' ORDER BY name"; 
	if ($agroups = get_records_sql ($strsql)) {
	    // синхронизируем все имеющиеся в деканате группы
 		foreach ($agroups as $agroup) {
			$namegroup = trim($agroup->name);
			$numgroup = 'mc' . substr($namegroup, 0, 6);                                 
            echo $namegroup . " : " . $numgroup . "<br>";
            set_field('groups', 'name', $numgroup, 'id', $agroup->id);  
        }        
    }  

	print_footer();
	

?>


