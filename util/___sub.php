<?php // $Id: __sub.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');

	$strtitle = '__ Delete %<sub>___ %</sub>';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean', 'block_dean').'</a>';
	$breadcrumbs .= " -> " . $strtitle;
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	$currtime = time();
	
	$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			where answer like'\\%<sub>%' ";	
	if ($subs = get_records_sql($strsql))	{
	
		foreach ($subs as $sub) {
			echo '<hr>'; print_r($sub); echo '<br>';  
			
			$str30 = substr($sub->answer, 0, 30);
			
			echo "str30 = $str30<br>";
			
			$findme   = '%</sub>';
			$pos = strpos($str30, $findme);
			if ($pos === false) {
				notify ('&lt;sub&gt; not found');
				continue;
			}	
			
			echo "pos = $pos<br>";
			
			$strnormal = substr($sub->answer, $pos+7);
			
			echo "strnormal = $strnormal<br>";
			
			$number = '';
			
			
			for ($i=0; $i<30; $i++) {
				
				if ($str30[$i] == '-' || $str30[$i] == '.' || ctype_digit($str30[$i]))	{
					$number .= $str30[$i]; 
				}
			}			
			
			echo "number= $number<br>";
			
			$number /= 100;
			
			echo "number= $number<br>";
			
			$rec->id = $sub->id;
			$rec->answer = $strnormal;  
			$rec->fraction = $number;
			
			if (!update_record('question_answers', $rec))	{
				print_r($rec);
				error("Error in update question_answers");
			} 
		}
 	} else {
 		// notice ('Records not found!', 'index.php');
 		notify ('Records not found!');
 	}
 	
 	$procents = array ('16.667', '33,3333', '33,333', '33,333', '33,33', '33,3', '33,4', '14,2', '12,5', '16,667', '16,6', 
	 					'100', '70', '50', '40', '25', '20', '30', '35');
 	
 	
 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\%$procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% $procent");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% $procent", '', $sub->answer);
						}	
					} else {
						$strnormal = str_replace ("%$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= 100;
				echo "number= $number<br>";	
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
			}	  
		}
	}		
 	
 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\%-$procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%-$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%-$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% -$procent");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% -$procent", '', $sub->answer);
						}	
						
					} else {
						$strnormal = str_replace ("%-$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%-$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= -100;
				echo "number= $number<br>";		
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
						
			}	  
		}
	}		


 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\% $procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% $procent%");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% $procent%", '', $sub->answer);
						}	
					} else {
						$strnormal = str_replace ("%$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= 100;
				echo "number= $number<br>";	
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
			}	  
		}
	}		
 	
 	foreach ($procents as $procent)	{
		$strsql = "SELECT id, answer, fraction FROM mdl_question_answers 
			       WHERE answer like'\\% -$procent%' ";
		// echo $strsql . '<br>'; 		   	
		if ($subs = get_records_sql($strsql))	{
			echo '<hr><hr><hr>';
			foreach ($subs as $sub) {
				echo '<hr>'; print_r($sub); echo '<br>';
				
				$pos = strpos($sub->answer, "%-$procent%");
				if ($pos === false) {
					$pos = strpos($sub->answer, "%-$procent");
					if ($pos === false) {
						$pos = strpos($sub->answer, "% -$procent%");
						if ($pos === false) {
						} else {
							$strnormal = str_replace ("% -$procent%", '', $sub->answer);
						}	
						
					} else {
						$strnormal = str_replace ("%-$procent", '', $sub->answer);
					}	
					
				} else {
					$strnormal = str_replace ("%-$procent%", '', $sub->answer);
				}	

				// $strnormal = str_replace ($strnormal, '', $sub->answer);
				$strnormal = trim ($strnormal);				
				echo "strnormal = $strnormal<br>";
			
				$number = str_replace (",", '.', $procent);
				$number /= -100;
				echo "number= $number<br>";		
				
				$rec->id = $sub->id;
				$rec->answer = $strnormal;  
				$rec->fraction = $number;
				
				if (!update_record('question_answers', $rec))	{
					print_r($rec);
					error("Error in update question_answers");
				} 
						
			}	  
		}
	}		
 	
	print_footer();
?>