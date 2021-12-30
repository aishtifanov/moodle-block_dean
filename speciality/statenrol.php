<?php // $Id: statenrol.php,v 1.2 2012/06/19 08:04:32 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
        
    $fid = required_param('id', PARAM_INT);          // Faculty id
    
	$admin_is = isadmin();

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


    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

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
	    popup_form("statenrol.php?id=", $facultymenu, "switchspeciality", 0, "", "", "", false);
		echo '</td></tr></table>';
	}
	else  {
	    popup_form("statenrol.php?id=", $facultymenu, "switchspeciality", "$faculty->id", "", "", "", false);
		echo '</td></tr></table>';
        
        $currenttab = 'statenrol';
        include("tabs.php");

		// echo "<hr />";
	   	$table = table_statenrol ($fid);
        print_table($table);
        
    }

 print_footer();



function table_statenrol($fid)
{
	global $CFG, $admin_is, $faculty;
    
    $strgroup = get_string('group');        
	
    $table->head  = array ($strgroup);
    $table->align = array ('center');
    $table->size = array ("10%");
    $table->width = "90%";
    
    for ($i=1; $i<=12; $i++)    {
        $table->head[] = $i;
        $table->align[] = 'center';
        $table->size[] = '5%';
    }

    $title = $strgroup;
    
    $allgroups = array();
    $alltabledata = array();
    
    $strsql = "SELECT id, name, code, enrolyear, formlearning, specialityid
			   FROM {$CFG->prefix}dean_curriculum
			   WHERE facultyid=$fid AND formlearning = 'correspondenceformtraining'
			   ORDER BY enrolyear";
    if ($curriculums = get_records_sql ($strsql))    {
        foreach ($curriculums as $curriculum)   {
            $countdiscurr = array();
            for ($i=1; $i<=12; $i++)    {
                $countdiscurr[$i]  = count_records_select('dean_discipline', "curriculumid=$curriculum->id AND term=$i AND courseid<>1");
                $currcourseids[$i] = array();
                if ($currtermdiscs = get_records_select('dean_discipline', "curriculumid = $curriculum->id AND term = $i", 'courseid', 'id, courseid')) {
                    foreach ($currtermdiscs as $currtermdisc)   {
                        if ($currtermdisc->courseid != 1)   {
                            $currcourseids[$i][] = $currtermdisc->courseid;
                        }    
                    }
                }
            }    
            // print_object($currcourseids); continue;

            $strsql = "SELECT id, name FROM {$CFG->prefix}dean_academygroups
					   WHERE facultyid=$fid AND curriculumid=$curriculum->id
					   ORDER BY name DESC";
            if ($agroups = get_records_sql($strsql)) {
                foreach ($agroups as $agroup) {  
                  
                    if ($groupsdisciplines = get_records_select("groups", "name = '$agroup->name'", '', 'id, courseid'))    {
                        $countdisgrup = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                        foreach ($groupsdisciplines as $grdis)  {
                            if ($termnums = get_term($grdis->courseid, $currcourseids)) {
                                foreach ($termnums as $termnum) {
                                    $countdisgrup[$termnum]++;
                                }       
                            } 
                        }
                        $sid = $curriculum->specialityid;
                        $cid = $curriculum->id;
                        $linkgroup = "<strong><a title=\"\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/registrgroup.php?mode=4&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
                        $tabledata = array ($linkgroup);
                        $allgroups[] = $agroup->name; 
                        for ($i=1; $i<=12; $i++)    {
                            if ($countdiscurr[$i] == $countdisgrup[$i]) {
                                $tabledata[] = '<font color=green><b>' . $countdiscurr[$i] . ' = ' . $countdisgrup[$i] . '</b></font>'; 
                            } else  if  ($countdiscurr[$i] > $countdisgrup[$i]) {
                                $tabledata[] = '<font color=red><b>' . $countdiscurr[$i] . ' > ' . $countdisgrup[$i] . '</b></font>';
                            } else {
                                $tabledata[] = '<b>' . $countdiscurr[$i] . ' < ' . $countdisgrup[$i] . '</b>';
                            }    
                        }
                        $alltabledata[$agroup->name] = $tabledata;  
                        
                    }      
                 }   
             }
        }
    }                
    
    rsort($allgroups);
    
    // print_object($alltabledata); exit();
    
    foreach ($allgroups as $group)  {
       $table->data[] = $alltabledata[$group]; 
    }
    $tabledata = array ('<b>'.$strgroup.'</b>');
    for ($i=1; $i<=12; $i++)    {
        $tabledata[] = $i;
    }
    $table->data[] = $tabledata;
    
           
    return $table;
}

function get_term($courseid, $currcourseids) 
{
    global $CFG, $admin_is; 
    
    $terms = array();
    
    foreach ($currcourseids as $term => $cc)   {
        if (in_array($courseid, $cc)) {
            $terms[] = $term;
        }
    }
    
    return $terms;
}         

?>


