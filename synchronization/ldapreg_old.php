<?php // $Id: ldapreg_old.php,v 1.3 2010/12/22 16:00:32 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');    

    $namegroup = optional_param('namegroup', '', PARAM_TEXT);		// Group name (number)

	$strsynchronization = get_string('ldapregistaration','block_dean');
    $strsynchroniz = get_string("ldapreg", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


    $currenttab = 'ldapreg';
    include('ldaptabs.php');

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	// $admin_is = true;
	 
	// echo  $namegroup . '<br>';
    $searchtext = (string)$namegroup;

	// $namegroup = '100459';
    if (isset($namegroup) && !empty($namegroup)) 	{

	    $agroup = get_record('dean_academygroups', 'name', $namegroup);
		// print_r($agroup); echo '<br>';
		if ($agroup)	{
			$numst = get_records('dean_academygroups_members', 'academygroupid', $agroup->id);
			// print_r($numst); echo '<br>';
		    if ($numst)	{
		    	error(get_string('syncgrouphavestudents', 'block_dean'), 'ldapreg_old.php');
		    }
	    
			if ($lstudents = get_records('dean_ldap', 'group1', $namegroup, 'lastname'))	{

				$ldapstuds = array();
				foreach($lstudents as $lstudent)	{
					$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
				}	
					
				$table->head  = array (get_string("ldapstuds","block_dean"), get_string("logins", "block_dean"), get_string("action","block_dean"));
				$table->align = array ("left", "center", "center");
				$table->size = array ('20%', '15%', '10%');
				$table->width = '50%';
				$strlinkupdate = '-';
				
				foreach($lstudents as $lstudent)	{
					
					$table->data[] = array ($ldapstuds[$lstudent->login], $lstudent->login, $strlinkupdate);
					
					dean_registry_ldap($lstudent, $agroup);
				}	
				print_table($table);
			}						
		}
		else {
			notify(get_string('groupnotfound','block_dean'));
			echo '<hr>';
		}
	}

	print_heading($strsynchronization, 'center', 2);
	notify (get_string('description_ldapregistaration','block_dean'), 'black');

    print_dean_box_start('center', '80%');


	echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapreg_old.php">'.
		 get_string('numgroup', 'block_dean'). '&nbsp&nbsp'.
		 '<input type="text" name="namegroup" size="10" value="' . $searchtext. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsynchroniz  . '" />'.
		 '</form></div>';
		 
   	print_dean_box_end();
   	
    print_footer();


?>