<?php // $Id: lib_dean.php,v 1.33 2011/07/01 05:24:18 shtifanov Exp $


// Display list group as popup_form
function listbox_group_pegas($scriptname, $fid, $sid, $cid, $gid, $idotd = 0)
{
  global $CFG;

  if ($idotd == 0) {
     $idotdelenie = '';
  } else {
     $idotdelenie = " AND idotdelenie in ($idotd)";
  }

  if ($cid == 0)   {
     $curriculum = '';
  } else {
     $curriculum = " AND curriculumid = $cid ";
  }

  $groupmenu = array();
  $groupmenu[0] = get_string('selectagroup', 'block_dean') . ' ...';

  if($sid == 0) {
  	$specialityid = '';
  } else {
	$specialityid = " AND specialityid=$sid";
  }

  if ($fid != 0)   {

	  $strsql = "SELECT id, name  FROM {$CFG->prefix}dean_academygroups
		         WHERE facultyid=$fid $specialityid $curriculum $idotdelenie
				 ORDER BY name";

      if ($arr_group = get_records_sql ($strsql)) 	{
    		foreach ($arr_group as $gr) {
    			$groupmenu[$gr->id] =$gr->name;
    		}
      }
 }

  echo '<tr><td>'.get_string('group').':</td><td>';
  popup_form($scriptname, $groupmenu, 'switchgroup', $gid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

?>
