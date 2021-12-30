<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');

	$strtitle = 'Заменить pegas.bsu.edu.ru в текстовых полях.';

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
    
    $olddomainname = "pegas.bsu.edu.ru";
    $newdomainname = "pegas1.bsu.edu.ru";

    $sql = "SELECT @i:=@i+1 as id, TABLE_NAME, COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
FROM (select @i:=0) as z, information_schema.`COLUMNS` C
where C.`TABLE_SCHEMA` = 'moodle' and  data_type in ('varchar', 'text', 'mediumtext', 'longtext', 'char')
and (TABLE_NAME not like 'mdl_ank%' and TABLE_NAME not like 'mdl_bsu_%' and TABLE_NAME not like 'mdl_dean_%'
and TABLE_NAME not like 'mdl_disser%' and TABLE_NAME not like 'mdl_user%' and TABLE_NAME not like 'mdl_v%'
and TABLE_NAME not like 'mdl_w%' and TABLE_NAME not like 'mdl_log%' and TABLE_NAME not like 'mdl_cache%' AND
TABLE_NAME not like 'mdl_mnet%'  AND TABLE_NAME not like 'mdl_employ%' AND
TABLE_NAME not in ('mdl_question_states','mdl_question_sessions'))";
    
	$columns = get_records_sql($sql);
    // print_object($columns);
    $tablenames = '';
	foreach ($columns as $column)	{
        $sql = "SELECT $column->COLUMN_NAME FROM $column->TABLE_NAME WHERE $column->COLUMN_NAME like '%{$olddomainname}%' LIMIT 1";
        if ($pegas = get_records_sql($sql)) {
            $tablenames .= $column->TABLE_NAME . '<br>';
            print $sql . '<br>';
            
            $sql = "update $column->TABLE_NAME set `{$column->COLUMN_NAME}` = REPLACE(`{$column->COLUMN_NAME}`, '{$olddomainname}',  '{$newdomainname}')
                    WHERE `{$column->COLUMN_NAME}` like '%{$olddomainname}%'";
            print $sql . '<br>';                    
            if (!execute_sql($sql)) {
                $sql = "SELECT id FROM $column->TABLE_NAME 
                        WHERE `{$column->COLUMN_NAME}` like '%{$olddomainname}%'";
                 if ($datas = get_records_sql($sql)) {
                    foreach ($datas as $data)   {
                        $sql = "update $column->TABLE_NAME set `{$column->COLUMN_NAME}` = REPLACE(`{$column->COLUMN_NAME}`, '{$olddomainname}',  '{$newdomainname}')
                                WHERE id = $data->id";
                        print $sql . '<br>';                    
                        execute_sql($sql);
                    }
                 }   
            }  
        }
	}
 	
    print $tablenames;
	print_footer();
?>