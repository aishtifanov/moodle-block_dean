<?php // $Id: jodb_gift.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/course/lib.php');


   // require_login();

    if (!$site = get_site())  {
        redirect('index.php');
    }

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


	// $courseid = required_param('c', PARAM_INT);			// Course id
	//$numsection = required_param('s', PARAM_INT);
	$courseid = 1700;

	// $numsection = 2814;
    $pathtopicture = 'img src = "http://pegas.bsu.edu.ru/file.php/' . $courseid . '/testimgs/';

    $strmonit = 'Test convertation ... (HTML)';

    print_header("$site->shortname: $strmonit", $site->fullname, $strmonit);

    print_heading($strmonit);

	$path_dir = $CFG->dataroot . '/1/h/';

	// rebuild_course_cache($courseid); 	exit();
	$dirlist = list_directories_and_files (trim($path_dir));
    // print_r($dirlist);
	foreach ($dirlist as $file1) {

	// foreach ($string_files as $file1 => $numsection)	{

	   $fn = $path_dir . $file1;

		$path_parts = pathinfo($fn);
		$path_parts_name = $path_parts["basename"];
		$path_parts_ext = $path_parts["extension"];
		$path_parts_shortname = substr ($path_parts_name, 0, strlen($path_parts_name)- 4);

		if ($path_parts_ext != "htm") {
		    continue;
		}

	   if (!file_exists($fn))  {
	       notify ("Файл $fn не найден.");
	       // break;
	       continue;
	   }
	   // Получить содержимое файла в виде массива
	   // $pages = file ($fn);
	   $line = file_get_contents ($fn);

	   if ($line == FALSE)  {
	       notify ("Файл $fn!");
		   break;
	   }


		$pos = strpos($line, '<body');

		if ($pos === false) {
		    error("Строка '<body' не найдена в строке '$line'");
		}

	    $line = substr ( $line, $pos);

	    $line = str_replace("<p ", "<z><p ", $line);

		$line = str_replace("\n", " ", $line);
		$line = str_replace("\r", "", $line);

	    $clearline = strip_tags ($line , '<z><u><b><i><img><hr/><sub><sup><br/>');

	    $clearline = str_replace('<b>+</b>', '+', $clearline);

	    $clearline = str_replace('<i>+</i>', '+', $clearline);

	    $clearline = str_replace('<u>+</u>', '+', $clearline);

		$clearline = str_replace('src="', $pathtopicture, $clearline);

		$lines = explode ('<z>', $clearline);

		// print_r($lines);

        // foreach ($lines as $key => $line)	{
   	    $nameoftest = $lines[2];

        print_heading($nameoftest);

        $num = count ($lines);
        $i=10;
        $k=1;
        $strtest = '';
        while ($i < $num)	{
        	$shablon = substr(ltrim($lines[$i]), 0, 6);

        	if ($shablon == '##time')	{
				$shablon = substr(ltrim($lines[$i+1]), 0, 6);
				if ($shablon == '##them')  {					$i++;
					continue;
				}

        	    // echo "<br><br>// Вопрос: $k<br>";
        	    $strtest .= "\n\n// Вопрос: $k\n";
        	    $k++;
        	    // echo $lines[$i+1] . '  {';
        	    $strtest .= trim($lines[$i+1]) . '  {';
        	    $i += 2;

        	    $flag = true;
        	    $arrqanswer = array();     $numtrue  = 0;
        	    $arrqanswerbin = array();  $numfalse = 0;
        	    while ($flag && $i<$num)	{
        	        $qanswer = trim($lines[$i]);

 	      	 	    if (!empty($qanswer))	{

	                    $clearline = strip_tags($qanswer);
	    				$shablon = substr($clearline, 0, 6);
	    				$sym = substr($clearline, 0, 1);

	    				if ($shablon == '##them')	{
							$i++;
							$flag = false;
							break;
						}

	                    if ($sym == '+')	{
 		                   	 $sym4 = substr($qanswer, 0, 4);
                    		 if ($sym4 == '<b>+')	{
	                    	 	$arrqanswer[] = substr($qanswer, 5);
 		                   	 } else {
	                     	 	$arrqanswer[] = substr($qanswer, 1);
			                 }
	   	 	        	    $arrqanswerbin[] = true;
 	  	 	        	    $numtrue++;
                   		 } else  if ($shablon != '&nbsp;')  {
	                   	 	$arrqanswer[] = $qanswer;
	   	 	        	    $arrqanswerbin[] = false;
	   	 	        	    $numfalse++;
	                    }
	                }
                    $i++;
        	    }

                if ($numtrue == 1)	{
                    $is_single = 1;
	                foreach ($arrqanswer as $key => $question)	{	                	if ($arrqanswerbin[$key])	{	                		$strtest .= "\n=".$question;	                	} else {
		                	$strtest .= "\n~". $question;	                	}	                }
	            } else {
                    $is_single = 0;

                    if ($numtrue == 0 ||  $numfalse  == 0)	{	                    print_r($arrqanswer); echo '<hr>';
	                	print_r($arrqanswerbin); echo '<hr>';
	                }
	            	$vestrue  = get_ves($numtrue);
	            	$vesfalse = get_ves($numfalse);

	                foreach ($arrqanswer as $key => $question)	{
	                	if ($arrqanswerbin[$key])	{
	                		$strtest .= "\n~%$vestrue%".$question;
	                	} else {
		                	$strtest .= "\n~%-$vesfalse%". $question;
	                	}
	                }
	            }
        	    // echo '<br>}';
        	    $strtest .= "\n}";

        	} else {        		$i++;        	}

        }

       // echo $strtest;

		// file_put_contents ( $path_dir . '1.txt',  $clearline);
	    if (!$handle = fopen($path_dir . $path_parts_shortname . '_gift.txt', 'w')) {
	         echo "Не могу открыть файл ($filename)";
 	         exit;
  		}

    // Записываем $somecontent в наш открытый файл.
	    // if (fwrite($handle, $clearline) === FALSE) {		if (fwrite($handle, $strtest) === FALSE) {
 	       echo "Не могу произвести запись в файл ($filename)";
  	       exit;
   		}

	    fclose($handle);
	}

    print_footer($site);


// This function return the names of all directories and files under a give directory
// Not recursive
function list_directories_and_files ($rootdir)
{

        $results = "";

        $dir = opendir($rootdir);
        while ($file=readdir($dir)) {
            if ($file=="." || $file=="..") {
                continue;
            }
            $results[$file] = $file;
        }
        closedir($dir);
        return $results;
}

function get_ves($num)
{
    if ($num == 0)	return 0;

   	$ves  = round(1.0 / $num, 5);

   	if ($ves > 0.33 && $ves < 0.34)	{
   		$ves = '0.33333';
   	} else 	if ($ves > 0.16 && $ves < 0.17)	{
   		$ves = '0.16666';
   	} else 	if ($ves > 0.14 && $ves < 0.15)	{
   		$ves = '0.14286';
   	}

   	return $ves;
}


/*
SELECT cm.id as coursemodule, m.*,cw.section,cm.visible as visible,cm.groupmode
FROM mdl_course_modules cm, mdl_course_sections cw, mdl_modules md, mdl_quiz m
WHERE cm.course = '1711' AND cm.instance = m.id AND cm.section = cw.id AND md.name = 'quiz' AND md.id = cm.module

*/

?>
