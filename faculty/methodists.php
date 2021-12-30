<?PHP // $Id: methodists.php,v 1.12 2012/02/02 09:03:56 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    define("MAX_USERS_PER_PAGE", 15000);

	$fid 	= optional_param('fid', 0, PARAM_INT);          // faculty id
	$action = optional_param('action', '');

    $strmethodists  = get_string('methodists','block_dean');
    $strsearch        = get_string("search");
    $strsearchresults  = get_string("searchresults");
    $strshowall = get_string("showall");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strmethodists";
    print_header("$SITE->shortname: $strmethodists", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
//	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is) { //  && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("methodists.php?fid=", $fid);
	echo '</table>';

    $otdelenies = get_records_select('dean_otdelenie', '', 'id, idotdelenie, name');

    $arr_otdelenies = array();
    $checked = array();
    foreach ($otdelenies as $otdelenie) {
        $arr_otdelenies[$otdelenie->idotdelenie] = $otdelenie->name;
        $checked[$otdelenie->idotdelenie] = '';
    }    

    // print_r($arr_otdelenies); echo '<hr>';
    if ($action == 'allist') {
         $table = table_methodists ();        
	     if (isset($table->data))  {
         	// echo '<div align=center>';
			// $table->print_html();
			$strsetcur = get_string('allmethodist','block_dean');
	       	print_heading($strsetcur, "center");

			print_table($table);
        	// echo '</div>';
		 }
		 else {
			notify(get_string('methodistsfacultysnotfound','block_dean'));
			echo '<hr>';
		 }
         print_footer();         
         exit();
	}
        


	if ($fid != 0) 	 {

		$strsetcur = get_string('setmethodistfaculty','block_dean');
       	print_heading($strsetcur, "center");

		/// A form was submitted so process the input
		if ($frm = data_submitted())   {

            if (!isset($frm->typeotdelenie2) && !isset($frm->typeotdelenie3) && !isset($frm->typeotdelenie4))    {
                notify('<b>Не выбрано ни одно отделение формы обучения!!!</b>');
            }  else {
                $aotd = array();
                foreach ($arr_otdelenies as $key => $arr_otdelenie) {
                    $fldname = 'typeotdelenie' . $key;
                    if (isset($frm->{$fldname})) {
                       $aotd[] =  $key;
                    }
                }    
                
                // print_r($aotd); echo '<hr>'; 
                
                $frm->typeotdelenie = implode (',', $aotd);
                
    			// print_r($frm); exit();
                             
    			if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
    				$rec->facultyid = $fid;
        			$role_methodist = get_record('role', 'shortname', 'methodist');
                    $role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
        			$ctx = get_context_instance(CONTEXT_FACULTY, $fid);
    				foreach ($frm->addselect as $addmethodist) {
    					$rec->userid = $addmethodist;
                        $rec->otdelenie = $frm->typeotdelenie;
                        
        	     		if (!role_assign_dean($role_sotrudnik->id, $rec->userid, 0, $ctx->id))	{
        					notify("Not assigned SOTRUDNIK $rec->userid.");
        			    }
    
        	     		if (!role_assign_dean($role_methodist->id, $rec->userid, 0, $ctx->id))	{
        	    			notify("Not unassigned GROUP TEACHER $rec->userid.");
        			    }
                        
    					if ($existmethodist = get_record_select('dean_methodist', "userid = $addmethodist", 'id, facultyid'))	{
    					    $faculty = get_record('dean_faculty', 'id', $existmethodist->facultyid);
    					    notify(get_string('existmethodist', 'block_dean', $faculty->name));
    					}	else {
        					if (insert_record('dean_methodist', $rec))	{
        						add_to_log(1, 'dean', 'methodist added', '/blocks/dean/faculty/methodists.php', $USER->lastname.' '.$USER->firstname);
        					} else  {
        						error(get_string('errorinaddingmethodist','block_dean'), "$CFG->wwwroot/blocks/dean/faculty/methodists.php?fid=$fid");
        					}
    					}
    				}
    			} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
    				foreach ($frm->removeselect as $removeaddmethodist) {
    					delete_records('dean_methodist', 'facultyid', $fid, 'userid', $removeaddmethodist);
    					add_to_log(1, 'dean', 'methodist deleted', '/blocks/dean/gruppa/methodists.php', $USER->lastname.' '.$USER->firstname);
    				}
    			} else if (!empty($frm->showall)) {
    				unset($frm->searchtext);
    				$frm->previoussearch = 0;
    			}
            }
            
            
            
		}

    	$previoussearch = (!empty($frm) && (!empty($frm->search) or ($frm->previoussearch == 1))) ;

		/// Get all existing methodists for this academygroup.
	 	$strsql =    "SELECT u.id, u.username, u.firstname, u.lastname, u.email, m.otdelenie
                      FROM {$CFG->prefix}user u, {$CFG->prefix}dean_methodist m
					  WHERE m.userid = u.id AND m.facultyid = $fid AND u.deleted = 0 AND u.confirmed = 1
					  ORDER BY lastname ASC";
	   //print_r($strsql);
	    $methodistcount = 0;
	    if ($methodists  = get_records_sql($strsql))	{
	    	$methodistcount = count($methodists);
	    }
	    /*
	   $deanmethodist  = get_record('dean_methodists', 'academygroupid', $gid);
	   if ($deanmethodist)	{
		   $methodist  = get_record('user', 'id', $deanmethodist->userid);
	   }
       */

	    /// Get search results
	    if (!empty($frm->searchtext) and $previoussearch) {
		    $LIKE      = sql_ilike();
	    	// $fullname  = " CONCAT(u.firstname,\" \",u.lastname) ";
			$fullname  = "u.lastname";
	        $search = trim($frm->searchtext);
	    	$searchsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
		                      FROM {$CFG->prefix}user u
							  WHERE	($fullname $LIKE '%$search%' OR email $LIKE '%$search%')
							  GROUP BY u.id
							  ORDER BY u.lastname ASC";
            $usercount = 0;
	        if ($searchusers = get_records_sql($searchsql))	{
		        $usercount = count($searchusers);
		    }

	    }
		else {
	    /// If no search results then get potential teachers
        /*
		    $notinmethodistssql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
	                          FROM {$CFG->prefix}user u
							  GROUP BY u.id
							  ORDER BY u.lastname ASC";

            $usercount = 0;
	    	if ($users = get_records_sql($notinmethodistssql))	{
	    		$usercount = count($users);
	    	}
         */
         /*
        	$dds = get_records_sql ("SELECT DISTINCT userid FROM {$CFG->prefix}role_assignments
        							 WHERE roleid<=4");
            $deansids = array();
            foreach ($dds as $d)	{
                $deansids[] = $d->userid;
            }
            $listdeansid = implode (',', $deansids); 
        
            $usercount = 0;
        	if ($users = get_records_sql("SELECT  id, username, lastname, firstname, email FROM {$CFG->prefix}user
        							   WHERE id in ($listdeansid) ORDER BY lastname")) {
                $usercount = count($users);
    	    }
           */
          $users = array();
          $usercount = count_records('user');
            
		}


        if (isset($frm->typeotdelenie2))    {
            $checked[2] = 'CHECKED=true';
        }
        if (isset($frm->typeotdelenie3))    {
            $checked[3] = 'CHECKED=true';
        }
        if (isset($frm->typeotdelenie4))    {
            $checked[4] = 'CHECKED=true';
        }
        
   		// $checked2 = 'CHECKED=true';
    	// $checked3 = '';

	   $searchtext = (isset($frm->searchtext)) ? $frm->searchtext : "";
	   $previoussearch = ($previoussearch) ? '1' : '0';

	   print_dean_box_start("center");

	   $sesskey = !empty($USER->id) ? $USER->sesskey : '';
?>


<form name="methodistsform" id="methodistsform" method="post" action="methodists.php">
<input type="hidden" name="previoussearch" value="<?php echo $previoussearch ?>" />
<input type="hidden" name="fid" value="<?php echo $fid ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
<table align="center" border="1"><tr><td>
<input type="checkbox" <?php echo $checked[2] ?> value="2" name="typeotdelenie2" /> <?php echo $arr_otdelenies[2] ?> формы обучения<br />
<input type="checkbox" <?php echo $checked[3] ?> value="3" name="typeotdelenie3" /> <?php echo $arr_otdelenies[3]  ?> формы обучения<br />
<input type="checkbox" <?php echo $checked[4] ?> value="4" name="typeotdelenie4" /> <?php echo $arr_otdelenies[4]  ?> формы обучения<br />
</td></tr></table>
</div>
  <table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top">
          <?php
              echo get_string('methodists', 'block_dean') . ' (' . $methodistcount . ')';
          ?>
      </td>
      <td></td>
      <td valign="top">
          <?php
              echo get_string('potentialmethodistfaculty', 'block_dean') . ' (' . $usercount . ')';
          ?>
      </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect"
                  onFocus="document.methodistsform.add.disabled=true;
                           document.methodistsform.remove.disabled=false;
                           document.methodistsform.addselect.selectedIndex=-1;" />
          <?php
		  	  if ($methodists)		{
	              foreach ($methodists as $methodist) {
                    $ids = explode(',', $methodist->otdelenie);
                    $aotd = array();
                    foreach ($ids as $id)   {
                        $aotd[] = $arr_otdelenies[$id];
                    }	               
 	                $fullname = fullname($methodist, true) . ' (' . implode (', ', $aotd) . ')';
  	                echo "<option value=\"$methodist->id\">".$fullname.", ".$methodist->email."</option>\n";
   		           }
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
                  onFocus="document.methodistsform.add.disabled=false;
                           document.methodistsform.remove.disabled=true;
                           document.methodistsform.removeselect.selectedIndex=-1;">
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
                  onFocus ="document.methodistsform.add.disabled=true;
                            document.methodistsform.remove.disabled=true;
                            document.methodistsform.removeselect.selectedIndex=-1;
                            document.methodistsform.addselect.selectedIndex=-1;"
                  onkeydown = "var keyCode = event.which ? event.which : event.keyCode;
                               if (keyCode == 13) {
                                    document.methodistsform.previoussearch.value=1;
                                    document.methodistsform.submit();
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


     $table = table_methodists ($fid);        
     if (isset($table->data))  {
     	// echo '<div align=center>';
		// $table->print_html();
		print_table($table);
    	// echo '</div>';
	 } else {
		notify(get_string('methodistsfacultysnotfound','block_dean'));
		echo '<hr>';
	 }

    if ($admin_is || $creator_is) {
		$options = array();
	    $options['fid'] = $fid;
	   	$options['sesskey'] = $USER->sesskey;
	    $options['action'] = 'allist';
		echo '<table align="center"><tr>';
	    echo '<td align="center">';
	    print_single_button('methodists.php', $options, get_string('allmethodist', 'block_dean'));
	    echo '</td></tr>';
	    echo '</table>';
	}

   print_footer();


function table_methodists ($fid=0)
{
    global $CFG, $arr_otdelenies;    

    $strselect = '';
    if ($fid > 0)   {
        $strselect = "AND m.facultyid = $fid";  
    }
    
	$table->head  = array ('', get_string('fullname'), get_string('otdelenie', 'block_dean'), get_string('username'), 
                            get_string('ffaculty', 'block_dean'), get_string('email'), get_string('lastaccess'));
	$table->align = array ("center", "left", "left", "left", "left", "left", "center");

     $strsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
    					   u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                           u.lastaccess, m.facultyid, m.otdelenie
                       FROM {$CFG->prefix}user u, {$CFG->prefix}dean_methodist m
                       WHERE m.userid = u.id AND u.deleted = 0 $strselect
                       ORDER BY u.lastname";
     $methodists = get_records_sql($strsql);
    
     if(!empty($methodists)) {
    
    
        $strnever = get_string('never');
        foreach ($methodists as $methodist) {


            $ids = explode(',', $methodist->otdelenie);
            $aotd = array();
            foreach ($ids as $id)   {
                $aotd[] = $arr_otdelenies[$id];
            }	               
    
            if ($methodist->lastaccess) {
                $lastaccess = format_time(time() - $methodist->lastaccess);
            } else {
                $lastaccess = $strnever;
            }
    
    		$faculty = get_record('dean_faculty', 'id', $methodist->facultyid);
    
    		$title = get_string('methodists','block_dean');
            $table->data[] = array (print_user_picture($methodist->id, 1, $methodist->picture, false, true),
    						    "<div align=left><strong><a href=\"{$CFG->wwwroot}/user/view.php?id={$methodist->id}\">".fullname($methodist)."</a></strong></div>",
                                implode(',<br>', $aotd), 
                                "<strong>$methodist->username</strong>",
                                $faculty->name,
                                $methodist->email,
                                "<center><small>$lastaccess</small></center>");
       }
    }    
    
    return $table;
}

?>