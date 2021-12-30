<?php // $Id: speciality2group.php,v 1.2 2011/10/06 10:27:52 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
        
    $fid = required_param('id', PARAM_INT);          // Faculty id
    
	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();


    $strfaculty = get_string('faculty','block_dean');
    $strffaculty = get_string("ffaculty","block_dean");
    $strspeciality = get_string("speciality","block_dean");
    $strinformation = get_string("information","block_dean");
    $numbersp= get_string("numbersp","block_dean");
    $strname = get_string("name");
    $facultytab = "{$CFG->prefix}dean_faculty";
    $specialitytab="{$CFG->prefix}dean_speciality";
    $strqualification=get_string("qualification","block_dean");
	$straction = get_string("action","block_dean");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a> -> $strspeciality";
    print_header("$SITE->shortname: $strspeciality", $SITE->fullname, $breadcrumbs);


	if ($fid == 0)  {
	   $faculty = get_record('dean_faculty', 'id', 1);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
    }

    // add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, $strspeciality);

   	$allfacs = get_records_sql("SELECT id, name FROM {$CFG->prefix}dean_faculty ORDER BY number");
	$facultymenu[0] = get_string("selectafaculty","block_dean")."...";
	if ($allfacs)	{
		foreach ($allfacs as $facultyI) 	{
			$facultymenu[$facultyI->id] = $facultyI->name;
		}
	}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    echo '<tr> <td>'.$strffaculty.': </td><td>';

	if ($fid == 0)  {
	    popup_form("speciality2group.php?id=", $facultymenu, "switchspeciality", 0, "", "", "", false);
		echo '</td></tr></table>';
	}
	else  {
	    popup_form("speciality2group.php?id=", $facultymenu, "switchspeciality", "$faculty->id", "", "", "", false);
		echo '</td></tr></table>';
        
        $currenttab = 'speciality2group';
        include("tabs.php");

		// echo "<hr />";
	   	$table = table_speciality ($fid);
        print_table($table);
        
    }

 print_footer();



function table_speciality($fid)
{
	global $CFG, $admin_is, $creator_is;        
	
    $strspace = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    
    $numbersp= get_string("numbersp","block_dean");
    $strname = get_string("name");
    $strqualification=get_string("qualification","block_dean");
    $strcurriculums = get_string("curriculums","block_dean");

	$faculty = get_record('dean_faculty', 'id', $fid);

    $table->head  = array ($numbersp . '.' . $strname . ' ('. $strqualification . ')', 
                            $strcurriculums . '/'. get_string('groups'));
    $table->align = array ('left', 'left');
    $table->size = array ("20%", "80%");
    $table->width = "90%";
    // $table->class = 'moutable';

	if ($arr_specs =  get_records('dean_speciality', 'facultyid', $faculty->id, 'name'))   {

		foreach ($arr_specs as $spec) {

                $rupsandgroups = '';
                $strsql = "SELECT id, name, code, enrolyear, formlearning, description
						   FROM {$CFG->prefix}dean_curriculum
						   WHERE facultyid=$fid AND specialityid=$spec->id
						   ORDER BY enrolyear";
			    if ($curriculums = get_records_sql ($strsql))    {
                    foreach ($curriculums as $curriculum)   {
                        if ($curriculum->formlearning == 'daytimeformtraining')  {
                            $curriculumname = $curriculum->name . ' (' . get_string('daytimeformtraining', 'block_dean') . ' форма обучения).';
                        } else if ($curriculum->formlearning == 'correspondenceformtraining')  {
                            $curriculumname = $curriculum->name . ' (' . get_string('correspondenceformtraining', 'block_dean') . ' форма обучения).';
                        } else {
                            $curriculumname = $curriculum->name;
                        }   
                        $countdis = count_records('dean_discipline', 'curriculumid', $curriculum->id);
                        $rupsandgroups .= '<hr>' . $curriculumname .  " Количество дисциплин: <b>$countdis</b><br><br>";
                        $strsql = "SELECT id, name FROM {$CFG->prefix}dean_academygroups
								   WHERE facultyid=$fid AND specialityid=$spec->id AND curriculumid=$curriculum->id
								   ORDER BY name DESC";
                        $strgroups = get_academygoups($fid, $spec->id, $curriculum->id, $countdis);
                        $rupsandgroups .= $strgroups;           
                        /*           
                        if ($agroups = get_records_sql ($strsql))   {
                            foreach ($agroups as $agroup)   {
       				            $title = get_string('disciplines', 'block_dean');
                                $linkagroup = "<a title=\"$title\" href=\"disciplines.php?fid={$faculty->id}&gid={$agroup->id}\"><strong>{$agroup->name}</strong></a>";  
                                $rupsandgroups .= $strspace . '* '. $linkagroup . '<br>'; 
                            }
                        } 
                        */          

                    }
                }    


				$table->data[] = array ('<b>' . $spec->number.'.'.$spec->name . ' ('. $spec->qualification . ')</b>', 
                                        $rupsandgroups);

		}
    }
    
    return $table;
}

function get_academygoups($fid, $sid, $cid, $countdis) 
{
    global $CFG, $admin_is, $creator_is; 
    
    $choices[0] = '-';
    $otdelenies = get_records_select('dean_otdelenie', '', 'id, idotdelenie, name');
    foreach ($otdelenies as $otdelenie) {
        $choices[$otdelenie->idotdelenie] = $otdelenie->name;
    }    
    
		$table->head  = array (get_string("course","block_dean"), get_string('group'), get_string("numofstudents","block_dean"), 
                               get_string('otdelenie','block_dean'), get_string('disciplines','block_dean'),
                                get_string("action","block_dean"));
		$table->align = array ("center", "center", "center", "center", "center","center");
        $table->size = array ("3%", "10%", "5%", "5%", "5%", "7%");
        $table->width = "50%"; 
        $table->tablealign = 'left';

		$ugroups1 = get_records_sql ("SELECT *
									  FROM {$CFG->prefix}dean_academygroups
									  WHERE facultyid=$fid AND specialityid=$sid AND curriculumid=$cid
									  ORDER BY name DESC");
		if ($ugroups1)
		foreach ($ugroups1 as $ugroup) {
			if 	($admin_is || $creator_is)	 {
				$title = get_string('editgroup','block_dean');
				$strlinkupdate = "<a title=\"$title\" href=\"../groups/addgroup.php?mode=edit&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/edit.gif\" alt=\"$title\" /></a>&nbsp;";
				$title = get_string('deletegroup','block_dean');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"../groups/delgroup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/delete.gif\" alt=\"$title\" /></a>&nbsp;";

				$title = get_string('movegraduategroup','block_dean');
			    $strlinkupdate = $strlinkupdate . "<a title=\"$title\" href=\"../groups/movegraduategroup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">";
				$strlinkupdate = $strlinkupdate . "<img src=\"{$CFG->pixpath}/t/removeright.gif\" alt=\"$title\" /></a>&nbsp;";

			// $strgroup = "<strong><a href=$CFG->wwwroot/blocks/dean/disciplines.php?fid=$fid&amp;sid=$sid&amp;cid={$curr->id}>$curr->name</a></strong>";
			}
			else	{
				$strlinkupdate = '-';
			}

            $countdiscipline = count_records("groups", "name", $ugroup->name);
            
            $countdiscipline .= ' (' . $countdis . ')';
            
            $countdiscipline = "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/registrgroup.php?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">$countdiscipline</a></strong>";
            
            if ($ugroup->idotdelenie == 3)  {
                $strotdelenie = '<b>' . $choices[$ugroup->idotdelenie] . '</b>';
            } else {
                $strotdelenie = $choices[$ugroup->idotdelenie] . ' ф.о.';
            }    
            
            
			$title = get_string('templatecertificate','block_dean');
			$countsudents = count_records('dean_academygroups_members', 'academygroupid',  $ugroup->id);
			$table->data[] = array (get_kurs($ugroup->startyear), 
                                    "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$ugroup->id}\">$ugroup->name</a></strong>",
									$countsudents,  $strotdelenie, $countdiscipline, $strlinkupdate);
		}

		$strret = print_table($table, true);
        
        return $strret;
}         

?>


