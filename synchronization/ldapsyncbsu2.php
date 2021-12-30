<?php // $Id: ldapsyncbsu.php,v 1.4 2011/02/14 08:20:18 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');

    
    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id
   	$go = optional_param('go', 1, PARAM_INT);			//
    $delay = optional_param('t', 3, PARAM_INT);			//     
	
	$strsynchronization = get_string('ldapsyncbsu','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    $currenttab = 'ldapsyncbsu';
    include('ldaptabs.php');
    
	if (!function_exists('odbc_connect')) {
	  notice('Function odbc_connect not found');  
	}               
    
    $conn=odbc_connect("S28-Server", "PegasS28Link", "8412c7788f");
    if (!$conn)  {
        exit("Connection Failed: " . $conn);
    }
    $sql="SELECT * FROM Plans";
    $rs=odbc_exec($conn,$sql);
    if (!$rs)  {
        exit("Error in SQL");
    }
    odbc_result_all($rs);
    odbc_close($conn);
    
    print_footer();    

?>