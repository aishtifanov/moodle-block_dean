<?php // $Id: statsfaculty.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $courseid = optional_param('did', 0, PARAM_INT);  // Course id

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroups = get_string('groups');
    $struser = get_string("user");
    $strimportfac = 'Статистика посещения ТОЗ';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> $strimportfac";

    print_header("$site->shortname: $strimportfac", "$site->fullname", $breadcrumbs);


	$faculty = get_record('dean_faculty', 'id', $fid);

	print_heading($faculty->name, "center");

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

/*
	if ($fid == 0)  {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
	}
*/
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("statsfaculty.php?did=$courseid&amp;fid=", $fid);
	listbox_courses_TOZ("statsfaculty.php?fid=$fid&amp;did=", $courseid);
	echo '</table>';

	if ($fid > 0  && $courseid > 0)  {
           if ($fid == 9999)	{
              $startfid = 1;
              $endfid = 22;
           } else {              $startfid = $fid;
              $endfid = $fid;
           }
           $table->head  = array (get_string('group'), get_string('countstud','block_dean'), 'Кол-во <br> посетивших курс');
 	  	   $table->align = array ('center', 'center', 'center');

        for ($i_fid = $startfid; $i_fid<=$endfid; $i_fid++)		{
       	   $faculty = get_record('dean_faculty', 'id', $i_fid);
	       $table->data[] = array ("<b>$faculty->name</b>", '<hr>', '<hr>');

           for ($z=7; $z>=6; $z--)	{
				$numag=0;
				$shablon = "'__0$z%'";
                $strsql = "SELECT id, name FROM {$CFG->prefix}dean_academygroups
                		  WHERE  facultyid = {$i_fid} and name like $shablon";
                // echo $strsql;

				if ($academygroups = get_records_sql($strsql))		{
			        // $academygroupsarray = array();
   			        //$academygroupslist = implode(',', $academygroupsarray);
	                // $strsql = "SELECT id, userid FROM {$CFG->prefix}dean_academygroups_members WHERE  academygroupid in $academygroupslist";
					// print_heading( 'Количество академических групп ' . count($academygroups), 'left', 4);
                    $vsegois = 0;
                    $vsegotoz = 0;
				    foreach ($academygroups as $academygroup)	{
						    if ($mgroup = get_record('groups', 'courseid', $courseid, 'name', $academygroup->name))	 {
								$mmembercount = count_records('groups_members', 'groupid', $mgroup->id);
								$vsegois += $mmembercount;

						        $select = 'SELECT u.id, u.username, s.timeaccess AS lastaccess ';
						        $from   = 'FROM '.$CFG->prefix.'user u LEFT JOIN '.$CFG->prefix.'user_students s ON s.userid = u.id ';
						        $from  .= 'LEFT JOIN '.$CFG->prefix.'groups_members gm ON u.id = gm.userid ';
						        $where  = 'WHERE s.course = '.$courseid.' AND u.deleted = 0 AND lastaccess > 0 ';
						        $where .= ' AND gm.groupid = '.$mgroup->id;

                                $totalcount =  '-';
                                if ($totalcount1 = count_records_sql('SELECT COUNT(*) '.$from.$where))	{                                	$totalcount = $totalcount1;
                                	$vsegotoz +=  $totalcount;                                }
                                /*
								$countotal = '-';
							    if ($students = get_records_sql($select.$from.$where))	{							    	$countotal = count($students);							    }
							    */
					 		    // $table->data[] = array ($academygroup->name, $mmembercount, $totalcount);
							}
					}
					$numcourse = 9 - $z;

                    // $table->data[] = array ('<hr>', '<hr>', '<hr>');
                    $table->data[] = array ("<b>$numcourse-й курс</b>", $vsegois, $vsegotoz);
                    // $table->data[] = array ('<hr>', '<hr>', '<hr>');

				}   else {			       $table->data[] = array ("<i>Академические группы не найдены</i>", '', '');				}
           }
        }
			print_table($table);

?>	<table align="center">
	<tr>
	<td>
  <form name="addspec" method="post" action="<?php echo "statsfaculty.php?did=$courseid&amp;fid=9999" ?>">
	    <div align="center">
		<input type="submit" name="addspeciality" value="<?php print('Все факультеты')?>">
	    </div>
  </form>
  </td>
	</td></tr>
  </table>
<?php


    }

	print_footer();


?>
