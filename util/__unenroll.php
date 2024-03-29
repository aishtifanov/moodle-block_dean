<?php // $Id: __unenroll.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    require_once('lib.php');

    $i = optional_param('i', 0, PARAM_INT); // course id
    
    $strtitle = 'Unenroll all students from CT';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

    // $courses = get_records_select('course', 'category = 64', '', 'id');
    $courseids = array(1742, 1742, 1989, 2851, 2852, 2853, 2854, 2855, 2856, 2857, 2860, 2861, 2862, 2863, 2864, 2868, 2869, 2881, 2883, 2887, 2889, 2907, 2908, 2934, 2935, 2936, 2959, 2960, 2961, 2962, 3052, 3083, 3117, 3137, 3185, 3186, 3187, 3188, 3189, 3198, 3199, 3201, 3202, 3210, 3212, 3222, 3223, 3224, 3226, 3227, 3228, 3229, 3230, 3231, 3232, 3233, 3234, 3235, 3236, 3237, 3240, 3241, 3242, 3243, 3244, 3245, 3246, 3247, 3248, 3249, 3250, 3251, 3252, 3253, 3254, 3255, 3256, 3260, 3264, 3265, 3266, 3267, 3268, 3269, 3270, 3271, 3272, 3273, 3274, 3275, 3276, 3277, 3278, 3279, 3280, 3281, 3282, 3283, 3284, 3285, 3286, 3287, 3288, 3289, 3290, 3291, 3292, 3293, 3294, 3295, 3296, 3297, 3298, 3299, 3300, 3301, 3305, 3306, 3307, 3308, 3309, 3311, 3312, 3313, 3314, 3315, 3316, 3319, 3322, 3324, 3325, 3326, 3341, 3343, 3344, 3346, 3347, 3348, 3349, 3350, 3351, 3352, 3353, 3354, 3355, 3356, 3357, 3358, 3359, 3360, 3361, 3362, 3363, 3364, 3365, 3366, 3367, 3368, 3369, 3370, 3371, 3372, 3373, 3375, 3376, 3378, 3379, 3380, 3382, 3383, 3384, 3385, 3386, 3387, 3388, 3389, 3390, 3391, 3392, 3393, 3394, 3395, 3396, 3397, 3399, 3400, 3401, 3402, 3403, 3404, 3405, 3406, 3407, 3408, 3411, 3413, 3414, 3415, 3420, 3422, 3423, 3424, 3425, 3426, 3427, 3428, 3429, 3430, 3431, 3433, 3434, 3435, 3437, 3438, 3439, 3440, 3441, 3442, 3443, 3444, 3445, 3446, 3448, 3449, 3450, 3451, 3452, 3453, 3454, 3456, 3459, 3460, 3461, 3462, 3463, 3465, 3466, 3467, 3468, 3469, 3470, 3471, 3472, 3473, 3474, 3475, 3476, 3477, 3478, 3480, 3481, 3482, 3483, 3485, 3486, 3487, 3488, 3491, 3492, 3494, 3495, 3497, 3499, 3502, 3505, 3506, 3507, 3508, 3509, 3510, 3513, 3514, 3515, 3516, 3517, 3519, 3520, 3521, 3522, 3525, 3527, 3528, 3529, 3533, 3534, 3556, 3562, 3565, 3567, 3568, 3573, 3575, 3577, 3602, 3603, 3604, 3607, 3608, 3609, 3611, 3612, 3615, 3618, 3622, 3623, 3624, 3626, 3627, 3628, 3629, 3630, 3631, 3632, 3633, 3635, 3636, 3637, 3638, 3639, 3640, 3641, 3642, 3643, 3645, 3646, 3647, 3648, 3658, 3659, 3660, 3661, 3665, 3666, 3667, 3668, 3670, 3671, 3672, 3674, 3675, 3676, 3677, 3678, 3679, 3680, 3682, 3684, 3685, 3687, 3689, 3692, 3693, 3694, 3695, 3696, 3697, 3698, 3699, 3700, 3701, 3703, 3704, 3705, 3707, 3709, 3710, 3712, 3715, 3718, 3719, 3720, 3721, 3722, 3723, 3725, 3728, 3729, 3736, 3737, 3738, 3739, 3740, 3741, 3743, 3744, 3745, 3746, 3747, 3748, 3749, 3750, 3753, 3754, 3755, 3756, 3757, 3758, 3760, 3761, 3762, 3763, 3765, 3766, 3767, 3768, 3769, 3770, 3771, 3772, 3773, 3774, 3775, 3776, 3777, 3778, 3779, 3780, 3781, 3782, 3783, 3784, 3785, 3786, 3788, 3789, 3790, 3792, 3793, 3794, 3795, 3796, 3797, 3798, 3799, 3800, 3801, 3802, 3803, 3804, 3805, 3806, 3807, 3808, 3809, 3811, 3812, 3813, 3814, 3815, 3816, 3817, 3818, 3819, 3820, 3821, 3822, 3823, 3826, 3827, 3828, 3829, 3830, 3831, 3832, 3834, 3835, 3836, 3837, 3838, 3839, 3840, 3841, 3842, 3843, 3844, 3845, 3846, 3847, 3848, 3849, 3850, 3851, 3852, 3853, 3854, 3855, 3856, 3857, 3858, 3859, 3861, 3862, 3863, 3864, 3865, 3866, 3867, 3868, 3870, 3871, 3872, 3873, 3874, 3875, 3877, 3878, 3879, 3880, 3881, 3882, 3883, 3885, 3886, 3887, 3888, 3889, 3891, 3892, 3893, 3894, 3895, 3896, 3897, 3898, 3899, 3900, 3901, 3902, 3903, 3904, 3905, 3906, 3908, 3909, 3910, 3912, 3914, 3915, 3917, 3918, 3920, 3921, 3922, 3923, 3925, 3926, 3927, 3928, 3929, 3930, 3931, 3932, 3933, 3934, 3935, 3939, 3940, 3941, 3942, 3945, 3946, 3947, 3948, 3949, 3952, 3953, 3954, 3955, 3958, 3959, 3960, 3963, 3964, 3965, 3966, 3967, 3968, 3969, 3970, 3971, 3973, 3974, 3976, 3977, 3979, 3981, 3982, 3983, 3985, 3986, 3987, 3988, 3989, 3990, 3991, 3992, 3994, 3997, 3998, 3999, 4000, 4001, 4002, 4003, 4004, 4005, 4006, 4007, 4008, 4009, 4010, 4012, 4014, 4015, 4017, 4018, 4020, 4021, 4022, 4024, 4025, 4026, 4028, 4030, 4031, 4033, 4034, 4035, 4037, 4039, 4040, 4041, 4043, 4044, 4045, 4048, 4049, 4052, 4054, 4055, 4060, 4063, 4069, 4070, 4071, 4072, 4073, 4075, 4079, 4096);    
    
    notify('Courseid = ' . $courseids[$i]);
    unenroll_all_students_from_course($courseids[$i]);
  
    notify ('Complete ' . $i);
    
    $i++;
    
    $cnt = count($courseids);
    if ($i <= $cnt)  { 
        redirect("__unenroll.php?i=$i");
    }        

	print_footer();
	
	
	
function unenroll_all_students_from_course($courseid=0) 
{
	global $CFG;
	$ii=0;
    if ($courseid) {
        if ($delgroups = get_records_select("groups", "courseid = $courseid and timecreated < 1314868562", '', 'id, name'))
        foreach ($delgroups as $delgroup)   {
            $academygroup = get_record_select("dean_academygroups", "name = '$delgroup->name'", 'id, name');
            // notify ('agroup' . $academygroup->name);
  	        $academystudents = get_records_select('dean_academygroups_members', "academygroupid = $academygroup->id", '', 'id, userid');
 		    if ($academystudents) 	{
	 			foreach ($academystudents as $astud)	  {
	 			   // notify ('student' . $astud->userid);
                    $ii++;
					unenrol_student_dean ($astud->userid, $courseid);
                }
            }              
            // notify ('mgroup' . $delgroup->name);          
            delete_records("groups", "id", $delgroup->id);
            delete_records("groups_members", "groupid", $delgroup->id);
        } 
    }
    notify('Unenroll students = '. $ii);
}	 

?>
