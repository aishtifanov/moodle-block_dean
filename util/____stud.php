<?PHP // $Id: 
	require_once('../../config.php');

	$strtitle = '__ Добавление студентов из bsu_students_2013 в user.';

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

    add_new_user_stud();
  
    print_footer();
    

function add_new_user_stud()
{
    // $sql = 'select id, name, codephysperson from mdl_bsu_students_2013 where numprikazotsh = ""';
    $sql = 'select id, name, codephysperson from mdl_bsu_students where idkodprith = 1 and kurs_new=2014';
    $bsu_stud = get_records_sql($sql);
    
    $sql = 'select id, username from mdl_user';
    $mdl_user = get_records_sql($sql);
    $arr_bs = array();
    $arr_mu = array();
    $copy_arr_bs = array();
    foreach($bsu_stud as $bs){
        $arr_bs[$bs->codephysperson]= array($bs->id, $bs->name, $bs->codephysperson);
        $copy_arr_bs[$bs->codephysperson]=$bs->codephysperson;
    }

    foreach($mdl_user as $mu){
        if(is_number($mu->username)) {
            $arr_mu[$mu->username]=$mu->username;
        }        
    }
    
    $dif = array_diff($copy_arr_bs, $arr_mu);//$copy_arr_bs
    
    $c = count($dif);
    echo "Необходимо синхронизировать <font color='red'>$c</font> пользователей(ля)<br>";//print_r($c);

    foreach($dif as $d=>$di){
        $diff_obj = new stdClass();
        $diff_obj->name = $arr_bs[$d][1]; //$di['name'];
        $diff_obj->codephysperson = $arr_bs[$d][2];
        create_new_user_from_stud($diff_obj);
        // print_object($diff_obj);
    }
}


function create_new_user_from_stud($stud)
{
    global $CFG;
    
    $user = new stdClass();
    $user->auth = 'cas';
    list ($user->lastname, $user->firstname, $user->secondname) = explode (' ', $stud->name);
    $user->firstname = $user->firstname . ' ' . $user->secondname;
	$user->username = $stud->codephysperson;
    $user->email = $user->username . '@bsu.edu.ru'; 
    $user->password = 'not cached';
	$user->city = 'Белгород';
	$user->mnethostid = $CFG->mnet_localhost_id;
	$user->timemodified = time();
	$user->country = 'RU';
	$user->lang = 'ru_utf8';
	$user->confirmed = 1;
    $user->address = '-'; 
	
    unset($user->secondname);
    //print_object($user);
    
    if ($userexist =  get_record_select ('user', "username = '$user->username'", null, 'id'))   {
        /*
        $uid      = $userexist->id;
        $user->id = $userexist->id;
        $DB->update_record('user', $user);
        */  
        // print_object($userexist);
    }  else if ($uid = insert_record('user', $user))    {
	      echo "Добавлен пользователь: $user->lastname $user->firstname, email: $user->email <br>";
          // print_object($user);
	      // print_error('Error insert user.');
	}
    
    return $uid; 
}

?>