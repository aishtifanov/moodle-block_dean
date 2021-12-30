<?php // $Id: ldapsyncfac.php,v 1.7 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');
    
    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id
   	$go = optional_param('go', 1, PARAM_INT);			//
    $delay = optional_param('t', 30, PARAM_INT);			//     
	
	$strsynchronization = get_string('ldapsynchfac','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    $currenttab = 'ldapsyncfac';
    include('ldaptabs.php');
    

    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");

	print_heading($strsynchronization, 'center', 4);
	notify (get_string('description_ldapsynchfac','block_dean'), 'black');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("ldapsyncfac.php?fid=", $fid);
	echo '</table>';

	if ($fid != 0)  {
		
		if (!$faculty = get_record('dean_faculty', 'id', $fid))	exit(1);
		
		$cod = $faculty->number - 100; 
		
		if ($cod > 21) {
	   		$fid++;
	   		redirect("ldapsyncfac.php?fid=$fid", '', 0);
		}
		
		if ($cod < 10)	{
			$shablon = '0'. $cod . '%';
		} else {
			$shablon = $cod . '%';
		}	
		
		$lgroups1 = get_records_sql("SELECT distinct group1 FROM mdl_dean_ldap where group1 like '$shablon'");
		if ($lgroups1) {
			foreach ($lgroups1 as $lgroup) {
				$namegroup = trim($lgroup->group1);
				$len = strlen ($namegroup);
				$numgroup = substr($namegroup, -2);
				 
                if(!$agroup = get_record_select('dean_academygroups', "name = '$namegroup'", 'id, name'))	{

			    	print_heading("Группа $namegroup не найдена в системе Пегас", 'center', 2);
				    
                    if ($len == 8)  {
                        
                        if ($numgroup < 50)	{
                            $formlearning = 'daytimeformtraining';
                        } else {
                            $formlearning = 'correspondenceformtraining';
                        }    
                    
        				$curriculum = get_record_sql("SELECT id, facultyid, specialityid  FROM mdl_dean_curriculum m
        								              where facultyid=$fid and formlearning='$formlearning'");
                                          
    					// print_r($curriculum);			  
    					if ($curriculum) {	

    						$rec->facultyid = $curriculum->facultyid;
    						$rec->specialityid = $curriculum->specialityid;
    						$rec->curriculumid = $curriculum->id;
    						$rec->name = $namegroup;
    						$rec->startyear = '20' . substr($namegroup, -4, 2);  
    						$rec->description = "";
                            $rec->idotdelenie =  get_idotdelenie_group($namegroup);                            
    						// print_r($rec);
    						if (insert_record('dean_academygroups', $rec))	{
    							notify(get_string('groupadded','block_dean'), 'green');
    						}
    					}	
                    }    	
				}    
			}	
		}	
		
		$ugroups1 = get_records_sql ("SELECT id, name FROM {$CFG->prefix}dean_academygroups
									 WHERE facultyid=$fid
									 ORDER BY name");
		if ($ugroups1) {
			$totalcountout = $totalcountmove = $totalcountldap = 0;  
			foreach ($ugroups1 as $ugroup) {
				$len = strlen ($ugroup->name);
				$numgroup = substr($ugroup->name, -2);
				if ($len == 6 || $len == 8) {  // && $numgroup < 50)	{ 
					$table = table_syncronization_with_ldap($ugroup->name, $go);
					if ($table)	{
						if (isset($table->data))	{
							print_heading($ugroup->name, 'center', 2);
							print_table($table);
						} else {
							notify ('Полное совпадение списков группы ' . $ugroup->name, 'green');
						}
					}		
				}	
			}
			
			echo '<hr>';
			notify("Количество отсутствующих студентов: ". $totalcountout, 'green');	
			notify("Количество переводимых студентов: ". $totalcountmove, 'green');
			notify("Количество студентов ldap, не зарегистрированных в Пегасе: ". $totalcountldap, 'green');
			echo '<hr>';
		} else {
			print_heading('Группы не найдены!', 'center', 2);
		}
		
	    print_dean_box_start('center', '80%');

		echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapsyncfac.php">'.
			 '<input type="hidden" name="fid" value="' . $fid. '" />'.
	 		 '<input type="hidden" name="go" value="1">' .				 
		     '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
			 '</form></div>';
			 
			 
	   	print_dean_box_end();
	   	
	   	if ($fid <= 22) {
	   		$fid++;
	   		redirect("ldapsyncfac.php?fid=$fid", '', $delay);
	   	}
	}   	
   	
    print_footer();

?>