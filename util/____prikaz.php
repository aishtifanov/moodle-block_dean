<?PHP // $Id: 
	require_once('../../config.php');
    require_once('lib.php');

    $yid = 16;
    // $startyear = 2015;
	$strtitle = '__ Переименование групп в Пегасе в соответствии с ИнфоБелГУ';

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
    
    $notin = '10305, 10205, 10204, 18100, 10303, 10203, 10201, 10206, 19101, 19102, 19103, 19206, 19207';

    $table = new stdClass();
    $table->head  = array ('Старое имя', '', 'Новое имя', '');
    $table->align = array ("center", "center", "center", "center");

    $time = time();

    execute_sql("drop temporary table if exists temp_info");
    execute_sql("create temporary table temp_info
    SELECT rg.id, rg.departmentcode,  rg.name, group_concat(gm.username) as unames, count(username) as c1
    FROM moodle.mdl_bsu_ref_groups rg
    inner join mdl_bsu_group_members gm on rg.id=gm.groupid
    inner join mdl_bsu_ref_department rd on rg.departmentcode=rd.DepartmentCode
    where rg.yearid=$yid and gm.deleted=0 and rg.departmentcode not in ($notin)
    group by rg.id"); // 10500, 10600, 10700

    if ($groups = get_records_sql("select * from temp_info"))   {
        foreach ($groups as $group) {
           
            $sql = "SELECT da.name, count(da.name) as c2
                    FROM mdl_dean_academygroups da
                    inner join mdl_dean_academygroups_members am on da.id=am.academygroupid
                    inner join mdl_user u on u.id=am.userid
                    where u.username in ($group->unames)
                    group by da.id
                    order by 2 desc";

            if ($datas = get_records_sql($sql)) {
                $data = reset($datas);
                if ($group->name <> $data->name)    {
                    if ($group->c1 == $data->c2 || ($group->c1+1 == $data->c2) || ($group->c1 == $data->c2+1))    {
                        if (record_exists_select('dean_academygroups', "name ='$group->name'")) {
                            notify('Попытка перименования группы ' . $data->name . '. Группа уже существует:' . $group->name);   
                        } else {
               			    $strsql = "UPDATE mdl_dean_academygroups 
                                       SET name = '$group->name', oldname = '$data->name'
                                       WHERE name = '$data->name'";
               			    if (!($rs = $db->Execute($strsql))) {
               			        print $sql;
               			        print($group);
               			        print($datas);
                                print "$data->name = $data->c2 | $group->name = $group->c1 <br>";
                				error(get_string('errorinupdatinggroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
                   			} else {
                   			    $strsql = 'UPDATE mdl_groups SET name = \''. $group->name .'\' WHERE name = \''. $data->name .'\'';
                   			    if (!($rs = $db->Execute($strsql))) {
                        			error(get_string('errorinupdatingnamegroup','block_dean'), "$CFG->wwwroot/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid");
                       			}
                   			}
                            $table->data[] = array ('<b>'.$data->name.'</b>', $data->c2, $group->name, $group->c1);
                        }    
                    } else {
                        $table->data[] = array ($data->name, $data->c2, $group->name, $group->c1);
                    }    
                    // print($datas);
                    // print "$group->name = $group->c1 | $data->name = $data->c2 <br>";
                }    
            }
        }    
        print_table($table);
    }
    
    notify ('Complete!!');
    print_footer();
  
/*
            execute_sql("create temporary table temp_info
                        SELECT rg.departmentcode, gm.username, rg.name
                        FROM moodle.mdl_bsu_ref_groups rg
                        inner join mdl_bsu_group_members gm on rg.id=gm.groupid
                        inner join mdl_bsu_ref_department rd on rg.departmentcode=rd.DepartmentCode
                        where rg.yearid=$yid and gm.deleted=0 and rg.id=$group->id");

            execute_sql("drop temporary table if exists temp_name");
            execute_sql("create temporary table temp_name
                        SELECT f.number as departmentcode, u.username, da.name as oldname, ti.name as newname
                        FROM mdl_dean_academygroups da
                        inner join mdl_dean_faculty f on f.id=da.facultyid
                        inner join mdl_dean_academygroups_members am on da.id=am.academygroupid
                        inner join mdl_user u on u.id=am.userid
                        inner join temp_info ti using(username)");


*/
    
?>