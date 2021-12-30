<?php // $Id: lib_startpage.php,v 1.10 2013/09/03 14:01:07 shtifanov Exp $


function print_my_moodle_dean($term, $dgroup)
{
/// Prints custom user information on the home page.
/// Over time this can include all sorts of information

    global $USER, $CFG;
        
    if (!isset($USER->id)) {
        error("It shouldn't be possible to see My Moodle without being logged in.");
    }

	$count_a = count_records('dean_academygroups_members', 'userid', $USER->id);
    if ( $count_a == 1)	{

        $dean_member = get_record_sql("SELECT id, academygroupid FROM mdl_dean_academygroups_members
        							   WHERE userid = {$USER->id}");
        $ret = print_tabs_one_group($dean_member, $term);
		
		if ($ret == true) return $ret;							   
	} else if ($count_a > 1)	{
        $dean_members = get_records_sql("SELECT id, academygroupid FROM mdl_dean_academygroups_members
        							   WHERE userid = {$USER->id}");
        $dean_member_print =  current($dean_members);							   
        $toprow = array();
        foreach ($dean_members as $dean_member)	{
        	if ($agroup =  get_record_sql("SELECT id, curriculumid, name, startyear FROM mdl_dean_academygroups
        							   WHERE id = {$dean_member->academygroupid}"))	{
				$agroupname = substr($agroup->name, 0, 8); 
    	    	if (strlen($agroupname) == 8 && is_numeric($agroupname))	{
		   	       $toprow[] = new tabobject($agroup->name, 'index.php?dg='.$agroup->name, $agroup->name);
		   	       if ($dgroup == 0)	{
		   	       		$dean_member_print =  $dean_member;
		   	       		$dgroup = $agroup->name;
		   	       } else if ($agroup->name == $dgroup)	{
		   	       		 $dean_member_print =  $dean_member;
		   	       }
		   	    } else {
					print_heading_block(get_string('mycourses'));
				   	print_my_moodle();
				   	return false;
		   	    }   
	   	    }
		}	      
        $tabs = array($toprow);
        print_heading_block(get_string('disciplinestermandgroup', 'block_dean'));
		print_tabs($tabs, $dgroup, NULL, NULL);
		$ret = print_tabs_one_group($dean_member_print, $term, false);
		if ($ret == true) return $ret;		
	}	

	print_heading_block(get_string('mycourses'));
    

    $courses = get_my_courses($USER->id, 'visible DESC,fullname ASC', array('summary'));
    
    $countcorses = count($courses);
    
    if ($countcorses < 60)  {
   	    print_my_moodle();        
    } else {

        $arrRus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'К', 'Л', 'М',
                      'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я');
                      
        $toprow = array();
        foreach ($arrRus as $key => $aRus)	{
           $toprow[] = new tabobject($key, 'index.php?dg='.$key, $aRus);
    	}	      
        $tabs = array($toprow);
        print_tabs($tabs, $dgroup, NULL, NULL);
                      
        echo '<ul class="unlist">';
        foreach ($courses as $course) {
            if ($course->id == SITEID) {
                continue;
            }
            $sym = mb_substr($course->fullname, 0, 2);
            $key = array_search($sym, $arrRus); 
            if ($key === false ) {
                if (strlen($sym) == 2 && $dgroup == 0)  {
                    echo '<li>';
                    print_course($course);
                    echo "</li>\n";
                } 
            } else if ($dgroup == $key) {
                echo '<li>';
                print_course($course);
                echo "</li>\n";
            }    
        }
        echo "</ul>\n";
    }   
    
    /*                 
    $countsym = array();
    foreach ($arrRus as $arrR)  {
        $countsym[] = 0;
    }              
    
    foreach ($courses as $course) {
        // print_r($course); break;
        $sym = mb_substr($course->fullname, 0, 2);
        $key = array_search($sym, $arrRus); 
        if ($key === false ) {
            notify($sym . 'not found');
        } else {
            $countsym[$key]++;
        }
    }
    
    foreach ($countsym as $key => $cntsym)  {
        notify ($arrRus[$key] . ': '. $cntsym, 'green', 'left');
    }
    
    echo count($courses); 
    exit();
    */
   	return false;
    
}


function print_tabs_one_group($dean_member, $term, $printheadingblock = true)
{
	global $USER, $CFG;
        // print_r($dean_member); echo '<hr>';

        if ($agroup =  get_record_sql("SELECT id, curriculumid, name, startyear FROM mdl_dean_academygroups
        							   WHERE id = {$dean_member->academygroupid}"))	{
			$agroupname = substr($agroup->name, 0, 8);
            $len = strlen($agroupname); 
        	if ( ($len == 6 || $len == 8) && is_numeric($agroupname))	{

		        // print_r($agroup); echo '<hr>';
		  		// print_tabs_semestr
                
				$year = date("Y");
			    $m = date("n");
                
                $numgroup = substr($agroupname, -2);
                if ($numgroup < 50)	{ 
                    $startm = 1;
                    $endm   = 6;
                } else {
                    $startm = 1;
                    $endm   = 2;
                }    
                    
                    
			    if(($m >= $startm) && ($m <= $endm)) {
					$curryear = $year-1;
			    } else {
					$curryear = $year;
			    }

                if ($agroup->startyear < 2000) $agroup->startyear = $curryear;

            	$endterm = ($curryear - $agroup->startyear + 1)*2; // 8 - 8; 8 - 7; 8 - 6

		 	    for ($i = 1; $i <= $endterm; $i++)   {
		   	       $toprow3[] = new tabobject($i, "index.php?term=$i&amp;dg={$agroup->name}", $i);
		    	}
	    	    $toprow3[] = new tabobject(13, "index.php?term=13&amp;dg={$agroup->name}", get_string('all'));

		        $tabs3 = array($toprow3);

			    if ($printheadingblock) {
			    	print_heading_block(get_string('disciplinesterm', 'block_dean'));	
			    }
				// print_heading(get_string('terms','block_dean'), 'center', 4);
				if ($term == 0)	{
					// if ($curryear == $year)	{
					$oddmonth = array(1,2,8,9,10,11,12);	
					if (in_array($m, $oddmonth)) {
						$term = $endterm - 1;
					} else {
						$term = $endterm;
					}
				}

		 		print_tabs($tabs3, $term, NULL, NULL);


		 		if ($disciplines = get_records_sql("SELECT id, courseid FROM mdl_dean_discipline
		 											WHERE curriculumid = {$agroup->curriculumid} AND term = $term"))	{

 			        // print_r($disciplines); echo '<hr>';
					$id_disciplines = array();
			        foreach ($disciplines as $discipline) {
			        	$id_disciplines[] = $discipline->courseid;
			        }
					if ($courses = get_my_courses($USER->id, 'fullname ASC', array('summary')))	{	
					
				        foreach ($courses as $course) {
				        	if (in_array($course->id, $id_disciplines))	{
				        		print_course($course);
				        	}
				        }
				        return true;
				    }

                } else {
                	if ($term == 13) {

				        $strsummary  = get_string("summary");
				        $strassignteachers  = get_string("assignteachers");
				        $strallowguests     = get_string("allowguests");
				        $strrequireskey     = get_string("requireskey");

				        echo '<table align="center" border="0" cellspacing="2" cellpadding="4" class="generalbox"><tr>';
			            echo '<th>&nbsp;</th>';
   				        echo '</tr>';

                        if ($courses = get_my_courses($USER->id, 'fullname ASC')) {

					        foreach ($courses as $acourse) {

					            $linkcss = $acourse->visible ? "" : ' class="dimmed" ';
					            echo '<tr>';
					            echo '<td><a '.$linkcss.' href="/course/view.php?id='.$acourse->id.'">'.$acourse->fullname.'</a></td>';

				                echo '<td align="left">';
				                if (!empty($acourse->guest)) {
				                    echo '<a href="/course/view.php?id='.$acourse->id.'"><img hspace="2" title="'.
				                         $strallowguests.'" alt="" height="16" width="16" border="0" src="'.
				                         $CFG->pixpath.'/i/user.gif" /></a>';
				                }
				                if (!empty($acourse->password)) {
				                    echo '<a href="/course/view.php?id='.$acourse->id.'"><img hspace="2" title="'.
				                         $strrequireskey.'" alt="" height="16" width="16" border="0" src="'.
				                         $CFG->pixpath.'/i/key.gif" /></a>';
				                }
				                if (!empty($acourse->summary)) {
				                    link_to_popup_window ("/course/info.php?id=$acourse->id", "courseinfo",
				                                          '<img hspace="2" alt="info" height="16" width="16" border="0" src="'.$CFG->pixpath.'/i/info.gif" />',
				                                           400, 500, $strsummary);
				                }
				                echo "</td>";
					            echo "</tr>";
					        }
       					}

				        echo '</table>';
				        echo '<br />';
                	}
               		return true;
                }
		 	} 
		} 
	return false;	
}    

?>
