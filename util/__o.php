<?php


echo 'odbc_connect S28-Server';

$CFG->dbtype    = 'odbc_mssql';     // Note this is different to all the other configs on this page!
$CFG->dbhost    = 's28.bsu.edu.ru';         // Where this matches the Data source name you chose above
$CFG->dbname    = 'DataWarehouse';               // Keep it blank!!
$CFG->dbuser    = 'PegasS28Link';   // I usually use the 'sa' account (dbowner perms are enough)
$CFG->dbpass    = '8412c7788f';
$CFG->dbpersist =  false;
$CFG->prefix    = '';   

/*
$CFG->wwwroot   = 'http://pegas.bsu.edu.ru';
$CFG->dirroot = "/var/www/html/pegas";
$CFG->dataroot  = '/var/www/html/moodledata';
*/
$CFG->wwwroot   = 'http://cdoc06/pegas';
$CFG->dirroot   = 'C:\usr\wwwroot\pegas';
$CFG->dataroot  = 'C:\usr\wwwroot\moodledata';




$CFG->admin     = 'admin';
$CFG->libdir   = $CFG->dirroot .'/lib';


    require_once($CFG->libdir .'/adodb/adodb.inc.php'); // Database access functions

    $db = &ADONewConnection($CFG->dbtype);

    // See MDL-6760 for why this is necessary. In Moodle 1.8, once we start using NULLs properly,
    // we probably want to change this value to ''.
    $db->null2null = 'A long random string that will never, ever match something we want to insert into the database, I hope. \'';

    error_reporting(0);  // Hide errors

    if (!isset($CFG->dbpersist) or !empty($CFG->dbpersist)) {    // Use persistent connection (default)
        $dbconnected = $db->PConnect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
    } else {                                                     // Use single connection
        $dbconnected = $db->Connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
    }
    if (! $dbconnected) {
        // In the name of protocol correctness, monitoring and performance
        // profiling, set the appropriate error headers for machine comsumption
        if (isset($_SERVER['SERVER_PROTOCOL'])) { 
            // Avoid it with cron.php. Note that we assume it's HTTP/1.x
            header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');        
        }
        // and then for human consumption...
        echo '<html><body>';
        echo '<table align="center"><tr>';
        echo '<td style="color:#990000; text-align:center; font-size:large; border-width:1px; '.
             '    border-color:#000000; border-style:solid; border-radius: 20px; border-collapse: collapse; '.
             '    -moz-border-radius: 20px; padding: 15px">';
        echo '<p>Error: Database connection failed.</p>';
        echo '<p>It is possible that the database is overloaded or otherwise not running properly.</p>';
        echo '<p>The site administrator should also check that the database details have been correctly specified in config.php</p>';
        echo '</td></tr></table>';
        echo '</body></html>';
        die;
    } else {
        echo 'OK!!!!!!!!!!';
    }








function one()
{

$conn = odbc_connect("S28-Server", "PegasS28Link", "8412c7788f");

if (!$conn)  {
    echo "Connection Failed: " . $conn;
    exit(0);
}

@set_time_limit(0);

/*
$tables =  array( 'Plans', 'Disciplines', 'EdWorks',
                  'TeachingLoad', 'PlanAndGroupBinding',
                  'Students', 'StaffForm',  'Ref_Department',
                  'Ref_DisciplineName',  'Ref_EdForm', 'Ref_EdWorkKind',
                  'Ref_EdYear', 'Ref_PlanType', 'Ref_Semester',
                  'Ref_Speciality', 'Ref_SubDepartment', 'Ref_TeachingLoadType',
                  'Auditories', 'Schedule');
*/

$tables = array ('Auditories');

foreach ($tables as $table)   {
      $strsql = "SELECT * FROM $table";
      echo $strsql . '<br>';
      $rs = odbc_exec($conn, $strsql);
      if (!$rs) {
            echo "Error in SQL: $strsql";
            exit();
      }
      while ($rec = odbc_fetch_array ($rs)) {
         $newrec = array();
         foreach ($rec as $key => $field)       {
            // $newrec[$key] = utf8_encode($field);
            $newrec[$key] = iconv('windows-1251','utf-8',$field);
        }
        // print_r($newrec); echo '<hr>';
        print_r($rec); echo '<hr>';
      }
      echo $table . " complete.\n";
}


odbc_close($conn);

echo "\nComplete all.\n";
}

