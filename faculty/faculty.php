<?php // $Id: faculty.php,v 1.11 2013/09/03 14:01:08 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../../course/lib.php');
    require_once('../lib.php');
    //echo '1';
	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        $table = table_faculty();
        print_table_to_excel_old($table, 1);
        exit();
	}

    $strfaculty = get_string('faculty','block_dean');
    $numberf = '№'; // get_string('numberf','block_dean');
    $strname = get_string('name');
    $strdeanname=get_string('deanname','block_dean');
	$strphone=get_string('telnum','block_dean');
 	$straddress=get_string('address','block_dean');
	$straction = get_string('action','block_dean');

	$admin_is = isadmin();
	$creator_is = iscreator();
    $methodist_is = ismethodist();

    // add_to_log(SITEID, 'dean', 'faculty view', 'faculty.php', $strfaculty);
    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strfaculty";
    print_header("$SITE->shortname: $strfaculty", $SITE->fullname, $breadcrumbs);

	print_heading($strfaculty, "center");
	$table = table_faculty();
	print_table($table);

function table_faculty()
{
    global $CFG, $admin_is, $creator_is, $methodist_is;
    
    $strfaculty = get_string('faculty','block_dean');
    $numberf = '№'; // get_string('numberf','block_dean');
    $strname = get_string('name');
    $strdeanname = get_string('deanname','block_dean') . ' / ' . get_string('deanzams','block_dean') ;
	$strphone=get_string('telnum','block_dean');
 	$straddress=get_string('address','block_dean');
 	$straction = get_string('action','block_dean');

	   $table->head  = array ('№', $strname, $strdeanname, $strphone, $straddress, $straction);
       $table->align = array ("center", "left", "left", "left", "left", "center");
       $table->columnwidth = array (10,30,30,20,20,0);
       // $table->class = 'moutable';
       $table->width = '90%';
       $table->downloadfilename = "faculty";
       $table->worksheetname = "faculty";
       $table->titles = array();
       $table->titles[] = $strfaculty;
       $table->titlesrows = array(20,20,20,20,20);
       
       $sqls = array();
       $sqls[] = "SELECT * FROM {$CFG->prefix}dean_faculty where number>10000 ORDER BY number";
       $sqls[] = "SELECT * FROM {$CFG->prefix}dean_faculty where number<10000 ORDER BY number";
        foreach ($sqls as $sql) {
		$allfacs = get_records_sql($sql);
		if ($allfacs)	{
			foreach ($allfacs as $facultyI) 	{

    	    	if ($facultyI->deanphone2 == 0){
        			$phone = $facultyI->deanphone1;
	        	}
	        	else {
    	    		$phone = $facultyI->deanphone1 . ",  " . $facultyI->deanphone2;
	        	}
                $linkname = "<strong><a href=$CFG->wwwroot/blocks/dean/speciality/speciality.php?id=$facultyI->id>$facultyI->name</a></strong>";
                
                $strlinkupdate = '';
                if ($admin_is || $creator_is || ($methodist_is == $facultyI->id)) {
	 				$title = get_string('editfaculty','block_dean');
					$strlinkupdate = "<a title=\"$title\" href=\"addfaculty.php?mode=edit&amp;fid={$facultyI->id}\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";

	 				$title = get_string('setsesiontimes','block_dean');
					$strlinkupdate .= "<a title=\"$title\" href=\"http://dekanat.bsu.edu.ru/blocks/bsu_schedule/options/options.php?ct=4\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/c/event.gif\" alt=\"$title\" /></a>&nbsp;";

            		// if ($context = get_record('context', 'contextlevel', CONTEXT_FACULTY, 'instanceid', $facultyI->id)) {
          		    if ($context = get_context_instance(CONTEXT_FACULTY, $facultyI->id))    {
    					$title = get_string('assignroles','role');
    				    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean_journal/roles/assign.php?contextid={$context->id}\">";
    					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/roles.gif\" alt=\"$title\" /></a>&nbsp;";
    				}	

                }    
                if ($admin_is)  {
					$title = get_string('deletefaculty','block_dean');
				    $strlinkupdate .=  "<a title=\"$title\" href=\"delfaculty.php?fid={$facultyI->id}\">";
	 				$strlinkupdate .=  "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";
                }    

					$dean_user = get_record_select('user', "id = $facultyI->deanid" , 'id, lastname, firstname');
	  				$dname = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$dean_user->id.'&amp;course=1">'.fullname($dean_user).'</a></strong>';
                    if ($facultyI->zamdeanid)   {
                        $zam_dean_user = get_record_select('user', "id = $facultyI->zamdeanid" , 'id, lastname, firstname');
                        $zamdname = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$zam_dean_user ->id.'&amp;course=1">'.fullname($zam_dean_user ).'</a></strong>';
                    } else {
                        $zamdname = '-';
                    }
                    if ($facultyI->zzdeanid)   {
                        $zz_dean_user = get_record_select('user', "id = $facultyI->zzdeanid" , 'id, lastname, firstname');
                        $zzdname = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$zz_dean_user ->id.'&amp;course=1">'.fullname($zz_dean_user ).'</a></strong>';
                    } else {
                        $zzdname = '-';
                    }

                    if ($facultyI->z3deanid)   {
                        $zz_dean_user = get_record_select('user', "id = $facultyI->z3deanid" , 'id, lastname, firstname');
                        $zzdname .= '<br><strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$zz_dean_user ->id.'&amp;course=1">'.fullname($zz_dean_user ).'</a></strong>';
                    } 
                    
                    if ($facultyI->number > 10000)  {
                        $facultynumber = $facultyI->number;
                    } else {
                        $facultynumber = $facultyI->number - 100;
                    }
                    
					$table->data[] = array ($facultynumber, $linkname, $dname . '<br>'.$zamdname . '<br>'.$zzdname,
                                             $phone, $facultyI->deanaddress, $strlinkupdate);

	        }
		}
        }
		return $table;
	//	print_table($table);
  }


	if ($admin_is || $creator_is) {
?><table align="center">
	<tr>
	<td>
  <form name="addfac" method="post" action="addfaculty.php?mode=new">
	    <div align="center">
		<input type="submit" name="addfaculty" value="<?php print_string('addfaculty','block_dean')?>">
		 </div>
  </form>
  	</td>
	<td>
	<form name="download" method="post" action="faculty.php?action=excel">
	    <div align="center">
		<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
	    </div>
  </form>
	</td>
	</tr>
  </table>
<?php
	}
    print_footer();

?>

