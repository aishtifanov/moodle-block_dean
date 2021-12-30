<?php // $Id: __uuu.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 
    $strtitle = 'Set role tutor'; 
    
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

  $arrEng = array('A', 'B', 'V', 'G', 'D', 'E', 'J', 'H', 'Z', 'I', 'Y', 'K', 'L', 'M',
                  'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'K', 'C', 'W', 'E', 'Q', 'X');


    // id, name, begwinter, endwinter, begsummer, endsummer
  $strsql = "SELECT u.id, u.lastname, u.email FROM mdl_user u
             LEFT JOIN mdl_role_assignments r ON u.id=r.userid
            where r.userid IS NULL and email like'K%@bsu.edu.ru'";

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


