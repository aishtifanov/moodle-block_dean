<?php // $Id: __clr_t.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __Clear all T-users";
    print_header("$SITE->shortname: __CLear all T-users", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

  	$k = usercleaner_ondata();
  	
    print($k . ' the DELETED users had been successfully removed.');
    print_continue('__clr_t.php');

	print_footer();
	
	
	
/**
 * Performs the deletion request
 */
function usercleaner_ondata() 
{
        // Gets all the tables where users are referenced
	$tables = usercleaner_get_tables();
        /*
        foreach($tables as $key => $table) {
        	echo $table["TABLE_NAME"] . ' => ' . $table["COLUMN_NAME"] . '<hr>';
        }	
		*/

	$t_groups = get_records_sql("SELECT DISTINCT name FROM mdl_groups where name like '______t'");
	// print_r($t_groups);
	$i = 0;
	foreach ($t_groups as $t_group)	{
		$groups = get_records('groups', 'name', $t_group->name);
		foreach ($groups as $group)	{
			if ($students = get_records('groups_members', 'groupid', $group->id))	{
				foreach ($students as $student)	{
					if ($user = get_record('user', 'id', $student->userid))	{
			        	$userid = $user->id;
			            // Cleans his files from assignments, data, exercise and workshop modules
			            usercleaner_clean_all_files($userid);
			            if ($tables) {
			                // for each table
			                foreach($tables as $key => $table) {
			                    $tablename  = $table["TABLE_NAME"];
			                    $columnname = $table["COLUMN_NAME"];
			                    // deletes the data from the table
			                    usercleaner_delete_fromtable($tablename, $columnname, $userid);
			                }
			            }
			            // deletes the user from the {$CFG->prefix}user table
			            usercleaner_delete($userid);
			            if ($i++ > 500) return $i;
			            echo $userid . '<hr>';
					}					
				}
			}	
		}	
	}
}
	
/**
 * Cleans all files of assignments, data, exercise and wokshop modules
 *
 * @param       integer             $userid     the id of the user to delete his files
 */
function usercleaner_clean_all_files($userid) {
    usercleaner_clean_assignments_files($userid);
    usercleaner_clean_data_files($userid);
    usercleaner_clean_exercise_files($userid);
    usercleaner_clean_workshop_files($userid);
}

/**
 * Cleans all files of assignments
 *
 * @param       integer             $userid     the id of the user to delete his files
 */
function usercleaner_clean_assignments_files($userid) {
    $infos  = usercleaner_get_assignment_infos($userid);
    if ($infos) {
        foreach ($infos as $info) {
            $path = usercleaner_get_assignments_filepath($info);
            fulldelete($path);
        }
    }
}

/**
 * Retrieves the path of the directory containing the files of the user in an assignment
 *
 * @return      array               $infos      a 0 based indexed 2-dimensional array of the recordset containing infos to build the path
 */
function usercleaner_get_assignments_filepath($info) {
    global $CFG;
    $path   = $CFG->dataroot.'/';
    $path  .= $info['course'].'/';
    $path  .= $CFG->moddata.'/';
    $path  .= 'assignment/';
    $path  .= $info['assignment'].'/';
    $path  .= $info['userid'];
    return $path;
}

/**
 * Cleans all files of data
 *
 * @param       integer             $userid     the id of the user to delete his files
 */
function usercleaner_clean_data_files($userid) {
    $infos  = usercleaner_get_data_infos($userid);
    if ($infos) {
        foreach ($infos as $info) {
            $path = usercleaner_get_data_filepath($info);
            fulldelete($path);
        }
    }
}

/**
 * Retrieves the path of the directory containing the files of the user in a data
 *
 * @return      array               $infos      a 0 based indexed 2-dimensional array of the recordset containing infos to build the path
 */
function usercleaner_get_data_filepath($info) {
    global $CFG;
    $path   = $CFG->dataroot.'/';
    $path  .= $info['course'].'/';
    $path  .= $CFG->moddata.'/';
    $path  .= 'data/';
    $path  .= $info['dataid'].'/';
    $path  .= $info['fieldid'].'/';
    $path  .= $info['recordid'];
    return $path;
}

/**
 * Cleans all files of exercises
 *
 * @param       integer             $userid     the id of the user to delete his files
 */
function usercleaner_clean_exercise_files($userid) {
    $infos  = usercleaner_get_exercise_infos($userid);
    if ($infos) {
        foreach ($infos as $info) {
            $path = usercleaner_get_exercise_filepath($info);
            fulldelete($path);
        }
    }
}

/**
 * Retrieves the path of the directory containing the files of the user in an exercise
 *
 * @return      array               $infos      a 0 based indexed 2-dimensional array of the recordset containing infos to build the path
 */
function usercleaner_get_exercise_filepath($info) {
    global $CFG;
    $path   = $CFG->dataroot.'/';
    $path  .= $info['course'].'/';
    $path  .= $CFG->moddata.'/';
    $path  .= 'exercise/';
    $path  .= $info['submission'];
    return $path;
}


/**
 * Cleans all files of workshops
 *
 * @param       integer             $userid     the id of the user to delete his files
 */
function usercleaner_clean_workshop_files($userid) {
    $infos  = usercleaner_get_workshop_infos($userid);
    if ($infos) {
        foreach ($infos as $info) {
            $path = usercleaner_get_workshop_filepath($info);
            fulldelete($path);
        }
    }
}

/**
 * Retrieves the path of the directory containing the files of the user in a workshop
 *
 * @return      array               $infos      a 0 based indexed 2-dimensional array of the recordset containing infos to build the path
 */
function usercleaner_get_workshop_filepath($info) {
    global $CFG;
    $path   = $CFG->dataroot.'/';
    $path  .= $info['course'].'/';
    $path  .= $CFG->moddata.'/';
    $path  .= 'workshop/';
    $path  .= $info['submission'];
    return $path;
}


/**
 * Returns all the table and column names where it exists a column which name inlcudes the string "userid"
 *
 * @uses        $CFG
 * @uses        $db
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function &usercleaner_get_tables() {
    global $CFG, $db;
    $sql    = "SELECT TABLE_NAME, COLUMN_NAME 
               FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE COLUMN_NAME LIKE '%userid%' 
               AND TABLE_SCHEMA = '$CFG->dbname'";
    return $db->GetArray($sql);
}


/**
 * Removes records where the id of a user is referenced
 * 
 * @uses        $db
 * @param       string              $table          the name of the table to search in
 * @param       string              $columnname     the name of the column corresponding to the user id
 * @param       integer             $userid         the id of the user to delete
 * @return      RecordSet|false     the record removed or false
 */
function usercleaner_delete_fromtable($table, $columnname, $userid) {
	global $db;
    $sql    = 'DELETE FROM '. $table .' WHERE '. $columnname .'='. $userid;
	return $db->Execute($sql);
}


/**
 * Removes a user from the {$CFG->prefix}user table
 * 
 * @param       integer             $userid         the id of the user to delete
 * @return      object              A PHP standard object with the results from the SQL call
 */
function usercleaner_delete($userid) {
	return delete_records_select('user', 'id='. $userid);
}

/**
 * Retrieve assignments infos for a particular user
 * 
 * @uses        $CFG
 * @uses        $db
 * @param       integer             $userid         the id of the user to look for records
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function usercleaner_get_assignment_infos($userid) {
    global $CFG, $db;
    $sql    = "SELECT assub.userid, ass.id as assignment, ass.course 
               FROM {$CFG->prefix}assignment ass, {$CFG->prefix}assignment_submissions assub 
               WHERE assub.userid=$userid 
               AND assub.assignment=ass.id";

    return $db->GetArray($sql);
}

/**
 * Retrieve data infos for a particular user
 * 
 * @uses        $CFG
 * @uses        $db
 * @param       integer             $userid         the id of the user to look for records
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function usercleaner_get_data_infos($userid) {
    global $CFG, $db;
    $sql    = "SELECT dr.userid, d.id as dataid, d.course, dc.fieldid, dc.recordid  
               FROM {$CFG->prefix}data_content dc, {$CFG->prefix}data d, {$CFG->prefix}data_records dr 
               WHERE dr.userid = $userid 
               AND d.id = dr.dataid  
               AND dr.id = dc.recordid";
    return $db->GetArray($sql); 
}

/**
 * Retrieve exercises infos for a particular user
 * 
 * @uses        $CFG
 * @uses        $db
 * @param       integer             $userid         the id of the user to look for records
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function usercleaner_get_exercise_infos($userid) {
    global $CFG, $db;
    $sql    = "SELECT es.userid, e.id as exercise, e.course, es.id as submission 
               FROM {$CFG->prefix}exercise_submissions es, {$CFG->prefix}exercise e 
               WHERE es.userid = $userid 
               AND es.exerciseid = e.id";
    return $db->GetArray($sql);
}

/**
 * Retrieve workshop infos for a particular user
 * 
 * @uses        $CFG
 * @uses        $db
 * @param       integer             $userid         the id of the user to look for records
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function usercleaner_get_workshop_infos($userid) {    
    global $CFG, $db;
    $sql    = "SELECT ws.userid, w.id as workshop, w.course, ws.id as submission 
               FROM {$CFG->prefix}workshop_submissions ws, {$CFG->prefix}workshop w 
               WHERE ws.userid = $userid 
               AND ws.workshopid = w.id";

    return $db->GetArray($sql);
}

/**
 * Retrieve forums infos for a particular user
 * 
 * @uses        $CFG
 * @uses        $db
 * @param       integer             $userid         the id of the user to look for records
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function usercleaner_get_forum_infos($userid) {
    global $CFG, $db;
    $sql    = "SELECT fp.userid, fd.forum, fd.course, fp.id as postid  
               FROM {$CFG->prefix}forum_posts fp, {$CFG->prefix}forum_discussions fd 
               WHERE fp.userid = $userid  
               AND fp.discussion = fd.id";

    return $db->GetArray($sql);
}

/**
 * Retrieve glossaries infos for a particular user
 * 
 * @uses        $CFG
 * @uses        $db
 * @param       integer             $userid         the id of the user to look for records
 * @return      array               a 0 based indexed 2-dimensional array of the recordset
 */
function usercleaner_get_glossary_infos($userid) {
    global $CFG, $db;
    $sql    = "SELECT ge.userid, g.id as glossaryid, g.course, ge.id as entryid
               FROM {$CFG->prefix}glossary_entries ge, {$CFG->prefix}glossary g 
               WHERE ge.userid = $userid  
               AND ge.glossaryid = g.id";

    return $db->GetArray($sql);
}


?>


