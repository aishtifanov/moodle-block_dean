<?php // $Id: reports.php,v 1.1.1.1 2009/10/29 08:23:06 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $gid = required_param('gid', PARAM_INT);          // Group id
	$tabroll = optional_param('tabroll', 'itogisessii', PARAM_ALPHA); // tabs
    $term = optional_param('term', 1, PARAM_INT);		// # semestra

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string('speciality','block_dean');
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
    $strrolls = get_string("rolls","block_dean");

    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> $strgroup";


    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("reports.php?mode=1&amp;gid=$gid&amp;fid=", $fid);
    listbox_group_allfaculty("reports.php?mode=2&amp;fid=$fid&amp;gid=", $fid, $gid);
	echo '</table>';

 	$toprow = array();
  	$toprow[] = new tabobject('poseshaemost', $CFG->wwwroot."/blocks/dean/rolls/rollofdiscipline.php?mode=2&amp;fid=$fid&amp;gid=$gid&amp;tabroll=poseshaemost",
                get_string('poseshaemost', 'block_dean'));
   	$toprow[] = new tabobject('tekushuspevaemost', $CFG->wwwroot."/blocks/dean/rolls/roll.php?mode=2&amp;fid=$fid&amp;gid=$gid&amp;tabroll=tekushuspevaemost",
       	        get_string('tekushuspevaemost', 'block_dean'));
   	$toprow[] = new tabobject('itogisessii', $CFG->wwwroot."/blocks/dean/reports/reports.php?mode=2&amp;fid=$fid&amp;gid=$gid&amp;tabroll=itogisessii",
       	        get_string('itogisessii', 'block_dean'));
    $tabs = array($toprow);
    print_tabs($tabs, $tabroll, NULL, NULL);

    if ($fid != 0 && $gid != 0 && $mode >= 2) {

        //$currenttab = 'roll';
   	   // include('../gruppa/tabsonegroup.php');


       	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/reports/reports.php?mode=2&amp;fid=$fid&amp;gid=$gid&amp;term=");

		if (!$faculty = get_record('dean_faculty', 'id', $fid)) {
  		      error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
	    }

        if(!$agroup = get_record('dean_academygroups', 'id', $gid)){
        	error(get_string('nogroup', 'block_dean'), '..\faculty\faculty.php');
        }

        if(!$students = get_records('dean_academygroups_members', 'academygroupid', $gid))
        {
        error(get_string('nostudents', 'block_dean'), '..\faculty\faculty.php') ;
        }else
        {
        $kolstudents= count($students);
         }

       if(!$disciplines =  get_records_sql ("SELECT *
		 							    FROM  {$CFG->prefix}dean_discipline
									    WHERE curriculumid={$agroup->curriculumid} AND term=$term
									    ORDER BY courseid"))
		{
        error(get_string('nodiscipline', 'block_dean'), '..\faculty\faculty.php');
       }else
       {
       	  $koldiscipline = count($disciplines);
        }
          $kolexz = 0;
        foreach ($disciplines as $discipl){
         if ($discipl->controltype == 'examination'){
         	$kolexz++;
         }
         }

	    $table->head  = array (get_string('numclev', 'block_dean'), get_string('kachestvo', 'block_dean'),
	    						get_string('sdal','block_dean'), get_string('dolg','block_dean'));

	    $table->align = array ('center', 'center', 'center', 'center');


    // if ($students)
 	  $sdal = $otlichniki =  $dolg = 0;

       foreach ($students as $student)  {

		    $mark = array();
            $i=0;
        	foreach ($disciplines as $discipline) 	{

	            if (!$roll =  get_record('dean_rolls', 'disciplineid', $discipline->id, 'academygroupid', $gid)) continue;

                $rol = get_record_sql ("SELECT *
				 					FROM {$CFG->prefix}dean_roll_marks
				 					WHERE rollid={$roll->id} AND studentid={$student->userid}");

	            if (!$rol)  {
	               error(get_string('nomarks', 'block_dean'), '..\faculty\faculty.php');
	            } else {
	                // print_r($rol->mark); echo '<hr>';
	            	$mark[$i++] = $rol->mark;
	            }
             }

            if (!empty($mark))	{

            	$kolmark = count($mark);
   	           // print_r($mark); echo $kolmark; echo '<hr>';

          		$kolhormark = $kolotlmark = $sum_hor_mark = 0;
	            for ($i=0; $i<$kolmark;$i++){
	            	if (($mark[$i] > 2 && $mark[$i] <= 5) || $mark[$i] == 1)  {
	            		$kolhormark++;
	            	}//else{error(get_string('nomarks','block_dean'));}

	            	if ($mark[$i] == 4 || $mark[$i] == 5)  {
	            		$sum_hor_mark += $mark[$i];
	            	echo $sum_hor_mark.'<hr>';}


	            }


	            if ($kolhormark == $kolmark)  {
	                 	$sdal++;
                        $max_otl = $kolexz*5;
                        if ($max_otl == 0) { continue; }
			            $proc = $sum_hor_mark/$max_otl*100;
			           // echo $proc.'<hr>';
			            if ($proc > 75)  $otlichniki++;


	            }	else {
                	$dolg++;
                }
	        }
	            //$kolmark = count($mark);
			    unset($mark);
           // $avg_mark = array_sum($mark)/$kolmark;
       }

      $kach = round ($otlichniki/$kolstudents*100);

      $table->data[] = array ($otlichniki,$kach.'%',$sdal,$dolg);
      print_table($table);

				 print_dean_box_start($align='center');

				echo  '<form name="allgroups" method="post" action="allgroups.php">';
                echo  '<input type="hidden" name="mode" value="'.$mode.'" />';
				echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
				echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
				echo  '<div align="center">';
			    echo  '<input type="submit" name="mark" value="';
			    print_string('allgroups','block_dean');
			    echo '"></div></form><p><p><p><p>';
   				echo  '<form name="download" method="post" action="excel.php?action=excel">';
                echo  '<input type="hidden" name="mode" value="'.$mode.'" />';
				echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
				echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
				echo  '<div align="center">';
			    echo  '<input type="submit" name="mark" value="';
			    print_string('downloadexcel');
			    echo '"></div></form><p><p><p><p>';
			    echo  '<form name="download" method="post" action="excelall.php?action=excel">';
                echo  '<input type="hidden" name="mode" value="'.$mode.'" />';
				echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
				echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
				echo  '<div align="center">';
			    echo  '<input type="submit" name="mark" value="';
			    print_string('downloadexcelall','block_dean');
			    echo '"></div></form><p><p><p><p>';

                 print_simple_box_end();
   }

    print_footer();


?>

