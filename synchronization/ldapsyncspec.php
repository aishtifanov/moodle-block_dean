<?php // $Id: ldapsyncspec.php,v 1.2 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');

    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);          // Speciality id
   	$go = optional_param('go', 0, PARAM_INT);			//     
  	
	
	$strsynchronization = get_string('ldapsynchspec','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    $currenttab = 'ldapsyncspec';
    include('ldaptabs.php');
    

    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");

	print_heading($strsynchronization, 'center', 4);
	notify (get_string('description_ldapsyncspec','block_dean'), 'black');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("ldapsyncspec.php?sid=$sid&amp;fid=", $fid);
    listbox_speciality("ldapsyncspec.php?fid=$fid&amp;sid=", $fid, $sid);
	echo '</table>';

	if ($fid != 0 && $sid != 0)  {
		
		$ugroups1 = get_records_sql ("SELECT id, name FROM {$CFG->prefix}dean_academygroups
									 WHERE facultyid=$fid AND specialityid=$sid
									 ORDER BY name");
		if ($ugroups1) {
			$totalcountout = $totalcountmove = $totalcountldap = 0;  
			foreach ($ugroups1 as $ugroup) {
				$len = strlen ($ugroup->name);
				$numgroup = substr($ugroup->name, -2);
				if ($len == 8 && $numgroup < 50)	{ 
					print_heading($ugroup->name, 'center', 2);
					
					$table = table_syncronization_with_ldap($ugroup->name, $go);
					if (isset($table->data))	{
						print_table($table);
					} else {
						notify ('Полное совпадение списков группы', 'green');
					}	
				}	
			}
			
			echo '<hr>';
			notify("Количество отсутствующих студентов: ". $totalcountout, 'green');	
			notify("Количество переводимых студентов: ". $totalcountmove, 'green');
			notify("Количество студентов ldap, не зарегистрированных в Пегасе: ". $totalcountldap, 'green');			

		} else {
			print_heading('Группы не найдены!', 'center', 2);
		}	

	    print_dean_box_start('center', '80%');

		echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapsyncspec.php">'.
			 '<input type="hidden" name="fid" value="' . $fid. '" />'.
			 '<input type="hidden" name="sid" value="' . $sid. '" />'.		 
	 		 '<input type="hidden" name="go" value="1">' .				 
		     '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
			 '</form></div>';
			 
			 
	   	print_dean_box_end();
	}   	
   	
    print_footer();

?>