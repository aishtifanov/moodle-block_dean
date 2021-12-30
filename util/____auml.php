<?PHP // $Id: 
	require_once('../../config.php');
    require_once('lib.php');

	$strtitle = '__ Замена умляутов ';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
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

    // restore_umlaute_from_2013();
    
    // restore_umlaute_from_2013_answers();    

    restore_umlaute_from_question_match_sub_2013();
        
    notify ('<strong>Complete!!!</strong>');
    print_footer();
  

function getUmlauteArray() {
   /* 
    return array( 'Ã¶'=>'ö', 'Ã¼'=>'ü', 'Ã¤'=>'ä', 'Ã–'=>'Ö', 'ÃŸ'=>'ß', 'Ã '=>'à', 'Ã¡'=>'á', 'Ã¢'=>'â', 'Ã£'=>'ã', 'Ã¹'=>'ù',
     'Ãº'=>'ú', 'Ã»'=>'û', 'Ã™'=>'Ù', 'Ãš'=>'Ú', 'Ã›'=>'Û', 'Ãœ'=>'Ü', 'Ã²'=>'ò', 'Ã³'=>'ó', 'Ã´'=>'ô', 'Ã¨'=>'è', 'Ã©'=>'é', 
     'Ãª'=>'ê', 'Ã«'=>'ë', 'Ã€'=>'À', 'Ã'=>'Á', 'Ã‚'=>'Â', 'Ãƒ'=>'Ã', 'Ã„'=>'Ä', 'Ã…'=>'Å', 'Ã‡'=>'Ç', 'Ãˆ'=>'È', 'Ã‰'=>'É', 
     'ÃŠ'=>'Ê', 'Ã‹'=>'Ë', 'ÃŒ'=>'Ì', 'Ã'=>'Í', 'ÃŽ'=>'Î', 'Ã'=>'Ï', 'Ã‘'=>'Ñ', 'Ã’'=>'Ò', 'Ã“'=>'Ó', 'Ã”'=>'Ô', 'Ã•'=>'Õ', 
     'Ã˜'=>'Ø', 'Ã¥'=>'å', 'Ã¦'=>'æ', 'Ã§'=>'ç', 'Ã¬'=>'ì', 'Ãí', 'Ã®'=>'î', 'Ã¯'=>'ï', 'Ã°'=>'ð', 'Ã±'=>'ñ', 'Ãµ'=>'õ', 
     'Ã¸'=>'ø', 'Ã½'=>'ý', 'Ã¿'=>'ÿ', 'â‚¬'=>'€');
*/     
    return array('&ouml;' => 'ö', '&uuml;' => 'ü', '&auml;' => 'ä', '&Ouml;' => 'Ö',  '&szlig;' => 'ß',
'&Agrave;' => 'À', '&Aacute;' => 'Á', '&Acirc;' => 'Â', '&Atilde;' => 'Ã', '&Auml;' => 'Ä', '&Aring;' => 'Å', '&AElig;' => 'Æ', 
'&Ccedil;' => 'Ç', '&Egrave;' => 'È', '&Eacute;' => 'É', '&Ecirc;' => 'Ê', '&Euml;' => 'Ë', '&Igrave;' => 'Ì',
'&Iacute;' => 'Í', '&Icirc;' => 'Î', '&Iuml;' => 'Ï', '&ETH;' => 'Ð', '&Ntilde;' => 'Ñ', '&Ograve;' => 'Ò', '&Oacute;' => 'Ó', 
'&Ocirc;' => 'Ô', '&Otilde;' => 'Õ', '&times;' => '×', '&Oslash;' => 'Ø', '&Ugrave;' => 'Ù', '&Uacute;' => 'Ú', 
'&Ucirc;' => 'Û', '&Uuml;' => 'Ü', '&Yacute;' => 'Ý', '&THORN;' => 'Þ', 
'&agrave;' => 'à', '&aacute;' => 'á', '&acirc;' => 'â', '&atilde;' => 'ã', '&aring;' => 'å', '&aelig;' => 'æ', '&ccedil;' => 'ç', 
'&egrave;' => 'è', '&eacute;' => 'é', '&ecirc;' => 'ê', '&euml;' => 'ë', '&igrave;' => 'ì', '&iacute;' => 'í', '&icirc;' => 'î', '&iuml;' => 'ï', 
'&eth;' => 'ð', '&ntilde;' => 'ñ', '&ograve;' => 'ò', '&oacute;' => 'ó', '&ocirc;' => 'ô', '&otilde;' => 'õ', '&divide;' => '÷', 
'&oslash;' => 'ø', '&ugrave;' => 'ù',  '&uacute;' => 'ú', '&ucirc;' => 'û', '&uuml;' => 'ü', '&yacute;' => 'ý', '&thorn;' => 'þ', '&yuml;' => 'ÿ',   
'&iexcl;' => '¡', '&cent;' => '¢', '&pound;' => '£', '&curren;' => '¤', '&yen;' => '¥', '&brvbar;' => '¦', '&sect;' => '§', '&uml;' => '¨', 
'&copy;' => '©', '&ordf;' => 'ª', '&laquo;' => '«', '&not;' => '¬', '&shy;' => '¬', '&reg;' => '®', '&macr;' => '¯', '&deg;' => '°', '&plusmn;' => '±', 
'&sup2;' => '²', '&sup3;' => '³', '&acute;' => '´', '&micro;' => 'µ', '&para;' => '¶', '&middot;' => '•', '&cedil;' => '¸', '&sup1;' => '¹', '&ordm;' => 'º', 
'&raquo;' => '»', '&frac14;' => '¼', '&frac12;' => '½', '&frac34;' => '¾', '&iquest;' => '¿');

}             

  
/*
public function fixeUmlauteDb() {                  
    $umlaute = $this->getUmlauteArray();                  
    foreach ($umlaute as $key => $value){                                         
        $sql = "UPDATE table SET tracks = REPLACE(row, '{$key}', '{$value}') WHERE row LIKE '%{$key}%'";                   
    } 
}    
*/    


function restore_umlaute_from_2013()
{
    global $DB;    

    $umlaute = getUmlauteArray();
    $umlaute2 = getUmlauteArray();

    foreach ($umlaute as $key => $value)    {    
        if ($questions = get_records_select('question_2013', "questiontext like BINARY '%{$value}%'", '', 'id, name, questiontext'))   {
            foreach ($questions as $question)   {
                // $q2014 = get_record_select('question', "id = $question->id", 'id, name, questiontext');
                /*
                print $question->id . '. ' . $question->name . '<br />';
                print $q2014->id . '. ' . $q2014->name . '<br />';
                print '<pre>' . htmlentities ($question->name) . '</pre>';
                print '<hr>';
                */
                
                $rec = new stdClass();
                $rec->id = $question->id;
                
                $questionname = $question->name;
                $questiontext = $question->questiontext; 
                
                foreach ($umlaute2 as $key2 => $value2)    {
                    $questionname = str_replace($value2, $key2, $questionname);
                    $questiontext = str_replace($value2, $key2, $questiontext);
                }    
                
                $rec->name = $questionname;
                $rec->questiontext = $questiontext;
                
                /*
                $rec->name = htmlentities ($question->name);
                $questiontext = htmlentities($question->questiontext);
                $questiontext =  str_replace('&lt;', '<', $questiontext);
                $questiontext =  str_replace('&gt;', '>', $questiontext);
                $rec->questiontext = $questiontext; 
                */
                
                // print_object($rec);
                update_record('question', $rec);
            }
        }
        print $value . ': ' . count($questions) . '<br />'; 
        // break;
    }
    
    return;
}

  
function restore_umlaute_from_2013_answers()
{
    global $DB;    

    $umlaute = getUmlauteArray();
    $umlaute2 = getUmlauteArray();

    foreach ($umlaute as $key => $value)    {    
        if ($questions = get_records_select('question_answers_2013', "answer like BINARY '%{$value}%'", '', 'id, answer'))   {
            foreach ($questions as $question)   {
                
                $questionanswer = $question->answer;
                foreach ($umlaute2 as $key2 => $value2)    {
                    $questionanswer = str_replace($value2, $key2, $questionanswer);
                }    
                
                $rec = new stdClass();
                $rec->id = $question->id;
                $rec->answer = $questionanswer;

                // print_object($rec);
                update_record('question_answers', $rec);
            }
        }
        print $value . ': ' . count($questions) . '<br />'; 
        // break;
    }
    
    return;
} 


function restore_umlaute_from_question_match_sub_2013()
{
    global $DB;    

    $umlaute = getUmlauteArray();
    $umlaute2 = getUmlauteArray();

    foreach ($umlaute as $key => $value)    {   
        if ($questions = get_records_select('question_match_sub_2013', "questiontext like BINARY '%{$value}%' OR answertext like BINARY '%{$value}%' ", '', 'id, questiontext, answertext'))   {
            foreach ($questions as $question)   {

                $rec = new stdClass();
                $rec->id = $question->id;
                $questionname = $question->answertext;
                $questiontext = $question->questiontext; 
                
                foreach ($umlaute2 as $key2 => $value2)    {
                    $questionname = str_replace($value2, $key2, $questionname);
                    $questiontext = str_replace($value2, $key2, $questiontext);
                }    
                
                $rec->answertext   = $questionname;
                $rec->questiontext = $questiontext;
                
                // print_object($rec);
                update_record('question_match_sub', $rec);
            }
        }
        print $value . ': ' . count($questions) . '<br />'; 
        // break;
    }
    
    return;
}
   
?>