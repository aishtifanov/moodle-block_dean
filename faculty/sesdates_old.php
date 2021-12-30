<?PHP // $Id: sesdates.php,v 1.11 2012/09/21 08:22:51 shtifanov Exp $

    require_once('../../../config.php');
    // require_once('../dean_journal/lib_journal.php');    
    require_once('../lib.php');

	$fid = required_param('fid', PARAM_INT);
    $ses = optional_param('ses', 3, PARAM_INT);		// vkladka session : 1,2,3,4,5,6,7

    $edyearid = optional_param('yid', 0, PARAM_INT);
    
    $curredyearid = get_current_edyearid();
    $nextedyearid = $curredyearid + 1;
    
    if ($edyearid == 0) {
        $edyearid = $curredyearid;
    }    
    
	$admin_is = isadmin();
	$creator_is = iscreator();
    $methodist_is = ismethodist();

    if (!($admin_is || $creator_is || $methodist_is == $fid)) {
        error(get_string('adminaccess', 'block_dean'), "faculty.php");
    }

	if($methodist_is) {
		$oid = get_record_select('dean_methodist', "userid=$USER->id", 'otdelenie');
		$oid = $oid->otdelenie;
        if ($oid == 3)  {
            $oid = '3,4';
        }
	} else {
	    $oid = '0,1,2,3,4';
	}
    
    
	$strfaculty = get_string('faculty','block_dean');
    $strtitle = get_string('setsesiontimes','block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"faculty.php\">$strfaculty</a> -> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $ffaculty = get_record_select ('dean_faculty', "id = $fid", 'id, name');
    print_heading($ffaculty->name . '.<br>' . $strtitle, 'center', 4);

	
	$strsummer = get_string('sessummer', 'block_dean');

    $toprow = array();
    $nameyear = get_field_select('bsu_ref_edyear', "EdYear", "id=$curredyearid");
    $toprow[] = new tabobject('yid'.$curredyearid, "sesdates.php?yid=$curredyearid&fid=$fid&ses=$ses", "Текущий уч. год ($nameyear)");    
    $nameyear = get_field_select('bsu_ref_edyear', "EdYear", "id=$nextedyearid");
    $toprow[] = new tabobject('yid'.$nextedyearid, "sesdates.php?yid=$nextedyearid&fid=$fid&ses=$ses", "Следующий уч. год ($nameyear)");      
    $tabs = array($toprow);
	print_tabs($tabs, 'yid'.$edyearid, NULL, NULL);


    $toprow3 = array();
    
    for ($i=1; $i<=10; $i++) {
        $strsession = get_string('session'.$i, 'block_dean');
        $toprow3[] = new tabobject('session'.$i, "sesdates.php?yid=$edyearid&fid=$fid&amp;ses=".$i, $strsession);    
    }
    
    $currenttab = 'session'.$ses;

    $tabs3 = array($toprow3);
	print_tabs($tabs3, $currenttab, NULL, NULL);

	if (!$frm = data_submitted())   {
         $frm = (object)$_GET;
    }

    if (isset($frm->setdate))	{
        $frm->startdatereport = get_timestamp_from_date($frm->s_day, $frm->s_month, $frm->s_year);
        $frm->enddatereport = get_timestamp_from_date($frm->e_day, $frm->e_month, $frm->e_year);
        if ($agroups = get_groups_exists_bsu_students($fid, $oid))  { 
            // print_r($agroups);
            foreach ($agroups as $agroup) {
                if ($session = get_record_select ('dean_journal_session', "edyearid=$edyearid AND groupid = $agroup->id AND numsession=$ses"))   {
                    $session->datestart = $frm->startdatereport;
                    $session->dateend   = $frm->enddatereport;
                    $session->timemodified = time();
                    $session->modifierid = $USER->id;
                    if ($ses == 1)  {
                         $session->typedocument = 2;
                    } else {
                         $session->typedocument = 1;
                    }     
                    if (!update_record('dean_journal_session', $session))  {
                        print_r($agroup);
                        notify('Not update dean_academygroups!');
                    }
                } else {
                    $session->facultyid = $fid;
                    $session->groupid = $agroup->id;
                    $session->edyearid = $edyearid;
                    $session->numsession = $ses;
            		$session->datestart = $frm->startdatereport; 
            		$session->dateend   = $frm->enddatereport;
                    $session->timemodified = time();
                    $session->modifierid = $USER->id;
                    if ($ses == 1)  {
                         $session->typedocument = 2;
                    } else {
                         $session->typedocument = 1;
                    }     
            		if (!insert_record('dean_journal_session', $session))	{
            		     echo '<pre>'; print_r($session); echo '</pre>';   
            			 notice("Ошибка при создании сессии: {$rec->numsession}", "sesdates.php?fid=$fid&amp;gid=$gid");		                    
                    }     
               }
            }
        }    
    }

    if (isset($frm->save))	{
        $agroups = array();
        // print_object($frm); 
		foreach($frm as $fieldname => $value)	{
			    $mask = substr($fieldname, 0, 2);
			    switch ($mask)  {
					case 'b_': 	$ids = explode('_', $fieldname);
                                if (empty($value)) {
					               $agroups[$ids[1]]->datestart = 0;
					            } else {
                                    list ($frms_day, $frms_month, $frms_year) = explode ('.', $value);
                                    $frmstartdatereport = get_timestamp_from_date($frms_day, $frms_month, $frms_year);
    		            		    $agroups[$ids[1]]->datestart = $frmstartdatereport;
                                }    
	  				break;
					case 'e_': 	$ids = explode('_', $fieldname);
                                if (empty($value)) {
					               $agroups[$ids[1]]->dateend = 0;
                                } else {    
                                    list ($frms_day, $frms_month, $frms_year) = explode ('.', $value);
                                    $frmenddatereport  = get_timestamp_from_date($frms_day, $frms_month, $frms_year);
    		            		    $agroups[$ids[1]]->dateend = $frmenddatereport;
                                }    
	  				break;
					case 'o_': 	$ids = explode('_', $fieldname);
                                if (empty($value)) {
					               $agroups[$ids[1]]->isset = 0;
                                } else {    
    		            		    $agroups[$ids[1]]->isset = 1;
                                }    
	  				break;
					case 't_': 	$ids = explode('_', $fieldname);
                                $agroups[$ids[1]]->typedocument = $value;
	  				break;
	  			}
        }
        
        // echo '<pre>'; print_r($agroups); echo '</pre>';        
        foreach ($agroups as $agroupid => $agroup)  {
        	if ($agroupid > 0) {
                if ($session = get_record_select ('dean_journal_session', "edyearid=$edyearid AND groupid = $agroupid AND numsession=$ses"))   {
                    $session->datestart = $agroup->datestart;
                    $session->dateend   = $agroup->dateend;
                    if (isset($agroup->isset)) {
                        $session->isset = $agroup->isset; 
                    } else {
                        $session->isset = 0;
                    }
                    $session->timemodified = time();
                    $session->modifierid = $USER->id;
                    $session->typedocument = $agroup->typedocument;
                    if (!update_record('dean_journal_session', $session))  {
                        echo '<pre>'; print_r($agroup); echo '</pre>';
                        notify('Ошибка при обновлении сессии: {$session->numsession}');
                    }
                } else {
                    if (isset($agroup->isset)) {
                        $session->isset = $agroup->isset; 
                    } else {
                        $session->isset = 0;
                    }
                    $session->facultyid = $fid;
                    $session->groupid   = $agroupid;
                    $session->edyearid  = $edyearid;
                    $session->numsession = $ses;
                    $session->datestart = $agroup->datestart;
                    $session->dateend   = $agroup->dateend;
                    $session->timemodified = time();
                    $session->modifierid = $USER->id;
                    if ($ses == 1)  {
                         $session->typedocument = 2;
                    } else {
                         $session->typedocument = 1;
                    }     
            		if (!insert_record('dean_journal_session', $session))	{
            		     echo '<pre>'; print_r($rec); echo '</pre>';   
            			 notice("Ошибка при создании сессии: {$session->numsession}", "sesdates.php?fid=$fid&amp;gid=$gid");		                    
                    }     
               }
        	}
        }
    }

    $redirlink = "sesdates.php?fid=$fid&amp;ses=$ses&amp;";

    if (isset($frm->s_day) && $frm->s_day!=0 &&  $frm->s_month !=0 && $frm->s_year !=0)  {
		 $frm->startdatereport = get_timestamp_from_date($frm->s_day, $frm->s_month, $frm->s_year);
         $startdaterep = $frm->startdatereport;
         $redirlink .= "s_day=$frm->s_day&amp;s_month=$frm->s_month&amp;s_year=$frm->s_year&amp;";
    } else {
        $startdaterep = 0;
    }

    if (isset($frm->e_day) && $frm->e_day!=0 &&  $frm->e_month !=0 && $frm->e_year !=0)  {
		$frm->enddatereport = get_timestamp_from_date($frm->e_day, $frm->e_month, $frm->e_year);
        $enddaterep = $frm->enddatereport;
        $redirlink .= "e_day=$frm->e_day&amp;e_month=$frm->e_month&amp;e_year=$frm->e_year";
    } else {
        $enddaterep = 0;
    }

    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    echo '<form name="choosetime" method="post" action="sesdates.php">';
    echo '<tr> <td>'.get_string('sesperiod', 'block_dean').': с</td><td>';
    print_date_monitoring('s_day', 's_month', 's_year', $startdaterep, 1);
    echo ' по ';
    print_date_monitoring('e_day', 'e_month', 'e_year', $enddaterep, 1);
    echo '</td></tr>';
    echo '<tr><td align=center colspan=2>';
	echo '<input type="hidden" name="yid" value="'.$edyearid.'">
          <input type="hidden" name="ses" value="'.$ses.'">
 		  <input type="hidden" name="fid" value="'.$fid.'">';
 	 echo '<input type="submit" name="setdate" value="' . get_string('applayforallgroup', 'block_dean') . '">';
     echo '</td></tr></form></table>';


	echo '<form name="addrecord" id="addrecord" method="post" action="sesdates.php">';

   	$table = table_timelessons($edyearid, $fid, $ses, $oid);
	print_table($table);

	echo '<input type="hidden" name="yid" value="'.$edyearid.'"> 
          <input type="hidden" name="ses" value="'.$ses.'">
 		  <input type="hidden" name="fid" value="'.$fid.'">';
	echo '<center><input name="save" type="submit" value="'.get_string('savechanges') . '" /></center>';
	echo '</form>';

	print_footer();


function table_timelessons ($edyearid, $fid, $ses, $oid)
{
	global $CFG;

	$table->head  = array (get_string('group'), get_string("begses","block_dean"), 
                           get_string('endses','block_dean'), get_string('enable'),
                           get_string('fullpage_dlg:doctypes', 'editor_tinymce'));
	$table->align = array ('center', 'center', 'center', 'center', 'center');
    $table->size = array ('10%', '20%', '20%', '10%', '10%');
	$table->columnwidth = array (7, 20, 20, 9, 9, 9);
    // $table->datatype = array ('char', 'char');
    // $table->class = 'moutable';
   	$table->width = '60%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('sesperiod', 'block_dean');
    $table->worksheetname = 'timelessons';

            
    $choices[1] = 'Справка';
    $choices[2] = get_string('notice');

	if ($agroups = get_groups_exists_bsu_students($fid, $oid))  {       
        foreach ($agroups as $agroup) {

			$tabledata = array($agroup->name);
            $begses = $endses = $checked = $typedocument = '';
            if ($session = get_record_select ('dean_journal_session', "edyearid=$edyearid AND groupid = $agroup->id AND numsession=$ses"))   {
                if ($session->datestart == 0){
                    $begses = '';
                } else {    
                    $begses = date ('d.m.Y', $session->datestart);
                }    
                
                if ($session->dateend == 0)   {
                    $endses = '';
                } else {    
                    $endses = date ('d.m.Y', $session->dateend);
                }

               	if($session->isset) {
    			     $checked = 'checked';
        		}
                     
                if ($session->typedocument) {
                    $typedocument = $session->typedocument;
                }
            } else {
                if ($ses == 1)  {
                    $typedocument = 2;
                }    
            }
            
   			$tabledata[] = "<input type=text  name=b_{$agroup->id} size=10 maxlength=10 value=\"$begses\">";
   			$tabledata[] = "<input type=text  name=e_{$agroup->id} size=10 maxlength=10 value=\"$endses\">";
            
            $tabledata[] = " <input type='checkbox' $checked name=o_{$agroup->id}> ";
            $tabledata[] = choose_from_menu ($choices, 't_'.$agroup->id, $typedocument, "", '', 0, true);

   			$table->data[] = $tabledata;
        }
    }

    return $table;
}


function get_groups_exists_bsu_students($fid, $oid)  
{
    global $CFG;

  	$grup = '';
	$strsql = "SELECT DISTINCT(grup) FROM {$CFG->prefix}bsu_students";
    // echo $strsql.'<br>'; 
	if ($sqls = get_records_sql ($strsql)) {

    	foreach($sqls as $sql) {
    		$array[] = $sql->grup;
    	}
    	$grup = implode(',', $array);
    	$grup = " AND name IN($grup)";
    }    

	$strsql = "SELECT id, name  FROM {$CFG->prefix}dean_academygroups
		       WHERE facultyid=$fid AND idotdelenie in ($oid) $grup
			   ORDER BY name DESC";
    // echo $strsql.'<br>';            
	$agroups = get_records_sql ($strsql);
    
    // echo '<pre>'; print_r($agroups); echo '</pre>'; 
    return $agroups;
}     

?>