<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');


    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __Clear all ZERO Moodle group";
    print_header("$SITE->shortname: __Clear all ZERO Moodle group", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

  	delete_zero_mgroups();
 
	print_footer();
	
	
	
function delete_zero_mgroups() 
{
	global $db;
	
    $sql    = 'SELECT mdl_groups.id
			FROM mdl_groups LEFT JOIN mdl_groups_members ON mdl_groups.id = mdl_groups_members.groupid
			WHERE (mdl_groups_members.groupid Is Null)';
    $i = 0; 
	if ($recs = get_records_sql($sql))	{
		foreach ($recs as $rec)	{
			$db->Execute('DELETE FROM mdl_groups WHERE id ='. $rec->id);
			echo $i . ':' . $rec->id . '<br>';
			$i++; 
		}
	}

	return true;
}	 

?>


