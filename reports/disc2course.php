<?php // $Id: disc2course.php,v 1.6 2011/11/25 09:33:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    require_login();
    
    $bdid = optional_param('bdid', 0, PARAM_INT); // BSU Discipline id
	$action = optional_param('action', '');
    $symnum = optional_param('symnum', 0, PARAM_INT); // Symbol number    

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    // if (!$admin_is && !$creator_is && !$methodist_is) {
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
  
	$action   = optional_param('action', '');
    if ($action == 'excel') 	{
		$table = table_examschedule("'");
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('disc2course','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'disc2course';
    include('tabs.php');


    $arrRus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'К', 'Л', 'М',
                  'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я');
                  
    $toprow = array();
    foreach ($arrRus as $key => $aRus)	{
       $toprow[] = new tabobject($key, 'disc2course.php?symnum='.$key, $aRus);
	}	      
    $tabs = array($toprow);
    print_tabs($tabs, $symnum, NULL, NULL);

    if ($action == 'clear') {
        sopostavlenie_discipline_courses();
        redirect('disc2course.php', "Сопоставление дисциплин выполнено.", 30);        
    }

	// Processing submitted data
	if ($frm = data_submitted())   {
		if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
			foreach ($frm->addselect as $additem) { //  
			    if (!record_exists_select('dean_course_discipline', "disciplineid = $bdid AND courseid = $additem"))	{
					$rec->disciplineid 	= $bdid;
			        $rec->courseid 	= $additem;
			    	if (!insert_record('dean_course_discipline', $rec)){
			    		notify('Error in adding dean_course_discipline!');
			    	} 
			    } 
              //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did&amp;rid=$rid");
            }
		} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
			foreach ($frm->removeselect as $removeitem) {
				delete_records('dean_course_discipline', 'disciplineid', $bdid, 'courseid', $removeitem);
				// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
			}
		} 
	}


	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_bsu_discipline_symnum("disc2course.php?symnum=$symnum&bdid=", $bdid, $arrRus[$symnum]);
  	echo '</table>';

	if ($bdid != 0)	{  
	   $bsudiscipline = get_record_select ('bsu_ref_disciplinename', "id = $bdid", 'id');
       
       $strsql = "SELECT a.courseid, a.disciplineid, b.fullname FROM {$CFG->prefix}dean_course_discipline a
                  INNER JOIN {$CFG->prefix}course b ON b.id=a.courseid
                  WHERE a.disciplineid = $bsudiscipline->id 
                  ORDER BY b.fullname";
	   $dstudents = get_records_sql($strsql);

       $firstsym = $arrRus[$symnum]; 
  	   $strsql = "SELECT id, fullname FROM {$CFG->prefix}course
                  where category in (58, 64, 63) and fullname like '$firstsym%'    
                  ORDER BY fullname";
	   $cstudents = get_records_sql($strsql);

 	    $idsstudents  = array();
	    $dstudentmenu = array();
	 	if ($dstudents)	{
	 		foreach ($dstudents as $dstud)	{
	 			$dstudentmenu[$dstud->courseid] = $dstud->fullname;
	 			$idsstudents[] = $dstud->courseid;
	 		}
	 	}

        $MAX_SYMBOLS = 100;
		$schoolmenu = array();
	    if ($cstudents)	{
	  		foreach ($cstudents as $cstud) {
			    $len = mb_strlen ($cstud->fullname);
			    if ($len > $MAX_SYMBOLS)  {
				    $cstud->fullname = mb_substr($cstud->fullname,  0, $MAX_SYMBOLS, "UTF-8") . ' ...';
			    }
	  			$schoolmenu[$cstud->id] = $cstud->fullname;
			}
		}
	    print_simple_box_start("center", '70%');
	    // print_heading($strtitle, "center", 3);
	    $sesskey = !empty($USER->id) ? $USER->sesskey : '';
        echo '<form name="formpoint" id="formpoint" method="post" action="disc2course.php">';
        echo '<table align="center" border="0" cellpadding="5" cellspacing="0"><tr> <td valign="top">'; 
        echo get_string('oustafftype', 'block_dean');
        echo '</td><td></td><td valign="top">'; 
        echo get_string('allstafftype', 'block_dean');
        ?>
         </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="30" id="removeselect"  multiple
                  onFocus="document.formpoint.add.disabled=true;
                           document.formpoint.remove.disabled=false;
                           document.formpoint.addselect.selectedIndex=-1;" />
          <?php
          if (!empty($dstudentmenu))	{
              foreach ($dstudentmenu as $key => $pm) {
                  echo "<option value=\"$key\">" . $pm . "</option>\n";
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
          <select name="addselect[]" size="30" id="addselect"  multiple
                  onFocus="document.formpoint.add.disabled=false;
                           document.formpoint.remove.disabled=true;
                           document.formpoint.removeselect.selectedIndex=-1;">
          <?php
          if (!empty($schoolmenu))	{
              foreach ($schoolmenu as $key => $sm) {
              	if (!in_array($key, $idsstudents))	{
                  echo "<option value=\"$key\">" . $sm . "</option>\n";
                }
              }
          }
          ?>
         </select>
       </td>
    </tr>
  </table>
  <input type="hidden" name="bdid" value="<?php echo $bdid ?>" />
  <input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
</form>

<?php
   print_simple_box_end();
   }


    print_simple_box_start('center', '50%', 'white');
	echo '<center><form name="staffform1" id="staffform1" method="post" action="disc2course.php">'.
         '<input type="hidden" name="action" value="clear">'.
	     '<input name="clear" id="clear" type="submit" value="Попытаться сопоставить дисциплины по имени" />'.
		 '</form></center>';
    print_simple_box_end();


   print_footer();



function sopostavlenie_discipline_courses()    
{
    global $CFG; 

   $strsql = "SELECT id, fullname FROM {$CFG->prefix}course
              where category in (58, 64)    
              ORDER BY fullname";
   $courses = get_records_sql($strsql);

    $coursesnames =array();
    foreach ($courses  as $course)  {          
        $clearname = $course->fullname;
        $clearname = mb_ereg_replace('(ЦТ)', '', $clearname);
        $clearname = mb_ereg_replace('(ЦТЛ)', '', $clearname);
        $clearname = mb_ereg_replace('_дневное', '', $clearname); 
        $clearname = mb_ereg_replace('(дневное)', '', $clearname);
        
        $pos = strpos($clearname, '(');
        $names = array();
        if ($pos === false) {
            $pos = strpos($clearname, '_');
            if ($pos === false) {
            } else {
                $names = explode ('_', $clearname); 
            }    
        } else {
            $names = explode ('(', $clearname);
        }    
        if (isset($names[0])) $clearname = $names[0];
		$coursesnames[$course->id] = trim($clearname);
    }
    
    // $bsudisciplines = get_records_select ('bsu_ref_disciplinename', "", '', 'id, name');
    $bsudisciplines = get_records_sql ("SELECT DISTINCT a.DisciplineNameId, b.name FROM mdl_dean_schedule a
                                      INNER JOIN mdl_bsu_ref_disciplinename b ON  a.DisciplineNameId=b.Id
                                      ORDER BY b.name;");
        

    foreach ($bsudisciplines as $bsudiscipline)   {
        echo '<hr>';
        foreach ($coursesnames as $courseid => $coursesname) {
            if ($coursesname == $bsudiscipline->name)  {
                echo $coursesname . ' === ' . $bsudiscipline->name . '<br>';
                echo $courseid . ' === ' . $bsudiscipline->DisciplineNameId . '<br>';
                if (!record_exists_select('dean_course_discipline', "disciplineid = $bsudiscipline->DisciplineNameId AND courseid=$courseid")) {
                    $rec->disciplineid = $bsudiscipline->DisciplineNameId;
                    $rec->courseid     = $courseid;
                    insert_record('dean_course_discipline', $rec);
                }
            } else {
                $pos = mb_strpos($coursesname ,  $bsudiscipline->name);
                if ($pos === false) continue;
                echo $coursesname . ' .=. ' . $bsudiscipline->name . '<br>';         
                echo $courseid . ' === ' . $bsudiscipline->DisciplineNameId . '<br>';       
                if (!record_exists_select('dean_course_discipline', "disciplineid = $bsudiscipline->DisciplineNameId AND courseid=$courseid")) {
                    $rec->disciplineid = $bsudiscipline->DisciplineNameId;
                    $rec->courseid     = $courseid;
                    insert_record('dean_course_discipline', $rec);
                }
            }
        }
    }        	
   
    
    return '';
}    


// Display list disciplines as popup_form
function listbox_bsu_discipline_symnum($scriptname, $bdid, $firstsym)
{
  global $CFG;

  $strtitle = get_string('selectadiscipline', 'block_dean') . ' ...';
  $disciplinemenu = array($strtitle);
/*
  $arr_discipline = get_records_sql ("SELECT DISTINCT a.DisciplineNameId, b.Name FROM mdl_dean_schedule a
                                      INNER JOIN mdl_bsu_ref_disciplinename b ON  a.DisciplineNameId=b.Id
                                      ORDER BY b.Name;");
*/
  $arr_discipline = get_records_sql ("SELECT Id, Name FROM mdl_bsu_ref_disciplinename
                                      WHERE Name like '$firstsym%'
                                      ORDER BY Name;");

  if ($arr_discipline) 	{
		foreach ($arr_discipline as $ds) {
			//$disciplinemenu[$ds->DisciplineNameId] =$ds->Name;
            $disciplinemenu[$ds->Id] =$ds->Name;
		}
  }


  echo '<tr><td>'.get_string('discipline', 'block_dean').':</td><td>';
  popup_form($scriptname, $disciplinemenu, 'switchbsudiscipline', $bdid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

?>