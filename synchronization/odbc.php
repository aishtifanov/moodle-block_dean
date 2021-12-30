<?php

  define('DB_SERVER', 'localhost');
  define('DB_SERVER_USERNAME', 'root');
  define('DB_SERVER_PASSWORD', '');
  define('DB_DATABASE', 'rating');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', 'mysql');
  
  require('database.php');
  
  $conn = odbc_connect("S28-Server", "PegasS28Link", "8412c7788f");
    
  if (!$conn)  {
      exit("Connection Failed: " . $conn);
  }

  tep_db_connect() or die('Unable to connect to database server!');
  
  /*
  $tables =  array('Plans', 'Disciplines', 'EdWorks', 'TeachingLoad', 'PlanAndGroupBinding', 'Students', 'StaffForm',  
                   'Ref_Department', 'Ref_DisciplineName', 'Ref_EdForm', 'Ref_EdWorkKind', 'Ref_EdYear', 
                   'Ref_PlanType', 'Ref_Semester', 'Ref_Speciality', 'Ref_SubDepartment', 'Ref_TeachingLoadType');
  */                  
  $tables =  array('Plans', 'Disciplines');
  
  foreach ($tables as $table)   {
      $strsql = "SELECT * FROM $table";
      $rs = odbc_exec($conn, $strsql);
      if (!$rs) {
          exit("Error in SQL: $strsql");
      }
      $mdltable = 'mdl_bsu_' . strtolower ($table);
      tep_db_query("TRUNCATE TABLE  $mdltable");
      while ($rec = odbc_fetch_array ($rs)) {
            tep_db_perform($mdltable, $rec);          
      }
      echo $table . 'complete.<br>';
  }
    
  odbc_close($conn);
  tep_db_close();

  echo 'Complete';     
?>
