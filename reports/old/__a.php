<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    
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

    $strsql =  "SELECT S.Id, S.GroupNo, D.DisciplineNameid, D.SubDepartmentId, S.EdWorkId, REFD.Name as DisciplineName, REFS.Name as SubDepartment, S.AuditoryId, A.Aud, A.KorpName, S.StaffFormId, S.DateTimeStart, S.DateTimeFinish, S.CustomText, S.IsExamTesting
                FROM            mdl_bsu_schedule AS S
                     INNER JOIN mdl_bsu_edworks AS W ON W.Id = S.edworkid
                     INNER JOIN mdl_bsu_disciplines AS D ON D.Id = W.Disciplineid
                     INNER JOIN mdl_bsu_ref_disciplinename AS REFD ON REFD.Id = D.DisciplineNameId
                     INNER JOIN mdl_bsu_ref_subdepartment AS REFS ON REFS.Id = D.SubDepartmentId
                     INNER JOIN mdl_bsu_auditories as A ON A.Id=S.AuditoryId
                where (datetimestart > '2011-04-01') AND (W.EdWorkTypeId = 4)
                ORDER BY S.GroupNo";
                
    if ($agroups = get_records_sql($strsql))    {
        execute_sql('TRUNCATE TABLE mdl_dean_schedule');
        foreach ($agroups as $agroup)   {
            unset($agroup->Id);
            insert_record('dean_schedule', $agroup); 
        }
    }    

    notify ('Complete mdl_dean_schedule', 'green');
/*    
    $strsql =  "SELECT PG.Id, PG.GroupNo, P.SpecialityId, REFSP.Name as Speciality
                FROM           mdl_bsu_plans AS P
                    INNER JOIN mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId
                    INNER JOIN mdl_bsu_ref_speciality AS REFSP ON REFSP.Id = P.SpecialityId
                WHERE (P.EdYearId = 11) AND (P.EdFormId = 6) AND (PG.SemesterId % 2 = 0)
                ORDER BY PG.GroupNo";
    if ($agroups = get_records_sql($strsql))    {
        execute_sql('TRUNCATE TABLE mdl_dean_schedspec');
        foreach ($agroups as $agroup)   {
            unset($agroup->Id);
            insert_record('dean_schedspec', $agroup); 
        }
    }    
                                
    notify ('Complete mdl_dean_schedspec', 'green');                                
*/

/*
    execute_sql('ALTER TABLE mdl_bsu_planandgroupbinding MODIFY COLUMN Id INT(11) NOT NULL AUTO_INCREMENT');

    $strsql =  "SELECT concat(PG.Id, PG.GroupNo) as Id, P.parentplanid, PG.planid, PG.semesterid, PG.groupno
                FROM  mdl_bsu_plans AS P INNER JOIN mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId
                WHERE (P.parentplanid>0) AND (P.EdYearid=11) AND (PG.semesterid % 2 = 0)";

    if ($agroups = get_records_sql($strsql))    {
        $cnt = count($agroups);
        echo $cnt;
        $i=0;  
        foreach ($agroups as $agroup)   {
            if (!record_exists_mou('bsu_planandgroupbinding', 'planid', $agroup->parentplanid, 'semesterid', $agroup->semesterid, 'groupno', $agroup->groupno))  {
                notify ("planid = $agroup->parentplanid, semesterid=$agroup->semesterid, groupno=$agroup->groupno");
                $rec->planid = $agroup->parentplanid;
                $rec->semesterid=$agroup->semesterid;
                $rec->groupno=$agroup->groupno;
                insert_record('bsu_planandgroupbinding', $rec);
                unset($rec);
                $i++;
            }
        }        
        echo $i;
    }    
                

$strsql =  "SELECT PG.GroupNo, D.DisciplineNameId, D.SubDepartmentId, W.Id as  EdWorkId, P.SpecialityId, REFD.Name as DisciplineName, REFS.Name as SubDepartment, REFSP.Name as Speciality
              FROM   mdl_bsu_plans AS P INNER JOIN mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
                     mdl_bsu_edworks AS W ON D.Id = W.DisciplineId INNER JOIN
                     mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId INNER JOIN
                     mdl_bsu_ref_disciplinename AS REFD ON REFD.Id = D.DisciplineNameId INNER JOIN
                     mdl_bsu_ref_subdepartment AS REFS ON REFS.Id = D.SubDepartmentId INNER JOIN
                     mdl_bsu_ref_speciality AS REFSP ON REFSP.Id = P.SpecialityId
            WHERE    (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
            ORDER BY   PG.GroupNo";
  
   
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
            insert_record('dean_schedule', $agroup); 
        }
    }    

*/
 	echo 'Complete';
	print_footer();
	


?>


