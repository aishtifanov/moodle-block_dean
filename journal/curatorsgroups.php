<?PHP // $Id: curatorsgroups.php,v 1.2 2009/09/02 12:08:17 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    define("MAX_USERS_PER_PAGE", 15000);

	if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$gid = optional_param('gid', 0, PARAM_INT);          // Group id
	$action   = optional_param('action', '');


	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	// $strstudents = get_string("students","block_dean");
    $strcurators  = get_string('curatorsgroups','block_dean');
    $strsearch        = get_string('search');
    $strsearchresults  = get_string('searchresults');
    $strshowall = get_string('showall');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strcurators";
    print_header("$site->shortname: $strcurators", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

   	listbox_all_group('curatorsgroups.php?gid=', $gid);

    if ($action == 'allist') {

         $strsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							   u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                           u.lastaccess, m.academygroupid
	                       FROM {$CFG->prefix}user u, {$CFG->prefix}dean_curators m
	                       WHERE m.userid = u.id
	                       ORDER BY u.lastname";
         $curators = get_records_sql($strsql);

        if(!empty($curators)) {

   			$table->head  = array ('', get_string('fullname'), get_string('username'), get_string('group'), get_string('email'), get_string('lastaccess'));
			$table->align = array ("center", "left", "left", "left", "left", "center");

		    $strnever = get_string('never');
	    	$datestring->day   = get_string('day');
		    $datestring->days  = get_string('days');
	    	$datestring->hour  = get_string('hour');
		    $datestring->hours = get_string('hours');
	    	$datestring->min   = get_string('min');
		    $datestring->mins  = get_string('mins');
		    $datestring->sec   = get_string('sec');
	    	$datestring->secs  = get_string('secs');

            foreach ($curators as $curator) {

           		$agroup = get_record('dean_academygroups', 'id', $curator->academygroupid);

                if ($curator->lastaccess) {
                    $lastaccess = format_time(time() - $curator->lastaccess, $datestring);
                } else {
                    $lastaccess = $strnever;
                }

				$title = get_string('curatorsgroups','block_dean');
                $table->data[] = array (print_user_picture($curator->id, 1, $curator->picture, false, true),
								    "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}&amp;uid={$curator->id}\">".fullname($curator)."</a></strong></div>",
                                    "<strong>$curator->username</strong>",
                                    "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>",
                                    $curator->email,
                                    "<center><small>$lastaccess</small></center>");
            }

	    	// echo '<div align=center>';
			// $table->print_html();
			$strsetcur = get_string('allcuratorsgroups','block_dean');
	       	print_heading($strsetcur, "center");

			print_table($table);
        	// echo '</div>';
		}
		else {
			notify(get_string('curatorsgroupsnotfound','block_dean'));
			echo '<hr>';
		}




        exit();
	}


	if ($gid != 0) 	 {

		$strsetcur = get_string('setcuratorgroup','block_dean');
       	print_heading($strsetcur, "center");

		/// A form was submitted so process the input
		if ($frm = data_submitted())   {
			// print_r($frm);
			if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
				$rec->academygroupid = $gid;
				foreach ($frm->addselect as $addcurator) {
					$rec->userid = $addcurator;
					if ($existcurator=get_record('dean_curators', 'academygroupid', $gid))	{
						notify(get_string('existcurator', 'block_dean'));
					}	else {
						if (insert_record('dean_curators', $rec))	{
							add_to_log(1, 'dean', 'curators added', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
						} else  {
							error(get_string('errorinaddingcurators','block_dean'), "$CFG->wwwroot/blocks/dean/gruppa/curatorsgroups.php?gid=$gid");
						}
					}
				}
			} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
				foreach ($frm->removeselect as $removeaddcurator) {
					delete_records('dean_curators', 'academygroupid', $gid, 'userid', $removeaddcurator);
					add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
				}
			} else if (!empty($frm->showall)) {
				unset($frm->searchtext);
				$frm->previoussearch = 0;
			}
		}

    	$previoussearch = (!empty($frm) && (!empty($frm->search) or ($frm->previoussearch == 1))) ;

		/// Get all existing curators for this academygroup.
		/*
  	  $curatorssql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
                      FROM {$CFG->prefix}user u
                      LEFT JOIN {$CFG->prefix}dean_curators t ON t.userid = u.id
					  WHERE u.deleted = 0 AND u.confirmed = 1
					  ORDER BY lastname ASC";
*/
	   //print_r($strsql);
	   // $curators  = get_records_sql($curatorssql);
	   $deancurator  = get_record('dean_curators', 'academygroupid', $gid);
	   if ($deancurator)	{
		   $curator  = get_record('user', 'id', $deancurator->userid);
	   }


    /// Get search results
    if (!empty($frm->searchtext) and $previoussearch) {
	    $LIKE      = sql_ilike();
    	// $fullname  = " CONCAT(u.firstname,\" \",u.lastname) ";
		$fullname  = "u.lastname";
        $search = trim($frm->searchtext);
    	$searchsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
	                      FROM {$CFG->prefix}user u
						  WHERE	($fullname $LIKE '%$search%' OR email $LIKE '%$search%')
						  GROUP BY u.username
						  ORDER BY u.lastname ASC";
        $usercount = 0;
        if ($searchusers = get_records_sql($searchsql))	{
	        $usercount = count($searchusers);
	    }
    }
	else {
    /// If no search results then get potential teachers
	    $notincuratorssql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
                          FROM {$CFG->prefix}user u
						  GROUP BY u.username
						  ORDER BY u.lastname ASC";
        $usercount = 0;
    	if ($users = get_records_sql($notincuratorssql))	{
			$usercount = count($users);
		}
	}


   $searchtext = (isset($frm->searchtext)) ? $frm->searchtext : "";
   $previoussearch = ($previoussearch) ? '1' : '0';

   print_dean_box_start("center");

   $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>


<form name="curatorsform" id="curatorsform" method="post" action="curatorsgroups.php">
<input type="hidden" name="previoussearch" value="<?php echo $previoussearch ?>" />
<input type="hidden" name="gid" value="<?php echo $gid ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
  <table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top">
          <?php
              echo get_string('curatorgroup', 'block_dean');
          ?>
      </td>
      <td></td>
      <td valign="top">
          <?php
              echo get_string('potentialcuratorgroup', 'block_dean') . ' (' . $usercount. ')';
          ?>
      </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect"
                  onFocus="document.curatorsform.add.disabled=true;
                           document.curatorsform.remove.disabled=false;
                           document.curatorsform.addselect.selectedIndex=-1;" />
          <?php
		  	  if ($curator)		{
                  $fullname = fullname($curator, true);
                  echo "<option value=\"$curator->id\">".$fullname.", ".$curator->email."</option>\n";
              }
          ?>

          </select></td>
      <td valign="top">
        <br />
        <input name="add" type="submit" id="add" value="&larr;" />
        <br />
        <input name="remove" type="submit" id="remove" value="&rarr;" />
        <br />
      </td>
      <td valign="top">
          <select name="addselect[]" size="20" id="addselect"
                  onFocus="document.curatorsform.add.disabled=false;
                           document.curatorsform.remove.disabled=true;
                           document.curatorsform.removeselect.selectedIndex=-1;">
          <?php
         if (!empty($searchusers)) {
                  echo "<optgroup label=\"$strsearchresults (" . count($searchusers) . ")\">\n";
                  foreach ($searchusers as $user) {
                      $fullname = fullname($user, true);
                      echo "<option value=\"$user->id\">".$fullname.", ".$user->email."</option>\n";
                  }
                  echo "</optgroup>\n";
              }
        else {
                  if ($usercount > MAX_USERS_PER_PAGE) {
                      echo '<optgroup label="'.get_string('toomanytoshow').'"><option></option></optgroup>'."\n"
                          .'<optgroup label="'.get_string('trysearching').'"><option></option></optgroup>'."\n";
                  }
                  else {
                      if ($usercount > 0) {    //fix for bug#4455
                          foreach ($users as $user) {
                              $fullname = fullname($user, true);
                              echo "<option value=\"$user->id\">".$fullname.", ".$user->email."</option>\n";
                          }
                      }
                  }
              }

          ?>
         </select>
         <br />
         <input type="text" name="searchtext" size="30" value="<?php p($searchtext) ?>"
                  onFocus ="document.curatorsform.add.disabled=true;
                            document.curatorsform.remove.disabled=true;
                            document.curatorsform.removeselect.selectedIndex=-1;
                            document.curatorsform.addselect.selectedIndex=-1;"
                  onkeydown = "var keyCode = event.which ? event.which : event.keyCode;
                               if (keyCode == 13) {
                                    document.curatorsform.previoussearch.value=1;
                                    document.curatorsform.submit();
                               } " />
         <input name="search" id="search" type="submit" value="<?php p($strsearch) ?>" />
         <?php
              if (!empty($searchusers)) {
                  echo '<input name="showall" id="showall" type="submit" value="'.$strshowall.'" />'."\n";
              }
         ?>
       </td>
    </tr>
  </table>
</form>

<?php
   print_dean_box_end();
  }

    if ($admin_is || $creator_is || $methodist_is) {
		$options = array();
	    $options['gid'] = $gid;
	   	$options['sesskey'] = $USER->sesskey;
	    $options['action'] = 'allist';
		echo '<table align="center"><tr>';
	    echo '<td align="center">';
	    print_single_button("curatorsgroups.php", $options, get_string('allcuratorsgroups', 'block_dean'));
	    echo '</td></tr>';
	    echo '</table>';
	}

   print_footer();

?>