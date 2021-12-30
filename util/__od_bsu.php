<?php // $Id: __uuu.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 
    $strtitle = 'Change email @od.bsu.edu.ru'; 
    
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

    //id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie, begwinter, endwinter, begsummer, endsummer

    // id, name, begwinter, endwinter, begsummer, endsummer
    $ausers = get_records_sql ("SELECT id, email FROM moodle.mdl_user where email like '%@od.bsu.edu.ru'");
    if ($ausers)	{
        foreach ($ausers as $auser) {
            $auser->email = str_ireplace('@od.bsu.edu.ru', '@bsu.edu.ru', $auser->email);
            
    		if (!set_field('user', 'email', $auser->email, 'id', $auser->id))	{
    		    echo '<pre>'; print_r($auser); echo '</pre>';   
    			notice("Ошибка при обновлении email: {$auser->email}", "");		                   
            }    
        }
    }                 

    notify('Complete all');

	print_footer();
	

?>


