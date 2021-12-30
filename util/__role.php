<?PHP // $Id: 
	require_once('../../config.php');

	$strtitle = '__ Убираем лишние роли из Пегаса.';

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

    update_and_delete_bad_role();
  
    print_footer();
    


function update_and_delete_bad_role()
{
    $sql = 'select shortname, id from mdl_role';
    $roles = get_records_sql_menu($sql);
    // print_object($roles);
    foreach ($roles as  $rolename => $roleid)   {
        if ($roleid < 20) continue;
        
        list ($badrole, $number) = explode('_', $rolename);
        // print $roles["$badrole"]. '<br />'; // $badrole 
        $wroleid = 0;
        if (!empty($number))    {
            if (isset($roles[$badrole]))    {
                $wroleid = $roles[$badrole];
            }
        }
        
        if ($wroleid == 0)  {
            notify ('Роль не найдена :' .  $rolename);
        } else {
            if (change_roleid($roleid, $wroleid))   {
                delete_records('role_allow_assign', 'allowassign', $roleid);
                print "delete_records role_allow_assign allowassign:$roleid<br />";
                delete_records('role_allow_override', 'allowoverride', $roleid);
                print "delete_records role_allow_override allowoverride:$roleid<br />";
                delete_records_select('role_capabilities', "roleid=$roleid");
                print "delete_records role_capabilities:$roleid<br />";
                delete_records_select('role', "id=$roleid");                
                print "delete_records role:$roleid<br />";
            } 
            // delete FROM mdl_role_capabilities where roleid>=20;
            // delete FROM mdl_role where id>=20;
        }
    }
}        


    
function change_roleid($fromid, $toid)
{
    global $CFG;
    
    $status = true;

    $tables = array('role_allow_assign', 'role_allow_override', 'role_names', 'role_sortorder', 'role_assignments');
    
    foreach ($tables as $table) {
        /*
        if ($table == 'role_allow_assign')  {
             if (set_field_select($table, 'allowassign', $toid, "allowassign=$fromid"))   {
                print "$fromid  ==> $toid : $table<br />";
             }   
        } else if ($table == 'role_allow_override')  {
             if (set_field_select($table, 'allowoverride', $toid, "allowoverride=$fromid"))   {
                print "$fromid  ==> $toid : $table<br />";
             } 
        }       
        */
         if (set_field_select($table, 'roleid', $toid, "roleid=$fromid"))   {
            print "$fromid  ==> $toid : $table<br />";
         } else {
            notify ("Найдены незамененные записи roleid = $fromid в таблице $table!");
            if ($table == 'role_assignments')   {
                $sql = "SELECT distinct  contextid as id1, contextid as id2 FROM  mdl_role_assignments where roleid = $fromid";
                if ($ctxids = get_records_sql_menu($sql))   {
                    $sql = "SELECT distinct userid as id1, userid as id2 FROM mdl_role_assignments where roleid = $fromid";
                    if ($userids = get_records_sql_menu($sql))   {
                        $strctxids = implode (',', $ctxids);
                        $struserids = implode (',', $userids);
                        $sql = "delete FROM mdl_role_assignments 
                                where roleid = $toid  and contextid in ($strctxids) and userid in ($struserids)";
                        print $sql . '<br />';          
                        delete_records_select('role_assignments', "roleid = $toid  and contextid in ($strctxids) and userid in ($struserids)");        
                        if (set_field_select('role_assignments', 'roleid', $toid, "roleid=$fromid"))   {
                            print "!!! $fromid  ==> $toid : $table<br />";
                        }
                    }
                }
            }                
         }
    }
    
  
    foreach ($tables as $table) {
        if (record_exists_select($table, "roleid = $fromid"))  {
            $status = false;
        }
    }    
    
    return $status; 
}

?>