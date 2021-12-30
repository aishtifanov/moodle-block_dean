<?php // $Id: __uuu.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __ set idotdelenie for academygroup";
    print_header("$SITE->shortname: __ set idotdelenie for academygroup", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

    $strsql = "SELECT distinct grup, idOtdelenie FROM mdl_bsu_students where Dateotsh='' order by grup";    
    if ($bsugroups = get_records_sql($strsql))    {
        // print_r($bsugroups); echo '<hr>';
        $arr_bsuotdl = array();
        foreach ($bsugroups as $bsugroup)   {
            $arr_bsuotdl["$bsugroup->grup"] = $bsugroup->idOtdelenie;
        }    
    }    
    
    // id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
    $strsql = "SELECT id, specialityid, curriculumid, name FROM {$CFG->prefix}dean_academygroups
			   WHERE idotdelenie=0 ORDER BY name"; 
	if ($agroups = get_records_sql ($strsql)) {
	    // синхронизируем все имеющиеся в деканате группы
 		foreach ($agroups as $agroup) {
 		     $idotdelenie = 0;  
 		     if (isset($arr_bsuotdl["$agroup->name"]))   {
                 $idotdelenie =  $arr_bsuotdl["$agroup->name"];
             } else {
				$namegroup = trim($agroup->name);
				$len = strlen ($namegroup);
				$numgroup = substr($namegroup, -2);
				if ($len == 6 && $numgroup < 50)	{
				    $idotdelenie = 2;
                } else if ($numgroup > 50 && $numgroup <100 ) {
                    $idotdelenie =  3;
                }    
             }
             echo $agroup->name . " : " . $idotdelenie . "<br>";
             set_field('dean_academygroups', 'idotdelenie', $idotdelenie, 'id', $agroup->id);  
        }        
    }  

	print_footer();
	

?>


