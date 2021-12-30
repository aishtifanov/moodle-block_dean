<?php // $Id: tabsgroups.php,v 1.0 2011/07/07 12:00:56 zagorodnyuk Exp $
    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

//	print_object($groups);
    while($group=mysql_fetch_array($groups)) {
		$groupname=mysql_query("SELECT name FROM mdl_bsu_ref_groups WHERE id='".$group['groupid']."'");
         if(mysql_num_rows($groupname)>0) {
            $groupname=mysql_fetch_assoc($groupname);   
    		if(($methodist_is>0) || $admin_is) {
    			$href = "index.php?ct=".$group['groupid']."&amp;cs=$servicetab&amp;id=$fid&amp;sid=$sid&amp;gid=".$group['groupid']."&amp;uid=$uid";
    		} else {
    			$href = "index.php?ct=".$group['groupid'];
    		}
    	    $toprow[] = new tabobject($group['groupid'], $href, $groupname['name']);
            //$gid=$group['groupid'];
            if(empty($currenttab)) $currenttab = $group['groupid'];
    	    if($gid != 0) $currenttab = $gid;
    	    if($gid == 0) $gid = $currenttab;
        }
	}
  //  if(empty($currenttab)&&$gid != 0) $currenttab = $gid;
 //   if($gid == 0&&!empty($currenttab)) $gid = $currenttab;
   
	$tabs = array($toprow);

    $f=mysql_query("SELECT id,departmentcode FROM mdl_bsu_ref_groups WHERE id='".$currenttab."'");
    $f=mysql_fetch_assoc($f);   
    
    $faculty=mysql_query("SELECT name FROM mdl_bsu_ref_department WHERE departmentcode='".$f['departmentcode']."'");
    $faculty=mysql_fetch_assoc($faculty);   
    
	print_heading('<font color="#0000AA">'.$faculty['name'].'</font>', 'center', 4);
	print_heading('<font color="#0000AA">'.$username['lastname'].' '.$username['firstname'].'</font>', 'center', 4);
	print_tabs($tabs, $currenttab, $inactive, $activetwo);
?>
