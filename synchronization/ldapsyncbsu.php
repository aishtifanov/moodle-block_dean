<?php // $Id: ldapsyncbsu.php,v 1.7 2012/10/19 06:24:42 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');

/*
LOAD DATA INFILE 'C:\\usr\\wwwroot\\moodledata\\1\\bsu\\bsu.csv' INTO TABLE  mdl_bsu_students
FIELDS TERMINATED BY ';' IGNORE 1 LINES (idPerson,CodePhysPerson,Name,DateRojd,idFakultet,FakultetName,idSpecyal,Specyal,idKvalif,Kvalif,idOtdelenie,Otdelenie,grup,Kurs_New,Gruppa,PodGruppa,FormObuts,idPeremetch,TextPerem,NumPrikazPost,Datepost,idKodPrith,TextPrith,NumPrikazOtsh,Dateotsh,Paralel,ParalelTxt,Zathetka)

update mdl_dean_speciality set idspecyal=0, idkvalif=0
where facultyid=1

SELECT distinct concat(idSpecyal, idKvalif) as a, idSpecyal, idKvalif, Specyal, Kvalif FROM mdl_bsu_students
where idFakultet=2
ORDER by Specyal

SELECT idspecyal, idkvalif, number, name, qualification FROM mdl_dean_speciality
where facultyid=1
ORDER BY number;


*/

    
    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id
   	$go = optional_param('go', 1, PARAM_INT);			//
    $delay = optional_param('t', 3, PARAM_INT);			//     
	
	$strsynchronization = get_string('ldapsyncbsu','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    $currenttab = 'ldapsyncbsu';
    include('ldaptabs.php');
    

    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("256M");

	print_heading($strsynchronization, 'center', 4);
	notify (get_string('description_ldapsynchbsu','block_dean'), 'black');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("ldapsyncbsu.php?fid=", $fid);
	echo '</table>';

	if ($fid != 0)  {
	   
       sync_spravochniki();
		
        // idPerson, CodePhysPerson, Name, DateRojd, idFakultet, FakultetName, idSpecyal, Specyal, idKvalif, Kvalif, idOtdelenie, Otdelenie, grup, Kurs_New, Gruppa, PodGruppa, FormObuts, idPeremetch, TextPerem, NumPrikazPost, Datepost, idKodPrith, TextPrith, NumPrikazOtsh, Dateotsh, Paralel, ParalelTxt, Zathetka
        // id, number, name, deanid, deanphone1, deanphone2, deanaddress, timemodified, shortname, idfakultet
		if (!$faculty = get_record_select('dean_faculty', "id=$fid", 'id, number, name, idfakultet'))	exit(1);
		
		$cod = $faculty->number - 100; 
		
		if ($cod > 21) {
	   		$fid++;
	   		redirect("ldapsyncbsu.php?fid=$fid", '', 0);
		}
		
        // синхронизация названия факультета
        
		if ($cod < 10)	{
			$shablon = '0'. $cod . '%';
		} else {
			$shablon = $cod . '%';
		}	
        
        $strsql = "SELECT distinct idFakultet, FakultetName FROM mdl_bsu_students where FakultetName like '$shablon'";
        if ($bsufacultet = get_record_sql($strsql))    {
            $idfacultet = $bsufacultet->idFakultet;
            $mas = explode('.', $bsufacultet->FakultetName);
            $FakultetName = trim($mas[1]);
            
            if (empty($faculty->idfakultet) || $faculty->idfakultet != $idfacultet) {
                set_field_select('dean_faculty', 'idfakultet', $idfacultet, "id=$fid"); 
                notify ('idFakultet изменен.', 'green');
            }  
            
            if ($faculty->name != $bsufacultet->FakultetName)   {
                set_field_select('dean_faculty', 'name', $FakultetName, "id=$fid");
                notify ('FakultetName изменен.', 'green');
            }
            
            sync_speciality($fid, $idfacultet);
            
            sync_academygroups($fid, $idfacultet);
            
            sync_studycardstudents($fid, $idfacultet);
                
           
        } else {
            notice('Факультет не найден в mdl_bsu_students.', 'ldapsyncbsu.php'); 
        }
        

	   	if ($fid <= 22) {
	   		$fid++;
	   		redirect("ldapsyncbsu.php?fid=$fid", '', $delay);
	   	}

        
	}   	
   	
    print_footer();


function sync_spravochniki()
{
    $strsql = "SELECT distinct idKodPrith, TextPrith FROM mdl_bsu_students ORDER BY idKodPrith";
    if ($bsuspravs = get_records_sql($strsql))    {
        foreach ($bsuspravs as $bsusprav)   {
            if (record_exists_select('dean_prith', "idprith = $bsusprav->idKodPrith"))   {            
                set_field_select('dean_prith', ' textprith', $bsusprav->TextPrith, "idprith = $bsusprav->idKodPrith");
            } else {
                $rec->idprith = $bsusprav->idKodPrith;
                $rec->textprith = $bsusprav->TextPrith;
                insert_record('dean_prith', $rec);                
            }    
        }    
    }
    notify ('Справочник dean_prith обновлен.', 'green');
    
    $strsql = "SELECT distinct idPeremetch, TextPerem  FROM mdl_bsu_students  ORDER BY idPeremetch";
    if ($bsuspravs = get_records_sql($strsql))    {
        foreach ($bsuspravs as $bsusprav)   {
            if (record_exists_select('dean_peremetchenie', "idperem = $bsusprav->idPeremetch"))   {
                set_field_select('dean_peremetchenie', 'textperem', $bsusprav->TextPerem, "idperem = $bsusprav->idPeremetch");                
            } else {
                $rec->idperem = $bsusprav->idPeremetch;
                $rec->textperem = $bsusprav->TextPerem;
                insert_record('dean_peremetchenie', $rec);
            }    
        }    
    }
    notify ('Справочник dean_peremetchenie обновлен.', 'green');
    
    $strsql = "SELECT distinct idOtdelenie, Otdelenie FROM mdl_bsu_students ORDER BY idOtdelenie";
    if ($bsuspravs = get_records_sql($strsql))    {
        foreach ($bsuspravs as $bsusprav)   {  // id, idotdelenie, otdelenie
            if (record_exists_select('dean_otdelenie', "idotdelenie = $bsusprav->idOtdelenie"))   {
                set_field_select('dean_otdelenie', 'otdelenie', $bsusprav->Otdelenie, "idotdelenie = $bsusprav->idOtdelenie");                
            } else {
                $rec->idotdelenie = $bsusprav->idOtdelenie;
                $rec->otdelenie = $bsusprav->Otdelenie;
                insert_record('dean_otdelenie', $rec);
            }    
        }    
    }
    notify ('Справочник dean_otdelenie обновлен.', 'green');

    $strsql = "SELECT distinct Paralel, ParalelTxt FROM mdl_bsu_students ORDER BY Paralel";
    if ($bsuspravs = get_records_sql($strsql))    {
        foreach ($bsuspravs as $bsusprav)   {  // id, idparalel, paraleltxt
            if (record_exists_select('dean_parallel', "idparalel = $bsusprav->Paralel"))   {
                set_field_select('dean_parallel', 'paraleltxt', $bsusprav->ParalelTxt, "idparalel = $bsusprav->Paralel");                
            } else {
                $rec->idparalel = $bsusprav->Paralel;
                $rec->paraleltxt = $bsusprav->ParalelTxt;
                insert_record('dean_parallel', $rec);
            }    
        }    
    }
    notify ('Справочник dean_parallel обновлен.', 'green');
    
} 

function sync_speciality($fid, $idfacultet)
{
    $flag = true; 
    // синхронизируем специальности с id
    $strsql = "SELECT distinct concat(idSpecyal, idKvalif) as a, idSpecyal, Specyal, idKvalif, Kvalif FROM mdl_bsu_students where idFakultet=$idfacultet";
    if ($bsuspecyals = get_records_sql($strsql))    {
        // print_r($bsuspecyals); echo '<hr>';
        $arr_bsuspec = array();
        $arr_bsukval = array();
        $arr_bsuname = array();
        $arr_bsunamespec = array();
        foreach ($bsuspecyals as $bsuspecyal)   {
            $iSpec = substr ($bsuspecyal->Specyal, 0, 6);
            $iKval = substr ($bsuspecyal->Kvalif, 0, 2);
            $index = $iSpec . '.' . $iKval;   
            $arr_bsuspec[$index] =  $bsuspecyal->idSpecyal;
            $arr_bsukval[$index] =  $bsuspecyal->idKvalif;
            $arr_bsuname[$index] =  mb_substr($bsuspecyal->Kvalif, 3);
            $arr_bsunamespec[$index] =  mb_substr($bsuspecyal->Specyal, 7);
        }    
        print_r($arr_bsuspec); echo '<hr>';
        // id, facultyid, number, name, qualification, timemodified, idspecyal, idkvalif, codespeciality, codekvalif, codespecii
        $deanspecs =  get_records_select('dean_speciality', "facultyid = $fid", '', 'id, number, name, qualification, idspecyal, idkvalif, codespeciality, codekvalif, codespecii');
        foreach ($deanspecs as $deanspec)   {
            $index = substr ($deanspec->number, 0, 9);
            if (isset($arr_bsuspec[$index])) {
                if (empty($deanspec->idspecyal) || $deanspec->idspecyal != $arr_bsuspec[$index] || $deanspec->idkvalif != $arr_bsukval[$index]) {
                    $rec->id = $deanspec->id;
                    $rec->idspecyal =  $arr_bsuspec[$index];
                    $rec->idkvalif =  $arr_bsukval[$index];                    
                    $rec->codespeciality = substr ($index, 0, 6);
                    $rec->codekvalif = substr ($index, 7, 2);
                    $rec->qualification = $arr_bsuname[$index];
                    // print_r($rec);
                    if (!update_record('dean_speciality',$rec)) {
                        print_r($rec);
                        error ('Ошибка при изменении специальности с кодом '. $index, 'ldapsyncbsu.php');
                    } else {
                        notify ('Изменена специальность с кодом '. $index, 'green');
                    } 
  
                }
            } else {
                notify ("Индекс $index отсутствует.");
            }    
        }   
         
         // проверяем все ли специальности есть в деканате
        $arr_deanspec = array();
        foreach ($deanspecs as $deanspec)   {
            $index = substr ($deanspec->number, 0, 9);
            $arr_deanspec[] =  $index;// $deanspec->id;
        }    
        
        // print_r($arr_bsuspec); echo '<hr>';
        // print_r($arr_deanspec); echo '<hr>';
        foreach ($arr_bsuspec as $index => $idbsuspec)  {
            if (!in_array($index, $arr_deanspec))   {
                notify ('Cпециальность с кодом '. $index . ' не обнаружена.');
                // Создаем специальность
                unset($rec);
                $rec->facultyid = $fid;
        		$rec->number = $index;
        		$rec->name = $arr_bsunamespec[$index];
        		$rec->qualification = $arr_bsuname[$index];
                $rec->idspecyal =  $arr_bsuspec[$index];
                $rec->idkvalif =  $arr_bsukval[$index];                    
                $rec->codespeciality = substr ($index, 0, 6);
                $rec->codekvalif = substr ($index, 7, 2);
       			$rec->timemodified = time();
    			if (insert_record('dean_speciality', $rec))	{
    				 notify(get_string('specialityadded','block_dean'), 'green');
    			} else  {
    				error(get_string('errorinaddingspec','block_dean'), "ldapsyncbsu.php");
    			}
            }    
        } 
        
        
        //do {
            $check=false;    
            // проверка
            $concatids = array();
            $deanspecs =  get_records_select('dean_speciality', "facultyid = $fid", '', 'id, number, name, qualification, idspecyal, idkvalif, codespeciality, codekvalif, codespecii');
            foreach ($deanspecs as $deanspec)   {
                $concatids[] = $deanspec->idspecyal .  $deanspec->idkvalif;
            }    
            // print_r($concatids); echo '<br>';    exit ();
            
            foreach ($bsuspecyals as $bsuspecyal)   {
                if (!in_array($bsuspecyal->a, $concatids))  {
                    notify ("Специальность с кодами $bsuspecyal->idSpecyal и $bsuspecyal->idKvalif не найдена");
                    $flag = false; 
                    
                    $strSpec = mb_substr ($bsuspecyal->Specyal, 7);
                    echo $strSpec. '<br>';
                    $strsql = "SELECT * from mdl_dean_speciality where name = '$strSpec' AND facultyid = $fid";
                    echo $strsql.'<br>';
                    if ($spec = get_record_sql($strsql))    {
                        echo 'FOUND!!!';
                        $rec->id = $spec->id;
                        $rec->idspecyal = $bsuspecyal->idSpecyal;
                        $rec->idkvalif =  $bsuspecyal->idKvalif;
                        $rec->codespeciality = substr ($bsuspecyal->Specyal, 0, 6);
                        $rec->codekvalif = substr ($bsuspecyal->Kvalif, 0, 2);
                        $rec->qualification = mb_substr($bsuspecyal->Kvalif, 3);
                        // print_r($rec);
                        if (!update_record('dean_speciality',$rec)) {
                            print_r($rec);
                            error ('Ошибка при изменении специальности с кодом '. $index, 'ldapsyncbsu.php');
                        } else {
                            notify ('Изменена специальность с кодом '. $index, 'green');
                        } 
                        $check=true;
                    } else {
                        unset($rec);
    
                        $iSpec = substr ($bsuspecyal->Specyal, 0, 6);
                        $iKval = substr ($bsuspecyal->Kvalif, 0, 2);
                        $index = $iSpec . '.' . $iKval;   
                       
                        $rec->facultyid = $fid;
                		$rec->number = $index;
                		$rec->name = mb_substr($bsuspecyal->Specyal, 7);
                		$rec->qualification = mb_substr($bsuspecyal->Kvalif, 3);
                        $rec->idspecyal = $bsuspecyal->idSpecyal;
                        $rec->idkvalif =  $bsuspecyal->idKvalif;
                        $rec->codespeciality = substr ($bsuspecyal->Specyal, 0, 6);
                        $rec->codekvalif = substr ($bsuspecyal->Kvalif, 0, 2);
               			$rec->timemodified = time();
            			if (insert_record('dean_speciality', $rec))	{
            				 notify(get_string('specialityadded','block_dean'), 'green');
            			} else  {
            				error(get_string('errorinaddingspec','block_dean'), "ldapsyncbsu.php");
            			}
                        $check=true;
                    }
                }  
            }
        // } while ($check);
        
        $concatids = array();
        $deanspecs =  get_records_select('dean_speciality', "facultyid = $fid", '', 'id, number, name, qualification, idspecyal, idkvalif, codespeciality, codekvalif, codespecii');
        foreach ($deanspecs as $deanspec)   {
            $concatids[] = $deanspec->idspecyal .  $deanspec->idkvalif;
        }    
        // print_r($concatids); echo '<br>';    exit ();
        $flag = true;
        foreach ($bsuspecyals as $bsuspecyal)   {
            if (!in_array($bsuspecyal->a, $concatids))  {
                notify ("Специальность с кодами $bsuspecyal->idSpecyal и $bsuspecyal->idKvalif не найдена!!!!!!!");
                $flag = false;
                        unset($rec);
    
                        $iSpec = substr ($bsuspecyal->Specyal, 0, 6);
                        $iKval = substr ($bsuspecyal->Kvalif, 0, 2);
                        $index = $iSpec . '.' . $iKval;   
                       
                        $rec->facultyid = $fid;
                		$rec->number = $index;
                		$rec->name = mb_substr($bsuspecyal->Specyal, 7);
                		$rec->qualification = mb_substr($bsuspecyal->Kvalif, 3);
                        $rec->idspecyal = $bsuspecyal->idSpecyal;
                        $rec->idkvalif =  $bsuspecyal->idKvalif;
                        $rec->codespeciality = substr ($bsuspecyal->Specyal, 0, 6);
                        $rec->codekvalif = substr ($bsuspecyal->Kvalif, 0, 2);
               			$rec->timemodified = time();
            			if (insert_record('dean_speciality', $rec))	{
            				 notify(get_string('specialityadded','block_dean'), 'green');
            			} else  {
            				error(get_string('errorinaddingspec','block_dean'), "ldapsyncbsu.php");
            			}
                
            }
        }         
            
           
    }
  return $flag;  
} 


function sync_academygroups($fid, $idfacultet)
{
    global $CFG;
    
    
    $otdelenies = get_records_select('dean_otdelenie', '', 'id, idotdelenie, otdelenie');
    $arr_otdelenies = array();
    $formlearnings = array ('', '', 'daytimeformtraining', 'correspondenceformtraining', 'eveningformtraining'); 
    foreach ($otdelenies as $otdelenie) {
        $arr_otdelenies[$otdelenie->idotdelenie] = $formlearnings[$otdelenie->idotdelenie];
    }    
        
    $deanspecs =  get_records_select('dean_speciality', "facultyid = $fid", '', 'id, number, name, idspecyal, idkvalif');
    $deanspecids = array();
    foreach ($deanspecs as $deanspec)   {
        $iSpec = $deanspec->idspecyal;
        $iKval = $deanspec->idkvalif;
        $index = $iSpec . '_' . $iKval;   
        $deanspecids[$index] = $deanspec->id;
    }

    $strsql = "SELECT distinct grup, idSpecyal, idKvalif, idOtdelenie, Kurs_New FROM mdl_bsu_students where idFakultet=$idfacultet and Dateotsh='' order by grup";    
    if ($bsugroups = get_records_sql($strsql))    {
        // print_r($bsugroups); echo '<hr>';
        $arr_bsuspec = array();
        $arr_bsukval = array();
        $arr_bsuotdl = array();
        $arr_bsukurs = array();
        foreach ($bsugroups as $bsugroup)   {
            $arr_bsuspec["$bsugroup->grup"] = $bsugroup->idSpecyal;
            $arr_bsukval["$bsugroup->grup"] = $bsugroup->idKvalif;
            $arr_bsuotdl["$bsugroup->grup"] = $bsugroup->idOtdelenie;
            $arr_bsukurs["$bsugroup->grup"] = $bsugroup->Kurs_New; 
        }    
    }    
    
    // id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified, idotdelenie
	$agroups = get_records_sql ("SELECT id, specialityid, curriculumid, name FROM {$CFG->prefix}dean_academygroups
								 WHERE facultyid=$fid
								 ORDER BY name");
	if ($agroups) {
	    // синхронизируем все имеющиеся в деканате группы
        $arr_deans_agroups = array();
		foreach ($agroups as $agroup) {
		    $arr_deans_agroups[] = $agroup->name;
            if (isset($arr_bsuspec["$agroup->name"]))   {
    		   $idspec = $arr_bsuspec["$agroup->name"]; 
               $idkval = $arr_bsukval["$agroup->name"];
               $index = $idspec . '_' . $idkval;
               if ($agroup->specialityid == $deanspecids[$index])   {
                    // notify ("Группа " .  $agroup->name . " зарегистрирована на аналогичной специальности.", 'green');
                    $agroup->startyear = $arr_bsukurs["$agroup->name"];
                    $agroup->idotdelenie = $arr_bsuotdl["$agroup->name"];
                    if (update_record('dean_academygroups', $agroup))   {
                          notify("Группа $agroup->name обновлена.", 'green');
                    }    
                    
               } else {
                    notify ("Группа " .  $agroup->name . " зарегистрирована на другой специальности. Now ".  $agroup->specialityid . '. Must be ' . $deanspecids[$index]);
                    // поиск рабочего учебного плана в другой специальности
                    $sid  = $deanspecids[$index]; 
                    $index = $arr_bsuotdl["$agroup->name"]; 
                    $formlearning = $arr_otdelenies[$index];
                    notify('Форма обучения: '.  get_string($formlearning, 'block_dean'), 'green');
                    
      			    $strsql = "SELECT id, name, enrolyear, formlearning FROM {$CFG->prefix}dean_curriculum
							   WHERE facultyid=$fid AND specialityid=$sid AND formlearning='$formlearning'
                               ORDER BY timemodified DESC";
                    if ($curriculums = get_records_sql ($strsql))   {
                        $cnt = count($curriculums);
                        notify('Количество учебных планов: '.  $cnt, 'green');
                        $currfirst = current($curriculums);
                        $agroup->specialityid = $sid;
                        $agroup->curriculumid = $currfirst->id;
                        $agroup->startyear = $arr_bsukurs["$agroup->name"];
                        $agroup->idotdelenie = $arr_bsuotdl["$agroup->name"];
                        if (update_record('dean_academygroups', $agroup))   {
                              notify('Группа переведена в другую специальность и РУП.', 'green');
                        }    
                    } else {
                        notify('Количество учебных планов: 0');
                        // создаем новый учебный план
                        $deanspec =  get_record_select('dean_speciality', "id = $sid", 'id, number, name');
                    	$rec->facultyid = $fid;
                    	$rec->specialityid = $sid;
                    	$rec->code = 0;
                    	$rec->name = $deanspec->number . ' ' . $deanspec->name;
                    	$rec->enrolyear = $arr_bsukurs["$agroup->name"];
                    	$rec->formlearning = $formlearning;
                    	$rec->description = "";
               			$rec->timemodified = time();
               			if ($cloneid = insert_record('dean_curriculum', $rec))	  {
               			    notify(get_string('curriculumadded','block_dean'), 'green'); 
                            // копируем дисциплины в новый учебный план
               			    $disciplines = get_records('dean_curriculum', 'id', $agroup->curriculumid);
                    		foreach ($disciplines as $discip) {
                    		     unset($discip->id);
                    			 $discip->curriculumid = $cloneid;
                   			 	 if (insert_record('dean_discipline',$discip->curriculumid))    {
                    				 notify('Дисциплина скопирована в новый РУП.', 'green');
                    		     }
                    	    }
                            // переводим группу в другой учебный план
                            $agroup->specialityid = $sid;
                            $agroup->curriculumid = $cloneid;
                            $agroup->startyear = $arr_bsukurs["$agroup->name"];
                            $agroup->idotdelenie = $arr_bsuotdl["$agroup->name"];
                            if (update_record('dean_academygroups', $agroup))   {
                                notify('Группа переведена в новый РУП другой специальности.', 'green');
                            }    
                        }    
                      }
                 }
            } else {
                // notify ("Группа " .  $agroup->name . " не найдена в БД БелГУ.");
            }    
        }
        
        // поиск групп, отсутсвующих в деканате
        foreach ($bsugroups as $bsugroup)   {
            if (!in_array($bsugroup->grup, $arr_deans_agroups)) {
                notify ("Группа " .  $bsugroup->grup . " не найдена в Деканате Пегаса.");
                // создание группы
    		    $idspec = $arr_bsuspec["$bsugroup->grup"]; 
                $idkval = $arr_bsukval["$bsugroup->grup"];
                // id, facultyid, number, name, qualification, timemodified, idspecyal, idkvalif, codespeciality, codekvalif, codespecii
                if ($speciality =  get_record_select('dean_speciality', "idspecyal =$idspec AND idkvalif=$idkval", 'id, number, name'))   {
                    $index = $arr_bsuotdl["$bsugroup->grup"]; 
                    $formlearning = $arr_otdelenies[$index];
                    
    				$curriculum = get_record_sql("SELECT id, specialityid  FROM mdl_dean_curriculum m
    						                      where specialityid=$speciality->id and formlearning='$formlearning'");
    			    			  
    			    if ($curriculum) {
    					$rec->facultyid = $fid;
    					$rec->specialityid = $speciality->id;
    					$rec->curriculumid = $curriculum->id;
    					$rec->name = $bsugroup->grup;
                        $rec->startyear = $bsugroup->Kurs_New;                        
    					// $rec->startyear = '20' . substr($bsugroup->grup, 2, 2);  
    					$rec->description = "";
                        $rec->idotdelenie = $bsugroup->idOtdelenie;
                       
    					// print_r($rec);
    					if (insert_record('dean_academygroups', $rec))	{
    						notify(get_string('groupadded','block_dean'), 'green');
    					}
    			    } else {
                        notify("Учебный план с параметрами specialityid=$speciality->id and formlearning='$formlearning' не найден. Группа $bsugroup->grup не создана.");
                    } 
                } else {
                    notify("Специальность с параметрами idspecyal =$idspec AND idkvalif=$idkval не найдена. Группа $bsugroup->grup не создана.");
                }    
                
            }
        }        
   }             
} 

function sync_studycardstudents($fid, $idfacultet)
{
    global $CFG;

    $deanspecs =  get_records_select('dean_speciality', "facultyid = $fid", '', 'id, number, name, idspecyal, idkvalif');
    $deanspecids = array();
    foreach ($deanspecs as $deanspec)   {
        $iSpec = $deanspec->idspecyal;
        $iKval = $deanspec->idkvalif;
        $index = $iSpec . '_' . $iKval;   
        $deanspecids[$index] = $deanspec->id;
    }

    $strsql = "SELECT CodePhysPerson, Name, DateRojd, idSpecyal, idKvalif, grup,  FormObutsTxt, idPeremetch, NumPrikazPost, Datepost, Dateotsh, idKodPrith, Paralel, Zathetka, Sex
               FROM mdl_bsu_students
               WHERE idFakultet=$idfacultet AND Dateotsh=''
               ORDER BY CodePhysPerson";

    if ($absustudents = get_records_sql($strsql))    {
        $bsustudents = (array)$absustudents;
        
        $listlogins = array();
        foreach ($bsustudents as $login => $bsustudent)   {
            $listlogins[] = "'$login'";
        }
        $strlistlogins = implode(',', $listlogins);
                
        
        // foreach($bsustudents as $login => $bsustudent)  {
            // print_r($bsustudent); echo '<hr>' . $login . ' ';
        // }
        
        $strsql = "SELECT id, username, lastname, firstname, auth FROM mdl_user WHERE username in ($strlistlogins) ORDER BY username";
        // echo $strsql . '<hr>'; 
        if ($astudents = get_records_sql($strsql))    {
            $students = (array)$astudents;
            echo count($students) . '<br>';
            
            foreach ($students as $student) {
                
                $login = $student->username;
                $fullname = fullname($student);
                if  ($student->auth != 'cas')  {
                    notify ("ВНИМАНИЕ! У студента $student->username $fullname установлена авторизация, отличная от CAS.");
                    continue;
                }
                
                $studentcard->userid = $student->id;
                $studentcard->facultyid = $fid;
                
                $iSpec = $bsustudents["$login"]->idSpecyal;
                $iKval = $bsustudents["$login"]->idKvalif;
                $index = $iSpec . '_' . $iKval;   
                $studentcard->specialityid = $deanspecids[$index];
                
                $groupname = $bsustudents["$login"]->grup;
                if ($agroup = get_record_select('dean_academygroups', "name = '$groupname'", 'id'))    {
                   $studentcard->academygroupid = $agroup->id; 
                } else {
                    $studentcard->academygroupid = 0;
                }
                 
                $studentcard->recordbook = $bsustudents["$login"]->Zathetka;
                $DateRojdenia = substr($bsustudents["$login"]->DateRojd, 0, 10);
                // echo $DateRojdenia . '<br>';
                $DateRojdenia = trim($DateRojdenia);
                $DateRojdenia = convert_date($DateRojdenia, 'en', 'ru');
                //echo $DateRojdenia . '<br>';
                $studentcard->birthday = get_timestamp_from_rus_date($DateRojdenia);
                $studentcard->ordernumber = $bsustudents["$login"]->NumPrikazPost;
                // $studentcard->enrolmenttype
                // $studentcard->gender = 0;
                 
                $studentcard->datepost = substr($bsustudents["$login"]->Datepost, 0, 10);
                // $studentcard->dateotsh = substr($bsustudents["$login"]->Dateotsh, 0, 10);
               
                $studentcard->idkodprith = $bsustudents["$login"]->idKodPrith;
                $studentcard->idparalel = $bsustudents["$login"]->Paralel;
                $studentcard->idperem = $bsustudents["$login"]->idPeremetch;
                $studentcard->financialbasis = $bsustudents["$login"]->FormObutsTxt;
                $studentcard->gender = $bsustudents["$login"]->Sex;

                if ($oldstudentcard = get_record_select('dean_student_studycard', "userid = $student->id", 'id, facultyid, specialityid, recordbook, birthday, ordernumber, enrolmenttype, gender, datepost, academygroupid, idkodprith, idparalel, idperem')) {
                    $studentcard->id = $oldstudentcard->id;
                    if (update_record('dean_student_studycard', $studentcard))   {
                        notify("Карточка студента $fullname обновлена.", 'green');
                    } else {
                        print_r($studentcard);
                        notify("Ошибка при обновлении карточки студента $fullname.");
                    }   
                } else {
					if (insert_record('dean_student_studycard', $studentcard))   {
						notify("Создана карточка студента $fullname.", 'green');
					} else {
					   print_r($studentcard);
                       notify("Ошибка при создании карточки студента $fullname.");
					}
                }
                // print_r($student); echo '<hr>'; echo '<hr>' . $username . ' ';
            }    
        }    
    }    
} 


function get_timestamp_from_rus_date($strdate)
{
   $t = 0;
   if (is_date($strdate, 'ru')) {
	   list($day, $month, $year) = explode(".", $strdate);
       $t = make_timestamp($year, $month, $day, 12);           
   }
   return $t;
}                
?>