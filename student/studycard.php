<?php // $Id: studycard.php,v 1.2 2011/02/10 11:56:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 5, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);          // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = required_param('uid', PARAM_INT);          // User id


    if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }

	if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	}
	elseif (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
    }

    include('incl_stud.php');

	$flag = false;
	if ($mode == 5) 	{
	  if (!$user = get_record("user", "id", $uid) ) {
	        error("No such user in this system.");
      }

      $user_student = get_record("dean_student_studycard", "userid", $uid);

	  if ($studentnew = data_submitted()) {
		  // print_r($studentnew);
          $flag = true;
          // unset($user_student);
          $user_student->userid = $user->id;
		  $user_student->facultyid = $fid;
		  $user_student->specialityid = $sid;
		  $user_student->recordbook = $studentnew->recordbook;
		  //$user_student->lastname = $studentnew->lastname;
		  // $user_student->firstname = $studentnew->firstname;
		  $user_student->birthday = make_timestamp($studentnew->syear, $studentnew->smonth, $studentnew->sday);
		  $user_student->whatgraduated = $studentnew->whatgraduated;
		  $user_student->previousdiploma = $studentnew->previousdiploma;
		  $user_student->issueday = make_timestamp($studentnew->iyear, $studentnew->imonth, $studentnew->iday);
		  $user_student->job = $studentnew->job;
		  $user_student->appointment = $studentnew->appointment;
		  $user_student->financialbasis = $studentnew->financialbasis;
		  $user_student->ordernumber = $studentnew->ordernumber;

		  if ($studentnew->R1 == 'V1')  $user_student->enrolmenttype = 10;
		  else if ($studentnew->R1 == 'V2')  $user_student->enrolmenttype = 11;
		 	    else if ($studentnew->R1 == 'V3')  $user_student->enrolmenttype = $studentnew->typeenrolment;

	  	 if (find_form_studycard_errors($studentnew, $err) == 0) {


        	   if (!$student1 = get_record("dean_student_studycard", "userid", $uid) ) {
      	        // error("Student have not study card. Make update table.");
      				 if (insert_record('dean_student_studycard', $user_student))	{
      					add_to_log(1, 'dean', 'dean_student_studycard insert', 'blocks/dean/student/studycard.php', $USER->lastname.' '.$USER->firstname);
      	           		redirect("$CFG->wwwroot/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
                                        get_string("changessaved"));
      				 } else  {
      					 error(get_string('errorincreatingstudentstudycard','block_dean'), "$CFG->wwwroot/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}");
      	 	 	     }
      	   	   } else  {
        				// $studentnew->timemodified = time();
        			  if (update_record('dean_student_studycard', $user_student))	{
      	  				 add_to_log(1, 'dean', 'dean_student_studycard update', 'blocks/dean/student/studycard.php', $USER->lastname.' '.$USER->firstname);
      	        		 redirect("$CFG->wwwroot/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
                                        get_string("changessaved"));
      				  } else  {
      						error(get_string('errorinupdatingstudentstudycard','block_dean'), "$CFG->wwwroot/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}");
      	 		   	  }
      	 		}
         }
	  }

      if (!$flag)	{
	    if (!$user_student = get_record("dean_student_studycard", "userid", $uid) ) {
 	       $user_student->userid = $user->id;
		   $user_student->facultyid = $fid;
      	   $user_student->specialityid = $sid;
      	   $user_student->recordbook = 0;
      	   $user_student->birthday = time();
      	   $user_student->whatgraduated = '';
      	   $user_student->previousdiploma = '';
      	   $user_student->issueday = time();
      	   $user_student->job = '';
      	   $user_student->appointment = '';
           $user_student->financialbasis = 'fd';
		   $user_student->ordernumber = '0';
   		   $user_student->enrolmenttype = 10;
        }
      }


    	$fullname = fullname($user);
	    $personalprofile = get_string("personalprofile");
	    $participants = get_string("participants");

	    if ($user->deleted) {
	        print_heading(get_string("userdeleted"));
	    }

	    $currenttab = 'studycard';
	    include('tabstudent.php');

	    print_dean_box_start("center");

	    include("studycard.html");

    	print_dean_box_end();
	}

print_footer();

/// FUNCTIONS ////////////////////
function find_form_studycard_errors(&$rec, &$err)
{

    if (empty($rec->recordbook) || $rec->recordbook==0)	{
		    $err["recordbook"] = get_string("missingname");
		}
		else if (!ctype_digit($rec->recordbook))  {
            $err["code"] = get_string("errordigit",'block_dean');
		}
    /*

    if (empty($rec->whatgraduated)) 	{
			$err["whatgraduated"] = get_string("missingname");
		}


    if (empty($rec->ordernumber))	{
			$err["ordernumber"] = get_string("missingname");
		}
    */

    return count($err);

	return 0;
}



?>

