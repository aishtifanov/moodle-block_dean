<?php
    require_once("../../config.php");


$names = array();
/*
$names[]='Озерова Юлия Александровна';
$names[]='Бондаренко Байрамкиз Агабалаевна';
$names[]='Сидельникова Ирина Ивановна';
$names[]='Назарова Наталья Васильевна';
$names[]='Новосельцева Татьяна Николаевна';
$names[]='Воронин Игорь Юрьевич';
$names[]='Булавина Ирина Анатольевна';
$names[]='Варфоломеева Маргарита Ивановна';
$names[]='Новак Вероника Юрьевна';
$names[]='Оноприенко Инна Григорьевна';
$names[]='Полунина Людмила Васильевна';
$names[]='Волошина Ирина Геннадьевна';
$names[]='Шевченко Валентина Николаевна';
$names[]='Фурманова Татьяна Николаевна';
$names[]='Белоусова Людмила Ивановна';
$names[]='Маматов Евгений Михайлович';
$names[]='Бондаренко Виктория Александровна';
$names[]='Келеберда Анна Сергеевна';
$names[]='Ерошенко Яна Борисовна';
$names[]='Зайцева Татьяна Валентиновна';
$names[]='Бочарова Галина Геннадьевна';
$names[]='Ушакова Светлана Викторовна';
$names[]='Шаталова Юлия Николаевна';
$names[]='Гулюк Лидия Александровна';
*/

$names[]='Команова Алла Юрьевна';
$names[]='Литвинова Лариса Вячеславовна';
$names[]='Пугач Владимир Сергеевич';
$names[]='Полякова Екатерина Евгеньевна';
$names[]='Дехнич Ольга Витальевна';
$names[]='Серелина Екатерина Викторовна';
$names[]='Сафронова Вера Александровна';
$names[]='Терне Елена Анатольевна';
$names[]='Амелькина Екатерина Васильевна';
$names[]='Мануйлова Екатерина Александровна';
$names[]='Семенова Марина Ивановна';
$names[]='Катаржнова Елена Петровна';
$names[]='Федорова Надежда Степановна';
$names[]='Шевелева Елена Николаевна';
$names[]='Лютова Ольга Валентиновна';
$names[]='Бондарева Марина Александровна';
$names[]='Степанова Нина Александровна';
$names[]='Золотухина Елена Ивановна';
$names[]='Ковляшенко Яна Владимировна';


foreach ($names as $name)   {
    list($l, $f, $s) = explode(' ', $name);
    $f .= ' '.$s; 
    // if ($user = $DB->get_record_select('user', "lastname = '$l' AND firstname = '$f'"))    {
    if ($user = get_record_select('user', "lastname = '$l' AND firstname = '$f'"))    {    
        // print_object($user);

        $str = 'INSERT INTO mdl_user (';
        foreach ($user as $f1 => $v) {
            if ($f1 == 'id' ) continue;
            if (!empty($v)) {
                $str .= $f1 . ',';
            }
        }
        $str .=  ') VALUES (';
        foreach ($user as $f1 => $v) {
            if ($f1 == 'id' ) continue;
            if (!empty($v)) {
                $str .= "'" . $v . "',";
            }
        }
        $str .=  ');' . '<br>';
        echo $str; 
        
    }   else {
        notify($name . ' not found!');
    } 
}  

?>