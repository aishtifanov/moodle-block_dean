<?php // $Id: examcompare.php,v 1.1 2011/01/19 15:52:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';
    
	$admin_is = isadmin();

    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$strtitle = 'Дробление записей по номерам групп';
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
    
    droblenie_group(',');
    droblenie_group('-');
    
    print_footer();
        

function droblenie_group($symbol='-')    
{
    global $CFG; 
    
    $strsql = "groups like '%$symbol%'";
    // echo $strsql . '<br>'; 
    if ($badgroups = get_records_select ('test',  $strsql))	{
        foreach ($badgroups as $badgroup)   {
            
            $names = explode ($symbol, $badgroup->groups);
            foreach ($names as $key => $name) {
                $clearname = checkname($name, $names[0]);
                echo $clearname .'<br>'; 
                if (!empty($clearname))  { 
                    if ($key == 0)  {
                        set_field('test', 'groups', $clearname, 'id', $badgroup->id);
                        print_r($badgroup);
                        notify('Record updated in mdl_test!!', 'green');
                        echo '<hr>';
                    }  else {
                        $badgroup->groups = $clearname;
                        if (!insert_record('test',$badgroup))   {
                            print_r($badgroup);
                            notify('Error inserting in mdl_test!!');
                            echo '<hr>';
                        }  else {
                            print_r($badgroup); 
                            notify('New record inserting in mdl_test!!', 'green');
                            echo '<hr>';
                            
                        }    
                    }
                }  else {
                    notify('Not ');
                }    
           }         
        }
   }    
}        

/*
function droblenie_zapatoi()    
{
    global $CFG; 
    
    $strsql = "groups like '%,%'";
    // echo $strsql . '<br>'; 
    if ($badgroups = get_records_select ('test',  $strsql))	{
        foreach ($badgroups as $badgroup)   {
            
            $names = explode (',', $badgroup->groups);
            foreach ($names as $key => $name) {
                $clearname = checkname($name);
                if ($key == 0)  {
                    set_field('test', 'groups', $clearname, 'id', $badgroup->id);
                    print_r($badgroup);
                    notify('Record updated in mdl_test!!', 'green');
                    echo '<hr>';
                }  else {
                    $badgroup->groups = $clearname;
                    if (!insert_record('test',$badgroup))   {
                        print_r($badgroup);
                        notify('Error inserting in mdl_test!!');
                        echo '<hr>';
                    }  else {
                        print_r($badgroup); 
                        notify('New record inserting in mdl_test!!', 'green');
                        echo '<hr>';
                        
                    }    
                }
           }         
        }
   }    
}     
*/   
    // print_r($tgroups); echo '<hr>';



function checkname($namegroup, $firstname='')
{
    $ret = trim($namegroup);
    $len = strlen ($ret);
    if ($len == 6) return $ret; 
    if ($len == 5) return '0'.$ret;
    
    if ($len == 2 || $len == 3) {
        $fgroup = trim($firstname);
        $len2 = strlen ($fgroup);
        if ($len2 == 5) $fgroup = '0'.$fgroup;
        $strgroup = substr($fgroup, 0, $len2 - $len);
        return $strgroup . $namegroup; 
    }    

    return '';
}    


?>