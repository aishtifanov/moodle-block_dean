<?php // $Id: __duble.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');

	$strtitle = '__ Delete duble userid in dean_academygroup';

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

    $strsql = "SELECT userid, academygroupid, count(userid) as cnt FROM mdl_dean_academygroups_members
               Group by userid, academygroupid
               having count(userid)>1";

	if ($agroups = get_records_sql($strsql))   {
        echo count($agroups) . '<hr>';
    	foreach ($agroups as $agroup)	{
    	   $dubles = get_records_select('dean_academygroups_members', "userid = $agroup->userid");
           echo '<pre>'; print_r($dubles); echo '</pre>'; 
           $curr = current($dubles);
           delete_records('dean_academygroups_members', 'id', $curr->id);
           echo '<hr>'; 
    	}
    } else {
        notify('Dubles not found.');
    }    
 	
	print_footer();
?>