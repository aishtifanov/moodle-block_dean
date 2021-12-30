<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once("../../../config.php");
   
   	$num = optional_param('n', 1, PARAM_INT);
   	
	$strtitle = 'Schedule';

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

    
    $strsql =  "SELECT     concat(D.Id, PG.GroupNo) as Id, PG.GroupNo, D.DisciplineNameId, D.SubDepartmentId, W.Id as  EdWorkId, P.SpecialityId, REFD.Name as DisciplineName, REFS.Name as SubDepartment, REFSP.Name as Speciality
                FROM       mdl_bsu_plans AS P INNER JOIN mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
                           mdl_bsu_edworks AS W ON D.Id = W.DisciplineId INNER JOIN
                           mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId INNER JOIN
                           mdl_bsu_ref_disciplinename AS REFD ON REFD.Id = D.DisciplineNameId INNER JOIN
                           mdl_bsu_ref_subdepartment AS REFS ON REFS.Id = D.SubDepartmentId INNER JOIN
                           mdl_bsu_ref_speciality AS REFSP ON REFSP.Id = P.SpecialityId
                WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
                ORDER BY   PG.GroupNo";
        
                     
    if ($agroups = get_records_sql($strsql))    {
        execute_sql('TRUNCATE TABLE mdl_dean_schedule');
        foreach ($agroups as $agroup)   {
            unset($agroup->Id);
            insert_record('bsu_schedule', $agroup); 
        }
    }    

 	echo 'Complete';
	print_footer();
	


?>


