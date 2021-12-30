<?php  // $Id: ldaptabs.php,v 1.5 2011/11/03 06:54:19 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow = array();

    if ($admin_is) {
        $toprow[] = new tabobject('ldapsyncfac', $CFG->wwwroot."/blocks/dean/synchronization/ldapsyncfac.php", 
        	            get_string('ldapsynchfac', 'block_dean'));
        $toprow[] = new tabobject('ldapsyncspec', $CFG->wwwroot."/blocks/dean/synchronization/ldapsyncspec.php", 
        	            get_string('ldapsynchspec', 'block_dean'));
    }                    
    $toprow[] = new tabobject('ldapsync', $CFG->wwwroot."/blocks/dean/synchronization/ldapsync.php", 
    	            get_string('ldapsynchronization', 'block_dean'));
    if ($admin_is) {
        $toprow[] = new tabobject('ldapreg', $CFG->wwwroot."/blocks/dean/synchronization/ldapreg.php", 
        	            get_string('ldapregistaration', 'block_dean'));
    	$toprow[] = new tabobject('ldapedit', $CFG->wwwroot."/blocks/dean/synchronization/ldapedit.php", 
        	            get_string('ldapedit', 'block_dean'));
    	$toprow[] = new tabobject('ldap2', $CFG->wwwroot."/blocks/dean/synchronization/ldap2.php", 
        	            get_string('ldapaspirant', 'block_dean'));
        $toprow[] = new tabobject('img_synchr', $CFG->wwwroot."/blocks/dean/synchronization/img_synchr.php", 
        	            get_string('img_synchr', 'block_dean'));
    }                    
    // $toprow[] = new tabobject('ldapsyncbsu', $CFG->wwwroot."/blocks/dean/synchronization/ldapsyncbsu.php", get_string('ldapsyncbsu', 'block_dean'));

    $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);

?>