<?php // $Id: lib_search.php,v 1.1 2011/12/19 12:35:44 shtifanov Exp $



function add_student_to_dean_group($userid, $academygroup)
{
    global $CFG, $USER, $admin_is, $fid, $sid, $cid;
    
    $user = get_record_sql ("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$userid");

    $link = "{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid";
    
    if (!$admin_is)		{
	   if ($deanstud = get_record('dean_academygroups_members', 'userid', $userid))	 {
    	   $gruppa = get_record_sql("SELECT id, name FROM {$CFG->prefix}dean_academygroups
    	                             WHERE id={$deanstud->academygroupid}");
           $fn = fullname ($user);                                  
           notify("Студент $fn уже зарегистрирован в группе <a href=\"$link&gid={$deanstud->academygroupid}\">$gruppa->name</a>");
           return false;
      }
    }

    $rec->academygroupid = $academygroup->id;
	$rec->timeadded = time();
    $rec->userid = $userid;
    if (insert_record('dean_academygroups_members', $rec))	{
        add_to_log(1, 'dean', 'one student added in academygroup', "/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$academygroup->id", $USER->lastname.' '.$USER->firstname);
    } else  {
        error(get_string('errorinaddinggroupmember','block_dean'), "$link&gid=$academygroup->id");
    }
    // Get groups (moodle) in course with equal name
    if ($mgroups = get_records("groups", "name", $academygroup->name))  {
        foreach($mgroups as $mgroup)  {
            if (!enrol_student_dean($rec->userid, $mgroup->courseid)) {
                error("Could not add student with id $rec->userid to this course $mgroup->courseid!");
            }
    
            /// Delete duplicated students in the other group
            /*
            $strsql = "SELECT g.id, g.name, g.courseid, m.userid
            	   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id = m.groupid
            	   WHERE (g.courseid={$mgroup->courseid}) AND (m.userid={$rec->userid})";
            if ($duplgroups = get_records_sql($strsql))	{
            foreach ($duplgroups as $duplgroup)	 {
            	 delete_records('groups_members', 'groupid', $duplgroup->id, 'userid', $rec->userid);
            }
            }
            */
    
            $record->groupid = $mgroup->id;
            $record->userid = $rec->userid;
            $record->timeadded = time();
            if (!insert_record('groups_members', $record)) {
                notify("Error occurred while adding user $rec->userid to group $mgroup->id");
            }
        } //foreach
    } //if
    
    return true;
}                


function restore_user_account($uid_g)
{
    global $CFG;
    
    // берем запись студента в таблице удаленных
    if($usergrad = get_record_select('dean_user_graduates', "id = $uid_g"))    {
        // echo '<pre>'; print_r($usergrad); echo '</  pre>'; 
        // если запись ещё существует в таблице пользователей, тогда изменяем её ( с проверко Ф.И.О ???)
        if ($user = get_record_select('user', "id = $usergrad->userid", 'id'))   {
            // если запись студента в таблице удаленных искажена, тогда восстанавливаем её
            if ($usergrad->deleted)    {
                list($login, $suffix) = explode ('@', $usergrad->username);
                $usergrad->username = $login;
                $usergrad->deleted = 0; 
            }
            $usergrad->id = $usergrad->userid;
            $fn = fullname($usergrad);            
            // проверяем существование пользователя с таким же логином
            if ($userexists = get_record_select('user', "username = $usergrad->username and deleted=0", 'id, lastname, firstname'))   {
                $fn2 = fullname($userexists);
                notify ("Учетная карточка $fn не восстановлена,<br> т.к. пользователь с логином $usergrad->username уже существует: $fn2.");
                return false;
            } else {    
                if (update_record('user', $usergrad))   {
                    notify ("Учетная карточка $fn успешно восстановлена.", 'green');
                }  else {
                    notify ("Учетная карточка $fn не восстановлена (update_record).");
                    return false;
                }
            }    
        } else { // иначе запись добавляем в таблицу пользователей
            $usergrad->id = $usergrad->userid;
            if (!$newid = insert_record_att('user', $usergrad))  {
                notify ("Учетная карточка $fn не восстановлена (insert_record_att).");
                return false;
            } else {
                notify ("Учетная карточка $fn успешно восстановлена (insert).", 'green');
            }
        }    
        delete_records_select('dean_user_graduates', "id = $uid_g");
    } else {
        return false;
    }

    return $usergrad->userid;    
}


function restore_dean_student($userid, $agroupanother=0)
{
    if (!$user = get_record_select('user', "id = $userid and deleted=0", 'id, lastname, firstname'))   {
        notify ("Учетная карточка с id = $userid не восстановлена.");
        return false;
    }    
    
    $fn = fullname($user);
        
    // смотрим в каких группах числился студент
    if ($amembers = get_records_select ('dean_academygroups_members_g', "userid=$userid", 'id', 'id, userid, academygroupid'))  {
        foreach ($amembers as $amember) {
            if ($agroup_g = get_record_select('dean_academygroups_g', "id = $amember->academygroupid", 'id, name'))   {
                if ($agroupanother) {
                    if (add_student_to_dean_group($userid, $agroupanother))    {
                        notify ("Студент $fn восстановлен в группе $agroupanother->name.", 'green');
                    }
                        // если группа ещё существует в деканате, то включаем студента в эту группу
                } else  if ($agroup = get_record_select('dean_academygroups', "name = '$agroup_g->name'", 'id, name'))   {
                    if (add_student_to_dean_group($userid, $agroup))    {
                        notify ("Студент $fn восстановлен в группе $agroup_g->name.", 'green');
                    }
                } else {
                    notify ("Группа $agroup_g->name не найдена. Студент $fn не может быть восстановлен в эту группу.");
                    return false;
                }   
            } else {
                notify ("Группа не найдена в списке удаленных групп.");
            }
        }
    } else {
        notify ("Студент $fn не числился ни в одной группе.");
    }
    
    return true;    
}



function insert_record_att($table, $dataobject, $returnid=true, $primarykey='id', $clearprimarykey = false) {

    global $db, $CFG, $empty_rs_cache;

    if (empty($db)) {
        return false;
    }

/// Check we are handling a proper $dataobject
    if (is_array($dataobject)) {
        debugging('Warning. Wrong call to insert_record(). $dataobject must be an object. array found instead', DEBUG_DEVELOPER);
        $dataobject = (object)$dataobject;
    }

/// Temporary hack as part of phasing out all access to obsolete user tables  XXX
    if (!empty($CFG->rolesactive)) {
        if (in_array($table, array('user_students', 'user_teachers', 'user_coursecreators', 'user_admins'))) {
            if (debugging()) { var_dump(debug_backtrace()); }
            error('This SQL relies on obsolete tables ('.$table.')!  Your code must be fixed by a developer.');
        }
    }

    if (defined('MDL_PERFDB')) { global $PERF ; $PERF->dbqueries++; };

/// In Moodle we always use auto-numbering fields for the primary key
/// so let's unset it now before it causes any trouble later
    if ($clearprimarykey)   {
        unset($dataobject->{$primarykey});    
    }

/// Get an empty recordset. Cache for multiple inserts.
    if (empty($empty_rs_cache[$table])) {
        /// Execute a dummy query to get an empty recordset
        if (!$empty_rs_cache[$table] = $db->Execute('SELECT * FROM '. $CFG->prefix . $table .' WHERE '. $primarykey  .' = \'-1\'')) {
            return false;
        }
    }

    $rs = $empty_rs_cache[$table];

/// Postgres doesn't have the concept of primary key built in
/// and will return the OID which isn't what we want.
/// The efficient and transaction-safe strategy is to
/// move the sequence forward first, and make the insert
/// with an explicit id.
    if ( $CFG->dbfamily === 'postgres' && $returnid == true ) {
        if ($nextval = (int)get_field_sql("SELECT NEXTVAL('{$CFG->prefix}{$table}_{$primarykey}_seq')")) {
            $dataobject->{$primarykey} = $nextval;
        }
    }

/// Begin DIRTY HACK
    if ($CFG->dbfamily == 'oracle') {
        oracle_dirty_hack($table, $dataobject); // Convert object to the correct "empty" values for Oracle DB
    }
/// End DIRTY HACK

/// Under Oracle, MSSQL and PostgreSQL we have our own insert record process
/// detect all the clob/blob fields and change their contents to @#CLOB#@ and @#BLOB#@
/// saving them into $foundclobs and $foundblobs [$fieldname]->contents
/// Same for mssql (only processing blobs - image fields)
    if ($CFG->dbfamily == 'oracle' || $CFG->dbfamily == 'mssql' || $CFG->dbfamily == 'postgres') {
        $foundclobs = array();
        $foundblobs = array();
        db_detect_lobs($table, $dataobject, $foundclobs, $foundblobs);
    }

/// Under Oracle, if the primary key inserted has been requested OR
/// if there are LOBs to insert, we calculate the next value via
/// explicit query to the sequence.
/// Else, the pre-insert trigger will do the job, because the primary
/// key isn't needed at all by the rest of PHP code
    if ($CFG->dbfamily === 'oracle' && ($returnid == true || !empty($foundclobs) || !empty($foundblobs))) {
    /// We need this here (move this function to dmlib?)
        include_once($CFG->libdir . '/ddllib.php');
        $xmldb_table = new XMLDBTable($table);
        $seqname = find_sequence_name($xmldb_table);
        if (!$seqname) {
        /// Fallback, seqname not found, something is wrong. Inform and use the alternative getNameForObject() method
            debugging('Sequence name for table ' . $table->getName() . ' not found', DEBUG_DEVELOPER);
            $generator = new XMLDBoci8po();
            $generator->setPrefix($CFG->prefix);
            $seqname = $generator->getNameForObject($table, $primarykey, 'seq');
        }
        if ($nextval = (int)$db->GenID($seqname)) {
            $dataobject->{$primarykey} = $nextval;
        } else {
            debugging('Not able to get value from sequence ' . $seqname, DEBUG_DEVELOPER);
        }
    }

/// Get the correct SQL from adoDB
    if (!$insertSQL = $db->GetInsertSQL($rs, (array)$dataobject, true)) {
        return false;
    }

/// Under Oracle, MSSQL and PostgreSQL, replace all the '@#CLOB#@' and '@#BLOB#@' ocurrences to proper default values
/// if we know we have some of them in the query
    if (($CFG->dbfamily == 'oracle' || $CFG->dbfamily == 'mssql' || $CFG->dbfamily == 'postgres') &&
      (!empty($foundclobs) || !empty($foundblobs))) {
    /// Initial configuration, based on DB
        switch ($CFG->dbfamily) {
            case 'oracle':
                $clobdefault = 'empty_clob()'; //Value of empty default clobs for this DB
                $blobdefault = 'empty_blob()'; //Value of empty default blobs for this DB
                break;
            case 'mssql':
            case 'postgres':
                $clobdefault = 'null'; //Value of empty default clobs for this DB (under mssql this won't be executed
                $blobdefault = 'null'; //Value of empty default blobs for this DB
                break;
        }
        $insertSQL = str_replace("'@#CLOB#@'", $clobdefault, $insertSQL);
        $insertSQL = str_replace("'@#BLOB#@'", $blobdefault, $insertSQL);
    }

/// Run the SQL statement
    if (!$rs = $db->Execute($insertSQL)) {
        debugging($db->ErrorMsg() .'<br /><br />'.s($insertSQL));
        if (!empty($CFG->dblogerror)) {
            $debug=array_shift(debug_backtrace());
            error_log("SQL ".$db->ErrorMsg()." in {$debug['file']} on line {$debug['line']}. STATEMENT:  $insertSQL");
        }
        return false;
    }

/// Under Oracle and PostgreSQL, finally, update all the Clobs and Blobs present in the record
/// if we know we have some of them in the query
    if (($CFG->dbfamily == 'oracle' || $CFG->dbfamily == 'postgres') &&
      !empty($dataobject->{$primarykey}) &&
      (!empty($foundclobs) || !empty($foundblobs))) {
        if (!db_update_lobs($table, $dataobject->{$primarykey}, $foundclobs, $foundblobs)) {
            return false; //Some error happened while updating LOBs
        }
    }

/// If a return ID is not needed then just return true now (but not in MSSQL DBs, where we may have some pending tasks)
    if (!$returnid && $CFG->dbfamily != 'mssql') {
        return true;
    }

/// We already know the record PK if it's been passed explicitly,
/// or if we've retrieved it from a sequence (Postgres and Oracle).
    if (!empty($dataobject->{$primarykey})) {
        return $dataobject->{$primarykey};
    }

/// This only gets triggered with MySQL and MSQL databases
/// however we have some postgres fallback in case we failed
/// to find the sequence.
    $id = $db->Insert_ID();

/// Under MSSQL all the Clobs and Blobs (IMAGE) present in the record
/// if we know we have some of them in the query
    if (($CFG->dbfamily == 'mssql') &&
      !empty($id) &&
      (!empty($foundclobs) || !empty($foundblobs))) {
        if (!db_update_lobs($table, $id, $foundclobs, $foundblobs)) {
            return false; //Some error happened while updating LOBs
        }
    }

    if ($CFG->dbfamily === 'postgres') {
        // try to get the primary key based on id
        if ( ($rs = $db->Execute('SELECT '. $primarykey .' FROM '. $CFG->prefix . $table .' WHERE oid = '. $id))
             && ($rs->RecordCount() == 1) ) {
            trigger_error("Retrieved $primarykey from oid on table $table because we could not find the sequence.");
            return (integer)reset($rs->fields);
        }
        trigger_error('Failed to retrieve primary key after insert: SELECT '. $primarykey .
                      ' FROM '. $CFG->prefix . $table .' WHERE oid = '. $id);
        return false;
    }

    return (integer)$id;
}


?>
