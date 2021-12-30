<?PHP // $Id: 
	require_once('../../config.php');

	$strtitle = '__ Перевод таблиц Пегаса на новую орг. структуру.';

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

    step1_create_field();
    step2_insert_new_faculty();
    step3_update_dean_academygroups();
    step4_update_dean_methodist();
    step5_update_mdl_groups();
    
    print_footer();
    

function step1_create_field()
{
    $sql = "SELECT * FROM mdl_dean_academygroups";
    $agroup = get_record_sql($sql);
    if (!isset($agroup->oldname))   {
        execute_sql("ALTER TABLE mdl_dean_academygroups ADD COLUMN `oldname` VARCHAR(100) AFTER `name`");
        execute_sql("ALTER TABLE mdl_dean_academygroups ADD COLUMN `oldfacultyid` INT(10) NOT NULL DEFAULT 0 AFTER `facultyid` ");
    }    
}


function step2_insert_new_faculty()
{      
  
    $sql = "SELECT departmentcode, name, shortname, olddepcode FROM mdl_bsu_ref_department
            where departmentcode>=10100";
    
    if ($departments = get_records_sql($sql))  {
        foreach($departments as $department){
            $expl_olddepcodes = explode(',', $department->olddepcode);
            $newdepcode = $department->departmentcode;
            if(!$old_depcode = get_record('dean_faculty', 'number', $expl_olddepcodes[0]))  {
                $old_depcode->deanid = 0;
                $old_depcode->deanphone1 = '';
                $old_depcode->deanphone2 = '';
                $old_depcode->deanaddress = '';
            }
            $rec->number       = $newdepcode;
            $rec->name         = $department->name;
            $rec->deanid       = $old_depcode->deanid;
            $rec->deanphone1   = $old_depcode->deanphone1;
            $rec->deanphone2   = $old_depcode->deanphone2;
            $rec->deanaddress  = $old_depcode->deanaddress;
            $rec->timemodified = time();
            $rec->shortname    = $department->shortname;
            
            if(!record_exists('dean_faculty', 'number', $newdepcode)){
                if(insert_record('dean_faculty', $rec)){
                    notify('Факультет "'.$rec->name.'" успешно содан.', 'green', 'center');
                }                    
            } else {
                notify('Факультет "'.$rec->name.'" уже существует.');
            }
        }
    }

}  

function step3_update_dean_academygroups()
{      
    global $CFG;
    
    $a = get_records_select_menu('bsu_ref_groups', "", '', 'oldname, name');
    $b = get_records_select_menu('bsu_ref_groups', "", '', 'oldname, departmentcode');
    $c = get_records_select_menu('dean_faculty', " number > 10000", '', 'number, id');
    
    // print_object($a);     print_object($b);     print_object($c);
                    
                    
    $sql = "SELECT id, facultyid, oldfacultyid, specialityid, curriculumid, name, oldname FROM {$CFG->prefix}dean_academygroups";
    if($academygroups = get_records_sql($sql)){
        // print_object($academygroups);
       $spec = array();
       $curr = array();
       foreach($academygroups as $academygroup) {
            if (isset($a[$academygroup->name]))  {
                $academygroup->oldfacultyid = $academygroup->facultyid;
                $academygroup->oldname = $academygroup->name;
                $academygroup->name = $a[$academygroup->oldname];
                if (isset($c[$b[$academygroup->oldname]]))  {
                    $academygroup->facultyid    = $c[$b[$academygroup->oldname]];
                    if  (!update_record('dean_academygroups', $academygroup))   {
                        notify('Ошибка при обновлении dean_academygroups');
                    } else {
                        print_object($academygroup);
                    }
                }    
                $spec[$academygroup->specialityid] = $academygroup->facultyid;
                $curr[$academygroup->curriculumid] = $academygroup->facultyid;
            }  else {
                notify ('Not found index for :'. $academygroup->name);
            }  
        }
        
        foreach ($spec as $specialityid => $facultyid)  {
             set_field('dean_speciality', 'facultyid', $facultyid, 'id', $specialityid);    
        }
        
        foreach ($curr as $curriculumid => $facultyid)  {
             set_field('dean_curriculum', 'facultyid', $facultyid, 'id', $curriculumid);    
        }

    }
    
}


function step4_update_dean_methodist()
{
    $faculty = get_records_select_menu('dean_academygroups', "", '', 'oldfacultyid, facultyid');
    
    foreach($faculty as $oldfacultyid => $facultyid){                
        set_field_select('dean_methodist', 'facultyid', $facultyid, "facultyid=$oldfacultyid"); 
    }
}
    
    
function step5_update_mdl_groups()
{
    $agroups = get_records_select_menu('dean_academygroups', "", '', 'oldname, name');
    
    foreach($agroups as $oldname => $name){                
        set_field_select('groups', 'name', $name, "name='$oldname'"); 
    }
}    
        



?>