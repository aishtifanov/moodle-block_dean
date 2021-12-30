<?php // $Id: examschedule.php,v 1.9 2011/04/19 07:21:56 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
/*
SELECT     D.Id
FROM       Plans AS P INNER JOIN Disciplines AS D ON P.Id = D.PlanId INNER JOIN
           EdWorks AS W ON D.Id = W.DisciplineId INNER JOIN
           PlanAndGroupBinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId
WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)

SELECT     PG.GroupNo, D.DisciplineNameId, D.SubDepartmentId, W.Id as  EdWorkId
FROM       mdl_bsu_plans AS P INNER JOIN mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
           mdl_bsu_edworks AS W ON D.Id = W.DisciplineId INNER JOIN
           mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId
WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
ORDER BY   PG.GroupNo


SELECT id, disciplineid FROM mdl_bsu_edworks where edworktypeid = 4 and semesterid in (2,4,6,8,10,12)


SELECT a.id, b.name, c.name FROM mdl_bsu_disciplines a, mdl_bsu_ref_disciplinename b, mdl_bsu_ref_subdepartment c
where a.disciplinenameid = b.id and a.subdepartmentid=c.id and a.id in (SELECT disciplineid FROM mdl_bsu_edworks where edworktypeid = 4 and semesterid in (2,4,6,8,10,12))


SELECT     TOP (100) PERCENT PG.GroupNo, Sem.Number AS Semester, DN.Name
FROM         dbo.Plans AS P INNER JOIN
                      dbo.Disciplines AS D ON P.Id = D.PlanId INNER JOIN
                      dbo.EdWorks AS W ON D.Id = W.DisciplineId INNER JOIN
                      dbo.PlanAndGroupBinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId INNER JOIN
                      dbo.Ref_DisciplineName AS DN ON D.DisciplineNameId = DN.Id INNER JOIN
                      dbo.Ref_Semester AS Sem ON W.SemesterId = Sem.Id
WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
ORDER BY PG.GroupNo, Semester, DN.Name


SELECT     PG.GroupNo, Sem.Number AS Semester, DN.Name
FROM        mdl_bsu_plans AS P INNER JOIN
            mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
            mdl_bsu_edworks AS W ON D.Id = W.DisciplineId INNER JOIN
            mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId INNER JOIN
            mdl_bsu_ref_disciplinename AS DN ON D.DisciplineNameId = DN.Id INNER JOIN
            mdl_bsu_ref_semester AS Sem ON W.SemesterId = Sem.Id
WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
ORDER BY PG.GroupNo, Semester, DN.Name



SELECT   PG.GroupNo, D.DisciplineNameId, D.SubDepartmentId, W.Id as  EdWorkId, P.SpecialityId, REFD.Name as DisciplineName, REFS.Name as SubDepartment, REFSP.Name as Speciality
            FROM       mdl_bsu_plans AS P INNER JOIN mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
                       mdl_bsu_edworks AS W ON D.Id = W.DisciplineId INNER JOIN
                       mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId INNER JOIN
                       mdl_bsu_ref_disciplinename AS REFD ON REFD.Id = D.DisciplineNameId INNER JOIN
                       mdl_bsu_ref_subdepartment AS REFS ON REFS.Id = D.SubDepartmentId INNER JOIN
                       mdl_bsu_ref_speciality AS REFSP ON REFSP.Id = P.SpecialityId
            WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
            ORDER BY   PG.GroupNo
*/
    require_login();
    
	$action = optional_param('action', '');

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    // if (!$admin_is && !$creator_is && !$methodist_is) {
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    // ignore_user_abort(false); 
    @raise_memory_limit("512M");       
    @set_time_limit(0);
    
	$action   = optional_param('action', '');
    if ($action == 'excel') 	{
		$table = table_examschedule("'");
  		print_table_to_excel($table);
        exit();
	}

    @ob_implicit_flush(true);
    @ob_end_flush();

	$strtitle = get_string('examschedule','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examschedule';
    include('tabs.php');

	$table = table_examschedule();
   	print_table($table);

	echo  '<form name="allgroups" method="post" action="examschedule.php">';
	echo  '<input type="hidden" name="action" value="excel" />';
	echo  '<div align="center">';
	echo  '<input type="submit" name="mark" value="';
	print_string('downloadexcel');
	echo '"></div></form>';

   print_footer();


function table_examschedule($specsym = "")	
{
	global $CFG;
	
    $table->head  = array ('N', get_string('group'), get_string('discipline','block_dean'), 
                            get_string('sspeciality','block_dean'), "Кафедра", "Экзаменатор"); 
    $table->align = array ("center", "left", "left", "left", 'left', 'left');
	$table->width = '75%';
    $table->size = array ('5%', '5%', '20%', '20%', '20%', '20%', '10%', '10%');
    $table->columnwidth = array (7, 8, 39, 39, 52, 48);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examschedule','block_dean');
    $table->downloadfilename = "examschedule";
    $table->worksheetname = 'examschedule';
    
    $recs = get_records_select('bsu_ref_disciplinename', '', '', 'Id, Name');
    $ref_discname = array(); 
    foreach ($recs as $rec) {
        $ref_discname[$rec->Id] = $rec->Name; 
    }

    unset($recs);    
    $recs = get_records_select('bsu_ref_subdepartment', '', '', 'Id, Name');
    $ref_subdepartment  = array(); 
    foreach ($recs as $rec) {
        $ref_subdepartment [$rec->Id] = $rec->Name; 
    }

    unset($recs);
    $recs = get_records_select('bsu_teachingload', '', '', 'Id, StaffFormId, EdWorkId');
    $ref_teachingload  = array(); 
    foreach ($recs as $rec) {
        $ref_teachingload [$rec->EdWorkId] = $rec->StaffFormId; 
    }

    unset($recs);
    $recs = get_records_select('bsu_staffform', '', '', 'Id, Name');
    $ref_staffform  = array(); 
    foreach ($recs as $rec) {
        $ref_staffform [$rec->Id] = $rec->Name; 
    }

    unset($recs);

    $recs = get_records_select('bsu_ref_speciality', '', '', 'Id, Code, Name');
    $ref_speciality  = array(); 
    foreach ($recs as $rec) {
        $ref_speciality[$rec->Id] = $rec->Code. '. ' . $rec->Name; 
    }

    unset($recs);
 
    $i = 1;
/*
    $strsql =  "SELECT     concat(D.Id, PG.GroupNo) as Id, PG.GroupNo, D.DisciplineNameId, D.SubDepartmentId, W.Id as  EdWorkId, P.SpecialityId
                FROM       mdl_bsu_plans AS P INNER JOIN mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
                           mdl_bsu_edworks AS W ON D.Id = W.DisciplineId INNER JOIN
                           mdl_bsu_planandgroupbinding AS PG ON P.Id = PG.PlanId AND W.SemesterId = PG.SemesterId
                WHERE     (P.EdYearId = 11) AND (P.EdFormId = 6) AND (W.EdWorkTypeId = 4) AND (W.SemesterId % 2 = 0)
                ORDER BY   PG.GroupNo";
*/        
    $strsql = "SELECT Id, GroupNo, DisciplineNameId, SubDepartmentId, EdWorkId, SpecialityId FROM mdl_dean_schedule";
                     
    if ($agroups = get_records_sql($strsql))    {
        
            foreach ($agroups as $agroup)   {
                
                $SubDepartment  = $ref_subdepartment[$agroup->SubDepartmentId];
                $DisciplineName = $ref_discname[$agroup->DisciplineNameId];
                $StaffFormId    = $ref_teachingload[$agroup->EdWorkId];
                $teacher        = $ref_staffform[$StaffFormId]; 
                $table->data[] = array ($i++, $specsym.$agroup->GroupNo, $DisciplineName, 
                                        $ref_speciality[$agroup->SpecialityId], $SubDepartment, $teacher);
            }
    }                                
                
    
    
/*    
    $semestrinyear = array(2 => '10', 4 => '09', 6 => '08', 8 => '07', 10 => '06', 12 => '05');
    // $semestrinyear = array(1 => '10', 3 => '09', 5 => '08', 7 => '07', 9=> '06', 11 => '05');
    
    foreach ($semestrinyear as $semestrid =>  $year)  {
    
        $strsql = "SELECT a.id, semesterid, groupno FROM mdl_bsu_plans a INNER JOIN mdl_bsu_planandgroupbinding b ON a.id = b.planid
                   where edformid=6 and semesterid = $semestrid and groupno like '__{$year}__'
                   order by groupno";
        
        if ($agroups = get_records_sql($strsql))    {
            foreach ($agroups as $agroup)   {
                 
                $strsql = "SELECT id, DisciplineNameId, SubDepartmentId FROM mdl_bsu_disciplines 
                           where PlanId = $agroup->id"; 
                if ($disciplines = get_records_sql($strsql))    {
                    $discids = array();
                    $SubDepartmentIds = array();
                    $DisciplineNameIds = array();
                    foreach ($disciplines as $discipline)   {
                        $discids[] = $discipline->id;
                        $SubDepartmentIds[$discipline->id] = $discipline->SubDepartmentId;
                        $DisciplineNameIds[$discipline->id] = $discipline->DisciplineNameId; 
                    }
                    $listids = implode (',', $discids); 
                    
                    $strsql = "SELECT id, disciplineid FROM mdl_bsu_edworks where edworktypeid = 4 and semesterid = $semestrid and disciplineid in ($listids)";
                    if ($examdiscs = get_records_sql($strsql))    {
                        foreach ($examdiscs as $examdisc)   {
                                $SubDepartmentId = $SubDepartmentIds[$examdisc->disciplineid];
                                $DisciplineNameId = $DisciplineNameIds[$examdisc->disciplineid];
                                $StaffFormId = $ref_teachingload[$examdisc->id];
                                $teacher = $ref_staffform [$StaffFormId]; 
                                $table->data[] = array ($i++, $specsym.$agroup->groupno, $ref_discname[$DisciplineNameId], $ref_subdepartment[$SubDepartmentId], $teacher);                    
                        }
                     }   
                    
                } 
            }
        }
                
    }
*/    
    return $table;	
}	

?>