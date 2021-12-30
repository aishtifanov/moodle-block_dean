<?php // $Id: __uuu.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __ set session date for academygroup";
    print_header("$SITE->shortname: __ set session date for academygroup", $SITE->fullname, $breadcrumbs);


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
    $agroups = get_records_sql ("SELECT * FROM {$CFG->prefix}dean_academygroups");
    if ($agroups)	{
        foreach ($agroups as $agroup) {
            $session->facultyid = $agroup->facultyid;
            $session->groupid   = $agroup->id;
            $session->edyearid  = 12;
            $session->numsession = 3;
            $session->datestart = $agroup->begwinter;
            $session->dateend   = $agroup->endwinter;
    		if (!insert_record('dean_journal_session', $session))	{
    		     echo '<pre>'; print_r($session); echo '</pre>';   
    			 notice("Ошибка при создании сессии: {$session->numsession}", "sesdates.php?fid=$fid&amp;gid=$gid");		                    
            } else {
                echo '!<pre>'; print_r($session); echo '</pre>';
            }     

            $session->numsession = 5;
            $session->datestart = $agroup->begsummer;
            $session->dateend   = $agroup->endsummer;
    		if (!insert_record('dean_journal_session', $session))	{
    		     echo '<pre>'; print_r($session); echo '</pre>';   
    			 notice("Ошибка при создании сессии: {$session->numsession}", "sesdates.php?fid=$fid&amp;gid=$gid");		                    
            } else {
                echo '!<pre>'; print_r($session); echo '</pre>';
            }
        }    
    }             


	print_footer();
	

?>


