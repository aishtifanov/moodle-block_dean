<?php  // $Id: graph_teachers.php,v 1.2 2009/03/17 08:48:06 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir."/graphlib.php");
//    require_once('../lib.php');
//    require_once($CFG->libdir.'/tablelib.php');
//    require_once($CFG->dirroot.'/calendar/lib.php');
//    require_once($CFG->libdir.'/filelib.php');

//print_header();
//print "fff";
    define('MAX_STRING_LENGTH', 70);

    $prid = optional_param('prid', '');
    $exlev = optional_param('exlev', 0, PARAM_INT);
/*
    switch($exlev) {
        case 1:
            if($all_documents = get_record('meta_tg_statistic_all', 'projectid', $prid, 'exlevel', 1)){

                $count_all_documents = $all_documents->totalworks;
                $n = $all_documents->two_expert;
                $otkloneno = $all_documents->otkloneno;
                $odobreno = $all_documents->odobreno;
                $konflikt = $all_documents->aktiv_conflict;

                $table->data[] = array ($count_all_documents, $n, $otkloneno, $odobreno, $konflikt);
            }
        break;
        case 2:

        break;
    }
*/

for($i=1;$i<20;$i++){	$table->data[] = array (rand(5,10),  rand(5,20), rand(5,30), rand(5,20), rand(5,15));}


    $stat_graph = array();
    $zps = array();
    //$legenda = array();

    $strheading = '';
    $maxlen = 0;
    foreach ($table->data as $key => $tabledata)    {
//        echo $tabledata[1];
        if ($key == 0)  {
            $strheading = strip_tags($tabledata[0]);
            continue;
        }
        // $legenda $tabledata[1]
        // $zps[$key-1] = $key.".";
        $len = mb_strlen($tabledata[1], 'UTF-8');
//        echo $len.'<br>';
        if ($maxlen < $len) $maxlen = $len;
        if ($len > MAX_STRING_LENGTH)    {
//              $zps[$key-1] = '';
              $zps[$key-1] = mb_substr($tabledata[1], 0,  MAX_STRING_LENGTH, 'UTF-8') . ' ...';
        }  else {
              $zps[$key-1] = $tabledata[1];
        }
        $stat_graph [$key-1] = strip_tags($tabledata[2]);
    }

    $cntbar = count($stat_graph);
    if ($cntbar <= 7)    {
       $graphwidth =  $cntbar * 100;
    } else {
       $graphwidth =  $cntbar * 50;
    }

    if ($maxlen > MAX_STRING_LENGTH)   {
       $graphheight = MAX_STRING_LENGTH*10 + 300;
    } else {
       $graphheight = $maxlen*10 + 300;
    }

$graph = new graph($graphwidth, $graphheight);
$graph->parameter['title'] 			= '';
$graph->x_data           			= $zps;
$graph->y_data['logs']   			= $stat_graph;
$graph->y_order 					= array('logs');
$graph->y_format['logs'] 			= array('colour' => 'blue','bar' => 'fill','bar_size' => 0.6);
$graph->parameter['bar_spacing'] 	= 0;
$graph->parameter['y_label_left']   = '';
$graph->parameter['label_size']		= '1';
$graph->parameter['x_axis_angle']	= 90;
$graph->parameter['x_label_angle']  = 0;
$graph->parameter['tick_length'] 	= 0;
$graph->parameter['shadow']         = 'none';
$graph->draw_stack();

?>