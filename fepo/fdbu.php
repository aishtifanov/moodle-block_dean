<?php // $Id: fdbu.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->dirroot.'/course/lib.php');

   // require_login();

    if (!$site = get_site())  {
        redirect('index.php');
    }

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$quizid_start	= 7667;
	$quizid_end 	= 8445;

	// $courseid = 1711;

    $strmonit = 'Test updating ...';

    print_header("$site->shortname: $strmonit", $site->fullname, $strmonit);

    print_heading($strmonit);

    for ($quizid=$quizid_start; $quizid<=$quizid_end; $quizid++)	{

            if ($quiz = get_record('quiz', 'id', $quizid))	{

                    $qanswers = explode (',', $quiz->questions);

                    $numansw = 0;
                    foreach ($qanswers as $qa) {
                    	 if ($qa > 0) $numansw++;
                    }

                    if ($numansw > 0)	{
						$quiz->sumgrades = $numansw;
						$quiz->grade = $numansw;
						$quiz->timelimit = $numansw*2;
						$quiz->review = 62415;
						$quiz->attempts = 99;

						if (update_record('quiz', $quiz))	{
							notify('QUIZ updated !!!', 'green');
						} else {
							error("Could not quiz updated!" . $quizid . ' ' . $quiz->name);
						}
					}  else {
						notify('NUM ANSWERS = 0 !!!');
					}
			}
	}

    print_footer($site);

?>