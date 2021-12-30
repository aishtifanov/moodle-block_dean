<?PHP // $Id: ldapedit.php,v 1.5 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $namegroup = optional_param('namegroup', '');		// Group name (number)

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('ldapedit', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");


	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$SITE->shortname: $strsearchgroup", $SITE->fullname, $breadcrumbs);
    

    $currenttab = 'ldapedit';
    include('ldaptabs.php');
    
    if (isset($namegroup) && !empty($namegroup)) 	{    
		$ugroups1 = get_records_sql("SELECT id, lastname, firstname, secondname, group1, group2  
									 FROM {$CFG->prefix}dean_ldap
	    							WHERE group1 LIKE '$namegroup%'
	    							ORDER BY group1, lastname");
		// $fullname = $ugroups1->lastname.' '.$ugroups1->firstname.' '.$ugroups1->secondname;
	}

	
	if ($recs = data_submitted())  {

               foreach($recs as $fieldname => $group1)     {
     
                    if ($group1 != '')     {
                      $mask = substr($fieldname, 0, 4);
                      if ($mask == 'num_')     {
                           $ids = explode('_', $fieldname);
                           $ldapid = $ids[1];
     
                           if (record_exists('dean_ldap', 'id', $ldapid))     {
                               set_field('dean_ldap', 'group1', $group1, 'id', $ldapid);
                               notify(get_string('succesavedata','block_dean'), 'green');
                           } 
                      }
     
                  }
               }   
                      
     }
	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    $searchtext = $namegroup;

    if (isset($namegroup) && !empty($namegroup)) 	{
    	
    	if (check_name_group($namegroup))	{

    	    // $ugroups1 = get_records_sql("SELECT * FROM {$CFG->prefix}dean_ldap WHERE group1 LIKE '$namegroup%' ORDER BY group1, lastname");
    		// $fullname = $ugroups1->lastname.' '.$ugroups1->firstname.' '.$ugroups1->secondname;
    		$ugroups1 = false;
    	//	$tabledata = array();
    		
    		if ($ugroups1)	{

    			$table->head  = array (get_string('login', 'block_dean'), get_string("students","block_dean"),get_string("group","block_dean"));
    			$table->align = array ("center", "left", "center", "center");
    			$table->size = array ('10%', '20%', '10%', '10%');
    			$table->width = '50%';
    
    			foreach ($ugroups1 as $ugroups){
    				$insidetable = "<input type=text  name=num_{$ugroups->id} size=8 value=\"{$ugroups->group1}\">";
    				$fullname = $ugroups->lastname.' '.$ugroups->firstname.' '.$ugroups->secondname;
    				//$insidetable = "input type=text  name=num_{$profile->id} size=30 value=\"$ugroups->group1\">";
    				$table->data[] = array($ugroups->login,$fullname, $insidetable);
    				
    			}
    			  
    			echo  '<form name="searchgroup" method="post" action="ldapedit.php">';
    			print_table($table);
    			echo  '<div align="center">';
    			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
    			echo  '</div></form>';
    		}
    		else {
                if ($idgroup = get_field_select('bsu_ref_groups', 'id', "name = '$namegroup'")) {
                    $usernames = get_records_select_menu('bsu_group_members', "groupid=$idgroup and deleted=0", '', 'id, username');
                    // print_object($usernames);                   
                    $strusers = implode (',', $usernames); 
                    $sql = "CodePhysPerson in ($strusers) and idKodPrith=1";
                    // echo $sql;
                    // $lstudents2 = get_records_select('bsu_students', "grup= '$namegroup' and Dateotsh = ''", 'name', 'CodePhysPerson as login, Name, grup');
                    $ugroups1 = get_records_select('bsu_students', $sql, 'name', 'id, CodePhysPerson as login, Name');
        		    /*  
                    $ugroups1 = get_records_sql("SELECT id, CodePhysPerson as login, Name, grup FROM {$CFG->prefix}bsu_students
        	    							WHERE grup LIKE '$namegroup%' and Dateotsh = ''
        	    							ORDER BY grup, Name");
                    }
                    */                        
        		    if ($ugroups1)	{
        
            			$table->head  = array (get_string('login', 'block_dean'), get_string("students","block_dean"),get_string("group","block_dean"));
            			$table->align = array ("center", "left", "center", "center");
            			$table->size = array ('10%', '20%', '10%', '10%');
            			$table->width = '50%';
            
            			foreach ($ugroups1 as $ugroups){
            				$insidetable = "<input type=text  name=num_{$ugroups->id} size=8 value=\"{$namegroup}\">";
            				$fullname = $ugroups->Name;
            				//$insidetable = "input type=text  name=num_{$profile->id} size=30 value=\"$ugroups->group1\">";
            				$table->data[] = array($ugroups->login,$fullname, $insidetable);
            			}
            			  
            			echo  '<form name="searchgroup" method="post" action="ldapedit.php">';
            			print_table($table);
            			echo  '<div align="center">';
            			// echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
            			echo  '</div></form>';
            		} else {
          			    notify('В группе отсутствуют студенты.');            			
            			echo '<hr>';
                    }
                } else {
                    notify(get_string('groupnotfound','block_dean'));
           			echo '<hr>';
                }        
    		}
		}  else {
			notify('Имя группы должно быть числовым и не меннее 5 цифр.');
		}
	}

	print_heading($strsearchgroup, 'center', 2);
	notify (get_string('description_ldapedit','block_dean'), 'black');

    print_dean_box_start('center', '50%');


	echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapedit.php">'.
		 get_string('numgroup', 'block_dean'). '&nbsp&nbsp'.
		 '<input type="text" name="namegroup" size="10" value="' . $searchtext. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.

		 '</form></div>';


   	print_dean_box_end();
   	
   print_footer();


function check_name_group($namegroup)
{
    $len = strlen ($namegroup);
    
    if (is_numeric($namegroup) && ($len == 6 || $len == 8))    { 
        return true;
   	}
    
    $prefgroup = substr($namegroup, 0, 2);
    $numgroup  = substr($namegroup, 2, 6);
   	if ($prefgroup == 'mc' && is_numeric($numgroup))	{
        return true;
    }

    return false;
}
?>
