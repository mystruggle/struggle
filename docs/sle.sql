create database if not exists sle collate  utf8_general_ci default character set utf8;

use sle;
create table  if not exists  sle_user(id int auto_increment primary key,name varchar(50) not null collate utf8_general_ci  default '',pwd varchar(255) not null  collate utf8_general_ci default '',`desc` varchar(255) collate utf8_general_ci default '' comment '简介',create_time int unsigned default 0 comment '创建时间')engine=innodb default character set utf8  collate utf8_general_ci;
INSERT INTO `sle_user` VALUES (1,'sys','123455',1,1404088414);