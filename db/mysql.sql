# ������� ��������


# ������� - ���������������� ��������� ��������
CREATE TABLE `prefix_dean_config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Dean configuration variables';


INSERT INTO `prefix_dean_config` VALUES (1, 'lastsertificatenumber', '111');

# ������� - ��� ���������� ������ � ���������, ���������� ��������
CREATE TABLE `prefix_dean_user_graduates` (
  `id` int(10) unsigned NOT NULL auto_increment,
	`userid` int(10) NOT NULL default '0',  
  `auth` varchar(20) NOT NULL default 'manual',
  `confirmed` tinyint(1) NOT NULL default '0',
  `policyagreed` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `username` varchar(100) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `idnumber` varchar(64) default NULL,
  `firstname` varchar(30) NOT NULL default '',
  `lastname` varchar(20) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `emailstop` tinyint(1) unsigned NOT NULL default '0',
  `icq` varchar(15) default NULL,
  `skype` varchar(50) default NULL,
  `yahoo` varchar(50) default NULL,
  `aim` varchar(50) default NULL,
  `msn` varchar(50) default NULL,
  `phone1` varchar(20) default NULL,
  `phone2` varchar(20) default NULL,
  `institution` varchar(40) default NULL,
  `department` varchar(30) default NULL,
  `address` varchar(70) default NULL,
  `city` varchar(20) default NULL,
  `country` char(2) default NULL,
  `lang` varchar(10) default 'en',
  `theme` varchar(50) NOT NULL default '',
  `timezone` varchar(100) NOT NULL default '99',
  `firstaccess` int(10) unsigned NOT NULL default '0',
  `lastaccess` int(10) unsigned NOT NULL default '0',
  `lastlogin` int(10) unsigned NOT NULL default '0',
  `currentlogin` int(10) unsigned NOT NULL default '0',
  `lastIP` varchar(15) default NULL,
  `secret` varchar(15) default NULL,
  `picture` tinyint(1) default NULL,
  `url` varchar(255) default NULL,
  `description` text default NULL,
  `mailformat` tinyint(1) unsigned NOT NULL default '1',
  `maildigest` tinyint(1) unsigned NOT NULL default '0',
  `maildisplay` tinyint(2) unsigned NOT NULL default '2',
  `htmleditor` tinyint(1) unsigned NOT NULL default '1',
  `autosubscribe` tinyint(1) unsigned NOT NULL default '1',
  `trackforums` tinyint(1) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `user_deleted` (`deleted`),
  KEY `user_confirmed` (`confirmed`),
  KEY `user_firstname` (`firstname`),
  KEY `user_lastname` (`lastname`),
  KEY `user_city` (`city`),
  KEY `user_country` (`country`),
  KEY `user_lastaccess` (`lastaccess`),
  KEY `user_email` (`email`)
) TYPE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='One record for each person';

# ������� - ��� ���������� ������ � ���������, ���������� ��������
CREATE TABLE `prefix_dean_academygroups_g` (
	`id` int(10) NOT NULL auto_increment,
	`facultyid` int(10) NOT NULL default '0',
	`specialityid` int(10) NOT NULL default '0',
	`curriculumid` int(10) NOT NULL default '0',
	`name` varchar(100) NOT NULL default '',
	`startyear` int(10) default NULL,
	`description` varchar(254) NOT NULL default '',
	`timemodified` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX facultyid(`facultyid`),
	INDEX specialityid(`specialityid`),
	INDEX curriculumid(`curriculumid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;	

# ������� - ��� ���������� ������ � ���������, ���������� ��������
CREATE TABLE `prefix_dean_academygroups_members_g` (
	`id` int(10) NOT NULL auto_increment,
	`userid` int(10) NOT NULL default '0',
	`academygroupid` int(10) NOT NULL default '0',
	`timeadded` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX userid(`userid`),
	INDEX academygroupid(`academygroupid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;



# ������� - �������� �����
# id - ��� ��������
# academygroupid - ��� ������������� ������
# userid - ��� ������������
CREATE TABLE `prefix_dean_methodist` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `facultyid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `userid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(`id`),
  INDEX `facultyid`(`facultyid`),
  INDEX `userid`(`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


# ������� - ����������� ���������
# id - ��� ���������
# studentid - ��� ��������
# disciplineid - ��� ����������
# number - ����� �����������
# hours - ���������� ����� 
# datecreated - ���� ������� �����������
# timemodified - ����� �������� (���������) �����������
CREATE TABLE `prefix_dean_certificate` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`studentid` int(10) unsigned NOT NULL default 0,
  `disciplineid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `number` varchar(10) NOT NULL DEFAULT '�� 00-0000',
  `hours` smallint unsigned NOT NULL default 0,
  `datecreated` int(10) unsigned NOT NULL default 0,
  `timemodified` int(10) unsigned NOT NULL default 0,
  PRIMARY KEY(`id`),
  INDEX `disciplineid`(`disciplineid`),
	INDEX studentid(`studentid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


# ������� - ��������� �����
# id - ��� ���������
# disciplineid - ��� ����������
# academygroupid - ��� ������������� ������
# teacherid - ��� �������������
# number - ����� ���������
# datecreated - ���� ���������� ���������
# timemodified - ����� �������� (���������) ���������
CREATE TABLE `prefix_dean_rolls` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `disciplineid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `academygroupid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `teacherid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `number` INTEGER UNSIGNED NOT NULL DEFAULT 1,
  `datecreated` int(10) unsigned NOT NULL default '0',
  `timemodified` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY(`id`),
  INDEX `disciplineid`(`disciplineid`),
  INDEX `academygroupid`(`academygroupid`),
  INDEX `teacherid`(`teacherid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;



# ������� - ������� � ��������� ������
# id - ��� ������� � ���������
# rollid - ��� ���������
# studentid - ��� ��������
# mark - ������� � �������
CREATE TABLE `prefix_dean_roll_marks` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`rollid` int(10) unsigned NOT NULL default '0',
	`studentid` int(10) unsigned NOT NULL default '0',
	`mark` smallint unsigned NOT NULL default '0',
	PRIMARY KEY  (`id`),
	INDEX rollid(`rollid`),
	INDEX studentid(`studentid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;




# ������� ��� ������� ������
#
#
# ������� - �������� �����
# id - ��� ��������
# academygroupid - ��� ������������� ������
# userid - ��� ������������
CREATE TABLE `prefix_dean_curators` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `academygroupid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `userid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(`id`),
  INDEX `academygroupid`(`academygroupid`),
  INDEX `userid`(`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


#
# ������� - �������� ����� ���������
# id - ��� ����
# groupid - ��� ������������� ������
# term - ����� ��������
# disciplineid - ��� ����������
# shortdisname - �������� ��� ����������
CREATE TABLE `prefix_dean_journal_shortdisname` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `groupid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `term` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `disciplineid` INTEGER UNSIGNED NOT NULL DEFAULT 0,
  `shortdisname` VARCHAR(5) NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `groupid`(`groupid`),
  INDEX `disciplineid`(`disciplineid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

# ������� - ���� ������ ���������
# id - ��� ����
# groupid - ��� ������������� ������
# term - ����� ��������
# datestart - ���� ������ ��������
# denominator - ���������/�����������
# duration - ����������������� �������� 
CREATE TABLE `prefix_dean_journal_startdate` (
	`id` int(10) unsigned NOT NULL auto_increment, 
	`groupid` int(10) unsigned NOT NULL default '0', 
	`term` smallint unsigned NOT NULL default '1', 
	`datestart` int(10) unsigned NOT NULL default '0',  
        `denominator` tinyint(1) NOT NULL default '1', 
	`duration` smallint unsigned NOT NULL default '18', 
	PRIMARY KEY  (`id`),
	INDEX groupid(`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

# ������� - ����������
# id - ��� ����������
# groupid - ��� ������������� ������
# term - ����� ��������
# denominator - ���������/�����������
# numday - ����� ��� � ������
# numpair - ����� ����
# disciplineid - ��� ����������
CREATE TABLE `prefix_dean_journal_shedule` (
	`id` int(10) unsigned NOT NULL auto_increment, 
	`groupid` int(10) unsigned NOT NULL default '0', 
	`term` smallint unsigned NOT NULL default '0', 
        `denominator` tinyint(1) NOT NULL default '1', 
	`numday` smallint unsigned NOT NULL default '0', 
	`numpair` smallint unsigned NOT NULL default '0', 
	`disciplineid` int(10) unsigned NOT NULL default '0', 
	PRIMARY KEY  (`id`),
	INDEX groupid(`groupid`),
        INDEX `disciplineid`(`disciplineid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

#
# ������� - �������� �������
# id - ��� �������� �������
# groupid - ��� ������������� ������
# term - ����� ��������
# numweek - ����� ������ � ��������
# datestart - ���� ������ ��������
# denominator - ���������/�����������
# name - ��� ��������
CREATE TABLE `prefix_dean_journal_pages` (
	`id` int(10) unsigned NOT NULL auto_increment, 
	`groupid` int(10) unsigned NOT NULL default '0', 
	`term` smallint unsigned NOT NULL default '0', 
	`numweek` smallint unsigned NOT NULL default '0', 
	`datestart` int(10) unsigned default NULL,  
        `denominator` tinyint(1) NOT NULL default '1', 
	`name` varchar(50) NOT NULL default '', 
	PRIMARY KEY  (`id`),
	INDEX groupid(`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


# ������� - ������� � �������
# id - ��� ������� � �������
# journalpageid - ��� �������� �������
# numday - ����� ��� � ������
# numpair - ����� ����
# courseid - ��� �����
# userid - ��� ��������
# mark - ������� � �������
CREATE TABLE `prefix_dean_journal_marks` (
	`id` int(10) unsigned NOT NULL auto_increment, 
	`journalpageid` int(10) unsigned NOT NULL default '0', 
	`numday` smallint unsigned NOT NULL default '0', 
	`numpair` smallint unsigned NOT NULL default '0', 
	`disciplineid` int(10) unsigned NOT NULL default '0', 
	`userid` int(10) unsigned NOT NULL default '0', 
	`mark` smallint unsigned NOT NULL default '0',
  `dateday` int(10) unsigned NOT NULL, 
	PRIMARY KEY  (`id`),
	INDEX journalpageid(`journalpageid`),
	INDEX disciplineid(`disciplineid`),
	INDEX userid(`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_faculty` (
	`id` int(10) NOT NULL auto_increment,
	`number` int(10) NOT NULL default '0',
	`name` varchar(255) NOT NULL default '',
	`deanid` int(10) NOT NULL default '0',
	`deanphone1` varchar(20) default NULL,
	`deanphone2` varchar(20) default NULL,
	`deanaddress` varchar(100) default NULL,
	`timemodified` int(10) default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_speciality` (
	`id` int(10) NOT NULL auto_increment,
	`facultyid` int(10) NOT NULL default '0',
	`number` varchar(20)  NOT NULL default '',
	`name` varchar(255) NOT NULL default '',
	`qualification` varchar(255) NOT NULL default '',
	`timemodified` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX facultyid(`facultyid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_curriculum` (
	`id` int(10) NOT NULL auto_increment,
	`facultyid` int(10) NOT NULL default '0',
	`specialityid` int(10) NOT NULL default '0',
	`code` varchar(10) NOT NULL default '',
	`name` varchar(254) NOT NULL default '',
	`formlearning` varchar(20) NOT NULL default '',
	`enrolyear` varchar(10) NOT NULL default '',
	`thisyear` varchar(4) NOT NULL default '',
	`description` varchar(255) default NULL,
	`timemodified` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX facultyid(`facultyid`),
	INDEX specialityid(`specialityid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_discipline` (
	`id` int(10) NOT NULL auto_increment,
	`curriculumid` int(10) NOT NULL default '0',
	`term` int(10) NOT NULL default '0',
	`name` varchar(254) NOT NULL default '',
	`courseid` int(10) NOT NULL default '0',
	`cipher` varchar(20) NOT NULL default '-',
	`auditoriumhours` int(10) NOT NULL default '0',
	`selfinstructionhours` int(10) NOT NULL default '0',
	`termpaperhours` int(10) NOT NULL default '0',
	`controltype` varchar(20) NOT NULL default '',
	`timemodified` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX curriculumid(`curriculumid`),
	INDEX course(`courseid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
					
					
CREATE TABLE `prefix_dean_academygroups` (
	`id` int(10) NOT NULL auto_increment,
	`facultyid` int(10) NOT NULL default '0',
	`specialityid` int(10) NOT NULL default '0',
	`curriculumid` int(10) NOT NULL default '0',
	`name` varchar(100) NOT NULL default '',
	`startyear` int(10) default NULL,
	`term` int(10) NOT NULL default '0',
	`description` varchar(254) NOT NULL default '',
	`timemodified` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX facultyid(`facultyid`),
	INDEX specialityid(`specialityid`),
	INDEX curriculumid(`curriculumid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;	


CREATE TABLE `prefix_dean_academygroups_members` (
	`id` int(10) NOT NULL auto_increment,
	`userid` int(10) NOT NULL default '0',
	`academygroupid` int(10) NOT NULL default '0',
	`timeadded` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX userid(`userid`),
	INDEX academygroupid(`academygroupid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_student_studycard` (
	`id` int(10) NOT NULL auto_increment,
	`userid` int(10) NOT NULL default '0',
	`facultyid` int(10) NOT NULL default '0',
	`specialityid` int(10) NOT NULL default '0',
	`recordbook` int(10) NOT NULL default '0',
	`birthday` int(10) NOT NULL default '0',
	`whatgraduated` varchar(255) NOT NULL default '',
	`previousdiploma` varchar(255) NOT NULL default '',
	`issueday` int(10) NOT NULL default '0',
	`job` varchar(255) default '',
	`appointment` varchar(255) default '',
	`financialbasis` varchar(4) NOT NULL default '',
	`ordernumber` varchar(15) NOT NULL default '',
	`enrolmenttype` int(10) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	INDEX userid(`userid`),
	INDEX facultyid(`facultyid`),
	INDEX specialityid(`specialityid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_teacher_card` (
	`id` int(10) NOT NULL auto_increment,
	`userid` int(10) NOT NULL default '0',
	`uchstepen` varchar(100) default '',
	`uchzvanie` varchar(100) default '',
	PRIMARY KEY  (`id`),
	INDEX userid(`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


CREATE TABLE `prefix_dean_mark` (
	`id` int(10) NOT NULL auto_increment,
	`userid` int(10) NOT NULL default '0',
	`courseid` int(10) NOT NULL default '0',
	`controltype` varchar(20) NOT NULL default '',
	`mark` varchar(20) NOT NULL default '',
	`date` int(10) default NULL,
	PRIMARY KEY  (`id`),
	INDEX userid(`userid`),
	INDEX course(`courseid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;	

INSERT INTO `prefix_dean_student_studycard` (`userid`)  SELECT DISTINCT `userid` FROM  `prefix_user_students`;
INSERT INTO `prefix_dean_teacher_card` (`userid`)  SELECT DISTINCT  `userid` FROM  `prefix_user_teachers`;

INSERT INTO `prefix_faculty` VALUES (1, 1, '������-�������������� ���������', 2084, '(4722)34-05-87', '', '��. ������������ 14, 1 ������, 4 ����,  ���. 418', '');
INSERT INTO `prefix_faculty` VALUES (2, 2, '�������������� ���������', 2085, '(4722)34-15-70', '', '��. ������������ 14, 2 ������, 1 ����  ���. 147', '');
INSERT INTO `prefix_faculty` VALUES (3, 3, '��������� ������-���������� ���������', 2086, '(4722)30-12-41', '', '��. ������ 85, 12 ������, 5 ����, ���. 5-13', '');
INSERT INTO `prefix_faculty` VALUES (4, 4, '�������-���������� ���������', 2087, '(4722)30-11-62', '(4722)30-11-68', '��. ������ 85, 13 ������, 6 ����, ���. 6-18, 6-20', '');
INSERT INTO `prefix_faculty` VALUES (5, 5, '��������� ���������� ��������', 1912, '(4722)34-07-96', '', '��. ������������ 14, 3 ������, 1 ����, ���. 167', '');
INSERT INTO `prefix_faculty` VALUES (6, 6, '�������������� ���������', 2088, '(4722)34-14-15', '', '��. ������������ 14, 3 ������, 2 ����, ���. 285', '');
INSERT INTO `prefix_faculty` VALUES (7, 7, '������������ ���������', 2089, '(4722)34-21-20', '', '��. ������������ 14, 4 ������, 6 ����, ���. 601', '');
INSERT INTO `prefix_faculty` VALUES (8, 8, '����������� ���������', 1604, '(4722)30-12-26', '', '��. ������ 85, 13 ������,3 ����, ���. 3-16', '');
INSERT INTO `prefix_faculty` VALUES (9, 9, '����������� ���������', 2090, '(4722)36-89-28', '', '��. ������ 85, 10 ������, 3 ����, ���. 3-12, 3-14', '');
INSERT INTO `prefix_faculty` VALUES (10, 10, '������������� ���������',2091, '(4722)36-89-76', '', '��. ������ 85, 10 ������, 2 ����, ���. 2-22', '');
INSERT INTO `prefix_faculty` VALUES (11, 11, '��������� ���������� � �������������������', 2092, '(4722)30-12-81', '', '��. ������ 85, 13 ������, 2 ����, ���. 2-14', '');
INSERT INTO `prefix_faculty` VALUES (12, 12, '���������-������������� ���������', 2093, '(4722)30-13-41', '(4722)30-13-40', '��. �������������� 78, ���. 2', '');
INSERT INTO `prefix_faculty` VALUES (13, 13, '�������-�������������� ���������', 2094, '(4722)30-11-71', '', '��. ������ 85, 14 ������, 2 ����, ���. 2-11', '');
INSERT INTO `prefix_faculty` VALUES (14, 14, '��������� ������������ ���� � ����������������', 2095, '(4722)30-13-51', '', ' ��. ������ 85, 14 ������, 1 ����, ���. 1-11', '');
INSERT INTO `prefix_faculty` VALUES (15, 15, '��������� ����������', 2096, '(4722)34-31-24', '', '��. ������������ 14, 4 ������', '');
INSERT INTO `prefix_faculty` VALUES (16, 16, '��������� ������������', 2097, '(4722)30-13-60', '', '��. ������ 85, 17 ������, 2 ����, ���. 2-34', '');
INSERT INTO `prefix_faculty` VALUES (17, 17, '��������� ������� � ������� ', 2098, '(4722)30-????', '', '��. ������ 85, ?? ������, ? ����,  ���. ???', '');
INSERT INTO `prefix_faculty` VALUES (18, 18, '�������� ���������������� � �������������� ����������', 2098, '4722) 35-32-14', '', '��. ������ 85, 14 ������, 2 ����,  ���. 214', '');
INSERT INTO `prefix_faculty` VALUES (19, 19, '������������� ���������', 2098, '(4722)30-10-41', '', '��. ������ 85, 17 ������, 4 ����,  ���. 4-32', '');


INSERT INTO `prefix_speciality` VALUES (1, 1, '010101', '����������', '', '');
INSERT INTO `prefix_speciality` VALUES (2, 1, '010701', '������', '', '');
INSERT INTO `prefix_speciality` VALUES (3, 1, '050203', '������ � �������������� �������������� ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (4, 1, '050202', '����������� � �������������� �������������� ����������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (5, 1, '050202', '����������� � �������������� �������������� ������', '', '');
INSERT INTO `prefix_speciality` VALUES (6, 1, '210602', '�������������', '', '');
INSERT INTO `prefix_speciality` VALUES (7, 2, '031001', '��������� (������� ���� � ����������)', '', '');
INSERT INTO `prefix_speciality` VALUES (8, 2, '050301', '������� ���� � ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (9, 3, '031001', '��������� (����������) (���������� ����)', '', '');
INSERT INTO `prefix_speciality` VALUES (10, 3,'050303', '����������� ����: ���������� � �������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (11, 3,'050303', '����������� ����: �������� � ���������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (12, 3, '050303', '����������� ����: ����������� � ���������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (13, 3, '031202', '������� � ���������������', '', '');
INSERT INTO `prefix_speciality` VALUES (14, 4, '050102', '�������� � �������������� �������������� �����', '', '');
INSERT INTO `prefix_speciality` VALUES (15, 4, '020201', '��������', '', '');
INSERT INTO `prefix_speciality` VALUES (16, 4, '050101', '����� � �������������� �������������� ��������', '', '');
INSERT INTO `prefix_speciality` VALUES (17, 4, '020101', '�����', '', '');
INSERT INTO `prefix_speciality` VALUES (18, 4, '020201', '�������� �� �������������� ������������', '', '');
INSERT INTO `prefix_speciality` VALUES (19, 5, '050720', '���������� ��������', '', '');
INSERT INTO `prefix_speciality` VALUES (20, 6, '050708', '���������� � �������� ���������� �����������', '', '');
INSERT INTO `prefix_speciality` VALUES (21, 6, '050703', '���������� ���������� � ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (22, 6, '050715', '���������', '', '');
INSERT INTO `prefix_speciality` VALUES (23, 6, '050602', '��������������� ���������', '', '');
INSERT INTO `prefix_speciality` VALUES (24, 6, '100110', '�����������', '', '');
INSERT INTO `prefix_speciality` VALUES (25, 6, '050717', '����������� ���������� ���������� � ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (26, 7, '050401', '������� � �������������� �������������� ����������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (27, 7, '030401', '�������', '', '');
INSERT INTO `prefix_speciality` VALUES (28, 7, '032001', '���������������� � ���������������� ����������� ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (29, 8, '030501', '�������������', '', '');
INSERT INTO `prefix_speciality` VALUES (30, 9, '060101', '�������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (31, 9, '060108', '��������', '', '');
INSERT INTO `prefix_speciality` VALUES (32, 9, '060109', '����������� ����', '', '');
INSERT INTO `prefix_speciality` VALUES (33, 9, '060103', '���������', '', '');
INSERT INTO `prefix_speciality` VALUES (34, 10,'080105', '������� � ������', '', '');
INSERT INTO `prefix_speciality` VALUES (35, 10, '180109', '������������� ����, ������ � �����', '', '');
INSERT INTO `prefix_speciality` VALUES (36, 10, '180107', '������ � ���������������', '', '');
INSERT INTO `prefix_speciality` VALUES (37, 10, '180102', '������� ���������', '', '');
INSERT INTO `prefix_speciality` VALUES (38, 10, '180103', '������������ ���������', '', '');
INSERT INTO `prefix_speciality` VALUES (39, 11, '080502', '��������� � ���������� �� ����������� (� ��������� ���������)', '', '');
INSERT INTO `prefix_speciality` VALUES (40, 11, '080502', '��������� � ���������� �� ����������� (������ � ����������� ���������)', '', '');
INSERT INTO `prefix_speciality` VALUES (41, 11, '080504', '��������������� � ������������� ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (42, 11, '080507', '���������� �����������', '', '');
INSERT INTO `prefix_speciality` VALUES (43, 11, '080505', '���������� ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (44, 12, '040101', '���������� ������', '', '');
INSERT INTO `prefix_speciality` VALUES (45, 12, '031901', '��������', '', '');
INSERT INTO `prefix_speciality` VALUES (46, 12, '030101', '���������', '', '');
INSERT INTO `prefix_speciality` VALUES (47, 12, '050711', '���������� ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (48, 13, '050103', '��������� � �������������� �������������� �������� ��� ������������ �����������������', '', '');
INSERT INTO `prefix_speciality` VALUES (49, 13, '020401', '���������', '', '');
INSERT INTO `prefix_speciality` VALUES (50, 13, '020802', '������������������', '', '');
INSERT INTO `prefix_speciality` VALUES (51, 13, '130302', '����� � �������� ��������� ��� � ���������-������������� ���������', '', '');
INSERT INTO `prefix_speciality` VALUES (52, 13, '120302', '��������� �������', '', '');
INSERT INTO `prefix_speciality` VALUES (53, 14, '080801', '���������� ����������� (� ���������)', '', '');
INSERT INTO `prefix_speciality` VALUES (54, 14, '010503', '�������������� ����������� � ����������������� �������������� ������', '', '');
INSERT INTO `prefix_speciality` VALUES (55, 14, '210406', '���� ����� � ������� ����������', '', '');
INSERT INTO `prefix_speciality` VALUES (56, 14, '210405', '������������, ���������� � �����������', '', '');
INSERT INTO `prefix_speciality` VALUES (57, 15, '030300', '����������', '', '');
INSERT INTO `prefix_speciality` VALUES (58, 16, '030601', '������������', '', '');
                                                       

INSERT INTO `prefix_curriculum` VALUES (1, 1, 1, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (2, 1, 2, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (3, 1, 3, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (4, 1, 4, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (5, 1, 5, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (6, 1, 6, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (7, 2, 7, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (8, 2, 8, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (9, 3, 9, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (10, 3, 10, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (11, 3, 11, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (12, 3, 12, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (13, 3, 13, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (14, 4, 14, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (15, 4, 15, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (16, 4, 16, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (17, 4, 17, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (18, 4, 18, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (19, 5, 19, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (20, 6, 20, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (21, 6, 21, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (22, 6, 22, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (23, 6, 23, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (24, 6, 24, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (25, 6, 25, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (26, 7, 26, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (27, 7, 27, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (28, 7, 28, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (29, 8, 29, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (30, 9, 30, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (31, 9, 31, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (32, 9, 32, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (33, 9, 33, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (34, 10, 34, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (35, 10, 35, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (36, 10, 36, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (37, 10, 37, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (38, 10, 38, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (39, 11, 39, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (40, 11, 40, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (41, 11, 41, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (42, 11, 42, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (43, 11, 43, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (44, 12, 44, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (45, 12, 45, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (46, 12, 46, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (47, 12, 47, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', '');
INSERT INTO `prefix_curriculum` VALUES (48, 13, 48, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', '');
INSERT INTO `prefix_curriculum` VALUES (49, 13, 49, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (50, 13, 50, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (51, 13, 51, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (52, 13, 52, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (53, 14, 53, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (54, 14, 54, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (55, 14, 55, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (56, 14, 56, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (57, 15, 57, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 
INSERT INTO `prefix_curriculum` VALUES (58, 16, 58, '���01', '������� ������� ���� 2006/2007 �.�.', '2006/2007', 'correspondenceformtraining', '', ''); 

CREATE TABLE `moodle`.`mdl_dean_ldap` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(45) NOT NULL DEFAULT '0',
  `firstname` VARCHAR(100) NOT NULL,
  `lastname` VARCHAR(100) NOT NULL,
  `secondname` VARCHAR(100) NOT NULL,
  `group1` VARCHAR(6) NOT NULL DEFAULT '-',
  `group2` VARCHAR(6) NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;
