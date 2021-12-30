<?PHP // $Id: checkstudents.php,v 1.2 2013/09/03 14:01:07 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

	$numcurr = optional_param('numcurr', 0, PARAM_INT);	// # KOL_CURRICULUM
	$cid = optional_param('cid', 0, PARAM_INT);	// # CURRICULUM ID

	if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup  = get_string('group');
	$strgroups = get_string('groups');
    $strsearchgroup = get_string('checkstudents', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchgroup";
    print_header("$site->shortname: $strsearchgroup", "$site->fullname", $breadcrumbs);


	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


    if ($curriculums = get_records ('dean_curriculum', 'formlearning', 'correspondenceformtraining', 'enrolyear'))		{
        // print_heading( 'Количество рабочих учебных планов ' . count($curriculums), 'left', 2);
		foreach ($curriculums as $curriculum)	{
			$numag=0;
			if ($academygroups = get_records('dean_academygroups',  'curriculumid', $curriculum->id, 'name'))		{
			    foreach ($academygroups as $academygroup)	{
			        $len = strlen($academygroup->name);
			        if ($len == 6 || $len == 8)	$numag++;
			    }
			}
			if ($numag > 0)	{
	            $sortidarray[$curriculum->id] = $numag;
	            $currnamearray[$curriculum->id] = $curriculum->name;
	        }
        }
        arsort ($sortidarray);

		$currmenu = array();
	    $currmenu[0] = get_string('selectacurr', 'block_dean') . ' ...';

        $cc = 0;
		foreach ($sortidarray as $currid => $numag )	{
			$cc++;
			$len = strlen ($currnamearray[$currid]);
			if ($len > MAX_SYMBOLS_LISTBOX)  {
				$currnamearray[$currid] = mb_substr($currnamearray[$currid],  0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
			}			

			$currmenu[$currid] = $cc . '. ' . $currnamearray[$currid] . " ($numag)";
        }

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    echo '<tr><td>'.get_string('curriculum', 'block_dean') . ':</td><td>';
		popup_form("checkstudents.php?cid=", $currmenu, 'switchcurr', $cid, '', '', '', false);
	    echo '</td></tr>';
		echo '</table>';
    }

    if ($cid != 0)	{

	    if ($curriculum = get_record ('dean_curriculum', 'id', $cid))		{

		    // if ($disciplines = get_records ('dean_discipline', 'curriculumid', $curriculum->id))    {
	    	$strsql = "SELECT DISTINCT courseid, name
					  FROM mdl_dean_discipline
					  where curriculumid = $cid";
			if ($disciplines = get_records_sql ($strsql))	{
				// print_r($disciplines);

			    $str1 = get_string('curriculum','block_dean') . ' ---> ' . get_string('discipline','block_dean');
				$table->head  = array ($str1, get_string('academygroup','block_dean'), get_string('groups'), 'ID students');
				$table->align = array ("left", "left", "center", "center");

	   			$table->data[] = array ($cc . '. ' . $curriculum->name);

                $NUMFORS = 0;
		    	foreach ($disciplines as $discipline)	 {

           			if ($discipline->courseid == 1)  continue;
/*
					$strsql = "SELECT ALL g.courseid,  m.userid, Count(m.userid) AS cc
							   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id =  m.groupid
							   GROUP BY g.courseid, m.userid
							   HAVING (g.courseid={$discipline->courseid}) AND (Count(m.userid)>1)";
*/
					$strsql = "SELECT m.id, g.courseid,  m.userid, Count(m.userid) AS cc
							   FROM mdl_groups as g, mdl_groups_members as m
							   WHERE g.id =  m.groupid
							   GROUP BY g.courseid, m.userid
							   HAVING (g.courseid={$discipline->courseid}) AND (Count(m.userid)>1)";

					if ($duplstuds = get_records_sql($strsql))	{
						print_r($duplstuds);
						$table->data[] = array ("<strong>--><a href=\"{$CFG->wwwroot}/course/view.php?id={$discipline->courseid}\">$discipline->name</a></strong>");
						// print_table($table); exit();

						foreach ($duplstuds as $duplstud)	{
							$NUMFORS++;
							/*
							if ($NUMFORS>70) {
								print_table($table);
								exit();
							}
							*/

                            $strsql = "SELECT g.id, g.name, g.courseid, m.userid
									   FROM mdl_groups as g INNER JOIN mdl_groups_members as m ON g.id = m.groupid
									   WHERE (g.courseid={$discipline->courseid}) AND (m.userid={$duplstud->userid})";
							if ($duplgroups = get_records_sql($strsql))	{
								$strsql = "SELECT mdl_dean_academygroups.name, mdl_dean_academygroups_members.userid
										   FROM mdl_dean_academygroups INNER JOIN mdl_dean_academygroups_members ON mdl_dean_academygroups.id = mdl_dean_academygroups_members.academygroupid
										   WHERE mdl_dean_academygroups_members.userid={$duplstud->userid}";
								if ($acadstud = get_record_sql($strsql))	{
									foreach ($duplgroups as $duplgroup)	 {
										$table->data[] = array ('', $acadstud->name, $duplgroup->name, $duplstud->userid);
										if ($acadstud->name != $duplgroup->name)	{
					 	                	 delete_records('groups_members', 'groupid', $duplgroup->id, 'userid', $duplstud->userid);
										}
									}
								}
							}
						}
					}
           		}
				print_table($table);
		    }
		}
	}


/*
	$numcurr += KOL_CURRICULUM;
			?>
			<table align="center">
				<tr><td>
				  <form name="delalldisc" method="post" action="checkgroups.php">
					    <div align="center">
						<input type="submit" name="continueprocess" value="<?php print_string('continue')?>">
						<input type="hidden" name="numcurr" value="<?php echo $numcurr ?>"/>
				  	    </div>
				  </form>
			    </td></tr>
			 </table>
			<?php

*/
    print_footer();

/*
				if ($academygroups = get_records('dean_academygroups',  'curriculumid', $curriculum->id, 'name'))		{

				    foreach ($academygroups as $academygroup)	{
				        if (strlen($academygroup->name) != 6)	continue;

			   			$table->data[] = array ('', $academygroup->name);

						if ($damem = get_records('dean_academygroups_members', 'academygroupid', $academygroup->id, 'userid')) 	{
							$dean_amembers = count($damem);
						} else {
							$dean_amembers = 0;
						}
*/

    /*
    if ($curriculums = get_records ('dean_curriculum', 'formlearning', 'correspondenceformtraining', 'enrolyear'))		{
        $cc = 0;
        print_heading( 'Количество рабочих учебных планов ' . count($curriculums), 'left', 2);
		foreach ($curriculums as $curriculum)	{
			$cc++;
			if ($cc <= $numcurr) continue;
			if ($cc > $numcurr+KOL_CURRICULUM) break;

		    if ($disciplines = get_records ('dean_discipline', 'curriculumid', $curriculum->id))  {
		    	 $disc_id_courses = array();
		    	 foreach ($disciplines as $discipline)	 {
			    	if ($discipline->courseid != 1)  {
						$disc_id_courses[] = $discipline->courseid;
					}
		    	 }
		    }
            */
           // print_r($disc_id_courses); echo '<hr>';
           // continue;
           // print_heading( $cc . '. ' . $curriculum->name, 'center', 4);

?>