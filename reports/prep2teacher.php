<?php // $Id: prep2teacher.php,v 1.11 2012/11/29 06:15:39 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    require_login();
    
    $uid = optional_param('uid', 0, PARAM_INT); // Teacher id (userid)
    $action = optional_param('action', '');
    $symnum = optional_param('symnum', 0, PARAM_INT); // Symbol number

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    // if (!$admin_is && !$creator_is && !$methodist_is) {
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
   
  
	$strtitle = get_string('prep2teacher','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'prep2teacher';
    include('tabs.php');


    $arrRus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'К', 'Л', 'М',
                  'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я');
                  
    $toprow = array();
    foreach ($arrRus as $key => $aRus)	{
       $toprow[] = new tabobject($key, 'prep2teacher.php?symnum='.$key, $aRus);
	}	      
    $tabs = array($toprow);
    print_tabs($tabs, $symnum, NULL, NULL);
    
    
    if ($action == 'allist')    {
    	$table = table_allist();
       	print_table($table);
    }

    if ($action == 'clear') {
        sopostavlenie_userid_staffid();
        redirect('prep2teacher.php', "Сопоставление преподавателей выполнено.", 30);        
    }

	// Processing submitted data
	if ($frm = data_submitted())   {
		if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
			foreach ($frm->addselect as $additem) { //  
			    if (!record_exists_select('dean_teacher_card', "userid = $uid AND staffid = $additem"))	{
					$rec->userid = $uid;
			        $rec->staffid = $additem;
			    	if (!insert_record('dean_teacher_card', $rec)){
			    		notify('Error in adding dean_course_discipline!');
			    	} 
			    } 
              //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&amp;sid=$sid&amp;did=$did&amp;rid=$rid");
            }
		} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
			foreach ($frm->removeselect as $removeitem) {
				delete_records('dean_teacher_card', 'userid', $uid, 'staffid', $removeitem);
				// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
			}
		} 
	}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    $currusername = listbox_teacher("prep2teacher.php?symnum=$symnum&uid=", $uid);
  	echo '</table>';

	if ($uid != 0)	{  
	   // $dstudents = get_records_select ('dean_teacher_card', "userid = $uid", '', 'id, userid, staffid');
       $strsql = "SELECT a.id, a.userid, a.staffid, b.name, b.notusing FROM mdl_dean_teacher_card a 
                  INNER JOIN mdl_bsu_staffform b ON b.id = a.staffid
                  where a.userid = $uid";
                   // where a.userid = $uid AND b.notusing = 0";
       $dstudents = get_records_sql($strsql);

       // $firstsym = mb_substr($currusername, 0, 1, 'UTF-8');
       $firstsym = $arrRus[$symnum]; 
       // echo $currusername . ' ---- ' . $firstsym; 
	   // $cstudents = get_records_select ('bsu_staffform', "notusing = 0 AND name LIKE '$firstsym%'", 'name', 'id, name, subdepartmentid');
       $cstudents = get_records_select ('bsu_staffform', "name LIKE '$firstsym%'", 'name', 'id, name, subdepartmentid');

 	    $idsstudents  = array();
	    $dstudentmenu = array();
	 	if ($dstudents)	{
	 		foreach ($dstudents as $dstud)	{
	 			$dstudentmenu[$dstud->staffid] = $dstud->name;
	 			$idsstudents[] = $dstud->staffid;
	 		}
	 	}

        $MAX_SYMBOLS = 100;
		$schoolmenu = array();
	    if ($cstudents)	{
	  		foreach ($cstudents as $cstud) {
	  			$schoolmenu[$cstud->id] = $cstud->name;
			}
		}
	    print_simple_box_start("center", '70%');
	    // print_heading($strtitle, "center", 3);
	    $sesskey = !empty($USER->id) ? $USER->sesskey : '';
        echo '<form name="formpoint" id="formpoint" method="post" action="prep2teacher.php">';
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
  <input type="hidden" name="uid" value="<?php echo $uid ?>" />
  <input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
</form>

<?php
   print_simple_box_end();
   }

	$options = array();
   	$options['sesskey'] = $USER->sesskey;
    $options['action'] = 'allist';
	echo '<table align="center"><tr>';
    echo '<td align="center">';
    print_single_button('prep2teacher.php', $options, get_string('showallpreps', 'block_dean'));
    echo '</td></tr>';
    echo '</table>';

    print_simple_box_start('center', '50%', 'white');
	echo '<center><form name="staffform1" id="staffform1" method="post" action="prep2teacher.php">'.
         '<input type="hidden" name="action" value="clear">'.
	     '<input name="clear" id="clear" type="submit" value="Попытаться сопоставить преподавателей по Ф.И.О." />'.
		 '</form></center>';
    print_simple_box_end();


   print_footer();


// Display list student of group
function listbox_teacher($scriptname, $uid)
{
    global $CFG, $symnum, $arrRus;
    
    $firstsym = $arrRus[$symnum];
    
    $strtitle = get_string("selectateacher","block_dean")."...";
    $teachermenu = array();
    
    $teachermenu[0] = $strtitle;
/*    
    $strsql = "SELECT id, lastname, firstname FROM {$CFG->prefix}user
              where (auth = 'ldap2' || email like '%@bsu.edu.ru') and  deleted = 0
              ORDER BY lastname";
*/    
    $strsql = "SELECT id, auth, lastname, firstname, email FROM {$CFG->prefix}user
              where (auth = 'cas' or auth = 'manual') and  deleted = 0 and lastname LIKE '$firstsym%'
              ORDER BY lastname";


    if($teachers = get_records_sql($strsql)) {
        foreach ($teachers as $teacher) 	{
            // if (!record_exists_mou('dean_teacher_card', 'userid', $teacher->id))    {
    		   $teachermenu[$teacher->id] = fullname($teacher) . " ($teacher->email, $teacher->auth)";
            // }  
    	}
    }

    echo '<tr><td>'.get_string('teachername','block_dean').':</td><td>';
    popup_form($scriptname, $teachermenu, "switchteacher", $uid, "", "", "", false);
    echo '</td></tr>';
    return $teachermenu[$uid];
}



function table_allist($specsym = "")	
{
	global $CFG;
	
    $table->head  = array ('N', "Экзаменатор"); 
    $table->align = array ("center", "left");
	$table->width = '75%';
    $table->size = array ('5%', '20%');
    $table->columnwidth = array (7, 39);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examschedule','block_dean');
    $table->downloadfilename = "examschedule";
    $table->worksheetname = 'examschedule';
    
    $strsql = "SELECT Distinct  a.id, a.name FROM mdl_bsu_staffform a LEFT JOIN mdl_dean_teacher_card b ON a.id=b.staffid
                WHERE (b.staffid Is Null) and (name not like '\_%') and (name <> '') AND notusing = 0
                ORDER BY name;";
    $i=1;            
    $arrstaff = array();
    if ($teachers  = get_records_sql($strsql))   {
        foreach ($teachers as $teacher) {
            if (!in_array($teacher->name, $arrstaff))    { 
                $arrstaff[] = $teacher->name; 
                $table->data[] = array($i++, $teacher->name);
            }    
        }
        
    }
    
    return $table;
}


function sopostavlenie_userid_staffid()    
{
    global $CFG; 

   $strsql = "SELECT id, lastname, firstname FROM {$CFG->prefix}user
              where (auth = 'ldap2' || email like '%@bsu.edu.ru') and  deleted = 0
              ORDER BY lastname";
              
   $teachers = get_records_sql($strsql);
    
   $staffids = array();
   
   $notfoundteacher = array();
   $i=0; 
   foreach ($teachers as $teacher)  {
        $UPPERTEACHER = mb_strtoupper($teacher->lastname, 'UTF-8');
        
        $strsql = "SELECT Id, Name FROM mdl_bsu_staffform 
                   WHERE notusing=0 AND Name like '$UPPERTEACHER%'";
        // echo $strsql . '<br>';            
        if ($staffs = get_records_sql($strsql)) {
            $UPPERTEACHER = mb_strtoupper($teacher->lastname, 'UTF-8') . ' ' . mb_strtoupper($teacher->firstname, 'UTF-8');
            $flag = false;
            foreach ($staffs as $staff) {
                if ($UPPERTEACHER == $staff->Name)   {
                    // echo fullname($teacher) . ' === ' . $staff->Name . '<br>'; 
                    // $flag = true;  break;
                    $staffids[] = $staff->Id;
                    if (!record_exists_select('dean_teacher_card', "userid = $teacher->id AND staffid=$staff->Id")) {
                        $rec->userid = $teacher->id;
                        $rec->staffid=$staff->Id;
                        insert_record('dean_teacher_card', $rec);
                        echo $i++ . '<br>';
                    }
                } 
            }
            /*
            if (!$flag) {
                echo fullname($teacher) . ' === ???' . '<br>';
            }
            */
        } else {
            // echo fullname($teacher) . ' === ?' . '<br>';
            // $notfoundteacher[] = fullname($teacher);
        }               
    }
    
    // $allstaffs = get_records_sql("SELECT staffformid FROM mdl_bsu_teachingload where edworkid in (SELECT distinct EdWorkId FROM mdl_dean_schedule);"
    $allstaffs = get_records_sql("SELECT id, Name FROM mdl_bsu_staffform WHERE notusing=0");
    
    foreach ($allstaffs as $staff)   {
        if (!in_array($staff->id, $staffids))   {
            // echo $staff->Name . ' === ' . '??? <br>';
            $notfoundteacher[] = $staff->Name; 
        } 
    }
    
    sort($notfoundteacher);
 
 
    $table->head  = array ('N', "Экзаменатор"); 
    $table->align = array ("center", "left");
	$table->width = '75%';
    $table->size = array ('5%', '20%');
    $table->columnwidth = array (7, 39);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examschedule','block_dean');
    $table->downloadfilename = "examschedule";
    $table->worksheetname = 'examschedule';
    
    $i=1;            
    $arrstaff = array();
    
        foreach ($notfoundteacher as $nft)  {
            if (!in_array($nft, $arrstaff))    { 
                $arrstaff[] = $nft; 
                $table->data[] = array($i++, $nft);
            }    
        }
        
    print_table($table);
        
    return true;
}    


?>