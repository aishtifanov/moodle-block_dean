<?php //$Id: __u16.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

///dummy field names are used to help adding and dropping indexes. There's only 1 case now, in scorm_scoes_track

    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/environmentlib.php');
    require_once($CFG->dirroot.'/course/lib.php');
    require_login();


	/**************************************
	 * End custom lang pack handling      *
	 **************************************/

    if (!isadmin()) {
        error('Only admins can access this page');
    }

    if (!$site = get_site()) {
        redirect('index.php');
    }


    $textlib = textlib_get_instance();

    $stradministration   = get_string('administration');
    $strdbmigrate = get_string('dbmigrate','admin');

    $filename = $CFG->dataroot.'/'.SITEID.'/maintenance.html';    //maintenance file

    print_header("$site->shortname: $stradministration", "$site->fullname",
                 '<a href="index.php">'. "$stradministration</a> -> $strdbmigrate");

    print_heading("rebuild_course_cache");
    
    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
                    
    for ($i=30; $i<=31; $i++)   {
        $modulesids = get_field_select('course_sections', sequence, "course = $i");
        set_field_select('course_modules', 'visible', 0, "id in ($modulesids)");
        rebuild_course_cache($i);
        notify($i);
    }    
/* 
	$courses = get_records("course");
	$i = 1;
  	foreach ($courses as $course)	{
  	     echo $course->fullname . '<hr>';
  	     $url = $course->modinfo;
  	     $mis = unserialize ($url);
  	     // print_r($mis);
  	     foreach ($mis as $key => $mi)	{
  	     	 if (!empty($mis[$key]->name))	{
			         $win1251 = urldecode($mis[$key]->name);
				      // echo $win1251 . '<hr>';
				      $utf8 = $textlib->convert($win1251, 'win1251');
 	     		     $mis[$key]->name = urlencode($utf8);		
  	     	}
  	     	if (!empty($mis[$key]->extra))	{
  			    $win1251 = urldecode($mis[$key]->extra);
    				// echo $win1251 . '<b>EXTRA</b><hr>';
    				$utf8 = $textlib->convert($win1251, 'win1251');
   	     		$mis[$key]->extra = urlencode($utf8);		
  	     	}
  	     }
  	     $modinfo = serialize ($mis);
  	     // echo $modinfo . '<hr>';
  	     // if ($i++ > 2) break;
      	 if (!set_field("course", "modinfo", $modinfo, "id", $course->id)) {
                notify("Could not cache module information for course '" . format_string($course->fullname) . "'!");
         }
  	}

    //update site language
    $sitelanguage = get_record('config', 'name', 'lang');
    $sitelanguage->value ='ru_utf8';
    migrate2utf8_update_record('config', $sitelanguage);

    //set the final flag
    migrate2utf8_set_config('unicodedb', 'true');    //this is the main flag for unicode db

    $sqlstr = "UPDATE {$CFG->prefix}user SET lang='ru_utf8'";
    execute_sql($sqlstr);
     //echo date("H:i:s");
*/
    print_footer();

/* returns the course lang
 * @param int courseid
 * @return string
 */
function get_course_lang($courseid) {

    static $coursecache;

    if (!isset($coursecache[$courseid])) {
        if ($course = get_record('course','id',$courseid)){
            $coursecache[$courseid] = $course->lang;
            return $course->lang;
        }
        return false;
    } else {
        return $coursecache[$courseid];
    }
}

/* returns the teacher's lang
 * @param int courseid
 * @return string
 */
function get_main_teacher_lang($courseid) {
    //editting teacher > non editting teacher
    global $CFG;
    static $mainteachercache;

    if (!isset($mainteachercache[$courseid])) {
        $SQL = 'SELECT u.lang from '.$CFG->prefix.'user_teachers ut,
               '.$CFG->prefix.'course c,
               '.$CFG->prefix.'user u WHERE
               c.id = ut.course AND ut.course = '.$courseid.' AND u.id = ut.userid ORDER BY ut.authority ASC';

        if ($teacher = get_record_sql($SQL, true)) {
            $mainteachercache[$courseid] = $teacher->lang;
            return $teacher->lang;
        } else {
            $admin = get_admin();
            $mainteachercache[$courseid] = $admin->lang;
            return $admin->lang;
        }
    } else {
        return $mainteachercache[$courseid];
    }
}

function get_original_encoding($sitelang, $courselang, $userlang){

    global $CFG, $enc;

    $lang = '';
    if ($courselang) {
        $lang = $courselang;
    }
    else if ($userlang) {
        $lang = $userlang;
    }
    else if ($sitelang) {
        $lang = $sitelang;
    }
    else {
        error ('no language found!');
    }

    if ($enc[$lang]) {
        return $enc[$lang];
    } else {
        notify ('unknown language detected: '.$lang);
        return false;
    }
}

/* returns the user's lang
 * @param int userid
 * @return string
 */
function get_user_lang($userid) {

    static $usercache;

    if (!isset($usercache[$userid])) {
        if ($user = get_record('user','id',$userid)) {
            $usercache[$userid] = $user->lang;
            return $user->lang;
        }
    } else {
        return $usercache[$userid];
    }
    return false;
}

// a placeholder for now
function log_the_problem_somewhere() {  //Eloy: Nice function, perhaps we could use it, perhpas no. :-)
    global $CFG, $dbtablename, $fieldname, $record;
    if ($CFG->debug>7) {
        echo "<br />Problem converting: $dbtablename -> $fieldname -> {$record->id}!";
    }
}

// only this function should be used during db migraton, because of addslashes at the end of the convertion
function utfconvert($string, $enc, $slash=true) {
    global $textlib;
    if ($result = $textlib->convert($string, $enc)) {
        if ($slash) {
            $result = addslashes($result);
        }
    }
    return $result;
}

function validate_form(&$form, &$err) {
    global $CFG;

    $newdb = &ADONewConnection('postgres7');
    error_reporting(0);  // Hide errors
    $dbconnected = $newdb->Connect($form->dbhost,$form->dbuser,$form->dbpass,$form->dbname);
    error_reporting($CFG->debug);  // Show errors
    if (!$dbconnected) {
        $err['dbconnect'] = get_string('dbmigrateconnecerror', 'admin');
        return;
    }

    if (!is_postgres_utf8($newdb)) {
        $encoding = $newdb->GetOne('SHOW server_encoding');
        $err['dbconnect'] = get_string('dbmigrateencodingerror', 'admin', $encoding);
        return;
    }

    if (!empty($form->pathtopgdump) && !is_executable($form->pathtopgdump)) {
        $err['pathtopgdump'] = get_string('pathtopgdumpinvalid','admin');
        return;
    }

    if (!empty($form->pathtopsql) && !is_executable($form->pathtopsql)) {
        $err['pathtopsql'] = get_string('pathtopsqlinvalid','admin');
        return;
    }

    return;
}

function is_postgres_utf8($thedb = null) {
    if ($thedb === null) {
        $thedb = &$GLOBALS['db'];
    }

    $db_encoding_postgres = $thedb->GetOne('SHOW server_encoding');
    if (strtoupper($db_encoding_postgres) == 'UNICODE' || strtoupper($db_encoding_postgres) == 'UTF8') {
        return true;
    } else {
        return false;
    }
}

function &get_postgres_db() {
    static $postgres_db;

    if (!$postgres_db) {
        if (is_postgres_utf8()) {
            $postgres_db = &$GLOBALS['db'];
        } else {
            $postgres_db = &ADONewConnection('postgres7');
            $postgres_db->Connect($_SESSION['newpostgresdb']->dbhost,$_SESSION['newpostgresdb']->dbuser,$_SESSION['newpostgresdb']->dbpass,$_SESSION['newpostgresdb']->dbname);
        }
    }

    return $postgres_db;
}

function is_postgres_setup() {
    $postgres_db = &get_postgres_db();

    return $GLOBALS['db']->MetaTables() == $postgres_db->MetaTables();
}

function migrate2utf8_update_record($table,$record) {
    global $CFG;

    if ($CFG->dbtype == 'mysql') {
        update_record($table,$record);
    } else {
        $backup_db = $GLOBALS['db'];
        $GLOBALS['db'] = &get_postgres_db();
        global $in;
        $in = true;
        update_record($table,$record);
        $GLOBALS['db'] = $backup_db;
    }
}

function migrate2utf8_set_config($name, $value, $plugin=NULL) {
    global $CFG;
    if ($CFG->dbtype == 'mysql') {
        set_config($name, $value, $plugin);
    } else {
        $backup_db = $GLOBALS['db'];
        $GLOBALS['db'] = &get_postgres_db();
        set_config($name, $value, $plugin);
        $GLOBALS['db'] = $backup_db;
    }
}

// this needs to print an error when a mod does not have a migrate2utf8.xml
function utf_get_xml ($mode=0) { // if mode is 1, do not perform check for script validity
    global $CFG;

    $xmls = array();
    $noscript = 0; // we assume all mod and all blocks have migration scripts

    /*****************************************************************************
     * traverse order is mod->backup->block->block_plugin->enroll_plugin->global *
     *****************************************************************************/

    ///mod
    if (!$mods = get_list_of_plugins('mod')) {
        error('No modules installed!');
    }

    foreach ($mods as $mod){
        if (file_exists($CFG->dirroot.'/mod/'.$mod.'/db/migrate2utf8.xml')) {
            $xmls[] = xmlize(file_get_contents($CFG->dirroot.'/mod/'.$mod.'/db/migrate2utf8.xml'));
        } else if (!$mode) {
            $noscript = 1;
            notify('warning, there is no migration script detected for this module - '.$mod);
        }
    }

    ///Backups
    $xmls[] = xmlize(file_get_contents($CFG->dirroot.'/backup/db/migrate2utf8.xml'));

    ///Blocks
    $xmls[] = xmlize(file_get_contents($CFG->dirroot.'/blocks/db/migrate2utf8.xml'));

    ///Block Plugins
    if (!$blocks = get_list_of_plugins('blocks')) {
        //error('No blocks installed!');    //Eloy: Is this a cause to stop?
    }

    foreach ($blocks as $block){
        if (file_exists($CFG->dirroot.'/blocks/'.$block.'/db/migrate2utf8.xml')) {
            $xmls[] = xmlize(file_get_contents($CFG->dirroot.'/blocks/'.$block.'/db/migrate2utf8.xml'));
        } else if (!$mode) {
            if (file_exists($CFG->dirroot.'/blocks/'.$block.'/db/mysql.sql') && filesize($CFG->dirroot.'/blocks/'.$block.'/db/mysql.sql')) { // if no migration script, and have db script, we are in trouble
                notify('warning, there is no migration script detected for this block - '.$block);
                $noscript = 1;
            }
        }
    }

    ///Enrol

    if (!$enrols = get_list_of_plugins('enrol')) {
        //error('No enrol installed!');   //Eloy: enrol, not blocks :-) Is this a cause to stop?
    }

    foreach ($enrols as $enrol){
        if (file_exists($CFG->dirroot.'/enrol/'.$enrol.'/db/migrate2utf8.xml')) {
           $xmls[] = xmlize(file_get_contents($CFG->dirroot.'/enrol/'.$enrol.'/db/migrate2utf8.xml'));
        }
    }

    ///Lastly, globals

    $xmls[] = xmlize(file_get_contents($CFG->dirroot.'/lib/db/migrate2utf8.xml'));

    if ($noscript) {
        notify ('Some of your modules or Blocks do not have a migration script. It is very likely that these are contrib modules. If your Moodle site uses non-UTF8 language packs and non-en language packs, data inside these moduels or blocks will not be displayed correctly after the migration. Please proceed with caution.');
    }

    return $xmls;

}
