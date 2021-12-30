<?php // $Id: printcrt.php,v 1.4 2011/12/06 08:37:13 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../../lib/textlib.class.php');
    require_once('../lib.php');

    $certid = required_param('certid', PARAM_INT);   // Certificate id
    $uid = required_param('uid', PARAM_INT);         // User id
	$gid = required_param('gid', PARAM_INT);		 // Academygroup ID

	$txtl = new textlib();
	
	if ($fp = fopen('1.mht', "r"))	{
		$fstat = fstat($fp);
		$buffer = fread($fp, $fstat['size']);

	    if ($user = get_record_sql("select firstname, lastname from {$CFG->prefix}user where id=$uid"))		{

			if ($cert = get_record('dean_certificate', 'id', $certid)) {

                if (isset($user->firstname) && !empty($user->firstname)) {
                 	   list($f,$s) = explode(' ', $user->firstname);
                       $user->firstname = $f;
                       $user->secondname = trim($s);
                } else {
                       $user->secondname = '';
                }
                $user->dativecase  =  dativecase($user->lastname, $user->firstname, $user->secondname);

				$user->lastnamedative  =  $txtl->convert($user->lastnamedative, 'utf-8', 'windows-1251');
				$user->firstnamedative =  $txtl->convert($user->firstnamedative, 'utf-8', 'windows-1251');
              
                /*                
				$buffer = str_replace('lastname', digit2russym($fio->lastname), $buffer);
				$buffer = str_replace('firstname', digit2russym($fio->firstname), $buffer);
                */
				$buffer = str_replace('lastname',  $user->lastnamedative, $buffer);
				$buffer = str_replace('firstname', $user->firstnamedative, $buffer);

                if ($discipline = get_record('dean_discipline', 'id', $cert->disciplineid))	{
					$strwin1251 =  $txtl->convert($discipline->name, 'utf-8', 'windows-1251');				
					$buffer = str_replace('courses', $strwin1251, $buffer);
				}

				$buffer = str_replace('kol', $cert->hours, $buffer);

			    $datestring = get_rus_format_date($cert->datecreated, 'full');
				$strwin1251 =  $txtl->convert($datestring, 'utf-8', 'windows-1251');
                $strwingod =  $txtl->convert(' г.', 'utf-8', 'windows-1251');			    
				$buffer = str_replace('datecreated', $strwin1251  . $strwingod, $buffer);

				$date = date('Y');
				$buffer = str_replace('year', $date, $buffer);

				$strwin1251 =  $txtl->convert($cert->number, 'utf-8', 'windows-1251');
				$buffer = str_replace('certificate', $strwin1251, $buffer);

                $filename =  'certificate_' . translit_russian($user->lastname) . '.xls';

				header("Content-type: application/vnd.ms-word");
				header("Content-Disposition: attachment; filename=\"$filename\"");
				header("Expires: 0");
				header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
				header("Pragma: public");

				print $buffer;
			}
		}
	}


function translit_russian($input)
{
  $arrRus = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м',
                  'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ь',
                  'ы', 'ъ', 'э', 'ю', 'я',
                  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М',
                  'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ь',
                  'Ы', 'Ъ', 'Э', 'Ю', 'Я');
  $arrEng = array('a', 'b', 'v', 'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'y', 'k', 'l', 'm',
                  'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'kh', 'c', 'ch', 'sh', 'sch', '',
                  'y', '', 'e', 'ju', 'ja',
                  'A', 'B', 'V', 'G', 'D', 'E', 'JO', 'ZH', 'Z', 'I', 'Y', 'K', 'L', 'M',
                  'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'KH', 'C', 'CH', 'SH', 'SCH', '',
                  'Y', '', 'E', 'JU', 'JA');
  return str_replace($arrRus, $arrEng, $input);
}



function digit2russym($input)
{
   $cyr = array('а','А','б','Б','в','В','г','Г','ґ','Ґ','д','Д','е','Е','є','Є','ё','Ё','ж','Ж','з','З','і','І','ї','Ї','и','И','й','Й','к','К','л','Л','м','М','н','Н','о','О','п','П','р','Р','с','С','т','Т','у','У','ф','Ф','х','Х','ц','Ц','ч','Ч','ш','Ш','щ','Щ','ъ','Ъ','ы','Ы','ь','Ь','э','Э','ю','Ю','я','Я');
   $cyr_c= array(1072,1040,1073,1041,1074,1042,1075,1043,1169,1168,1076,1044,1077,1045,1108,1028,1105,1025,1078,1046,1079,1047,1110,1030,1111,1031,1080,1048,1081,1049,1082,1050,1083,1051,1084,1052,1085,1053,1086,1054,1087,1055,1088,1056,1089,1057,1090,1058,1091,1059,1092,1060,1093,1061,1094,1062,1095,1063,1096,1064,1097,1065,1098,1066,1099,1067,1100,1068,1101,1069,1102,1070,1103,1071);
 

   $ilen = strlen($input);
   $clen = count($cyr);
   $ret = '';
   for ($i=0; $i<$ilen; ++$i) {
   	   	$flag = true;
		for($j=0; $j<$clen; $j++)	{
			if($input[$i] == $cyr[$j])	{
				$ret .= '&#' . $cyr_c[$j] . ';';
				$flag = false;
			}
		}
		if ($flag)	{
			$ret .= $input[$i];
		}
   }		
   return $ret;
}
?>