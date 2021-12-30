<?php // $Id: __clr_t.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
	 

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __Clear users list";
    print_header("$SITE->shortname: __CLear users list", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	$user777 = array (41116,41144,41450,41171,6286,43185,41120,41148,41175,41454,41122,41150,41177,41456,41127,41155,41182,41461,31710,41140,41167,41446,41130,41158,41464,41121,41149,41176,41455,41126,41154,41181,41460,31768,41110,41138,41165,41444,41129,41157,41184,41463,41107,41135,41162,41441,41119,41174,41440,41106,41161,41134,40523,41108,41136,41163,41442,41118,41146,41173,41452,41128,41156,41183,41462,31440,41125,41153,41180,41459,41467,41111,41139,41166,41445,41115,41143,41170,41449,41114,41142,41169,41448,41117,41145,41172,41451,41105,31447,41132,41160,41187,41466,41113,41141,41168,41447,41131,41159,41186,41465,41109,41137,41164,41443,41123,41151,41178,41457,41124,41152,41179,41458,31680,49499,47693,48471,47541,47217,48528,52714,48472,48967);



	foreach ($user777 as $userid)	{
		if ($user = get_record('user', 'id', $userid))	{
			echo fullname($user) . "=> $user->id <br>"; 
		}
	}		
	
  	usercleaner_ondata($user777);
  	
    print('Completed.');

	print_footer();
	

	
/**
 * Performs the deletion request
 */
function usercleaner_ondata($userids) 
{
    // Gets all the tables where users are referenced
	$tables = usercleaner_get_tables();

	foreach ($userids as $userid)	{
		if ($user = get_record('user', 'id', $userid))	{
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
            // if ($i++ > 500) return $i;
            echo fullname($user) . "=> $user->id <hr>"; 
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


