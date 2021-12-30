<?php // $Id: synchr_test.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$admin_is = isadmin();
	$creator_is = iscreator();

    if (!$admin_is && !$creator_is ) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$strsynchronization = get_string('synchronization','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/frontpage.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$site->shortname: $strsynchronization", $site->fullname, $breadcrumbs);

    $currenttab = 'synchr_fid';
    include('tabsynchr.php');

    $test = strip_tags_from_field('glossary_entries', 'concept');

    print_footer($course);

?>

