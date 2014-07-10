create database if not exists sle collate  utf8_general_ci default character set utf8;

use sle;
create table  if not exists  sle_user(
  id int auto_increment primary key,
  name varchar(50) not null collate utf8_general_ci  default '' comment '用户名',
  pwd varchar(255) not null  collate utf8_general_ci default '' comment '密码',
  `desc` varchar(255) collate utf8_general_ci default '' comment '简介',
  create_time int unsigned default 0 comment '创建时间'
  )engine=innodb default character set utf8  collate utf8_general_ci;


create table  if not exists  sle_role(
  id int auto_increment primary key,
  name varchar(50) not null collate utf8_general_ci  default '' comment '角色名称',
  `desc` varchar(255) collate utf8_general_ci default '' comment '简介',
  create_time int unsigned default 0 comment '创建时间'
  )engine=innodb default character set utf8  collate utf8_general_ci;

create table  if not exists  sle_menu(
  id int auto_increment primary key,
  name varchar(50) not null collate utf8_general_ci  default '' comment '菜单名称',
  parent_id int not null default 0 comment '父id',
  `desc` varchar(255) collate utf8_general_ci default '' comment '简介',
  create_time int unsigned default 0 comment '创建时间'
  )engine=innodb default character set utf8  collate utf8_general_ci;

create table  if not exists  sle_role_user(
  user_id int not null comment '用户id',
  role_id int not null comment '角色id'
  )engine=innodb default character set utf8  collate utf8_general_ci;

create table  if not exists  sle_role_menu(
  role_id int not null comment '用户id',
  menu_id int not null comment '菜单id'
  )engine=innodb default character set utf8  collate utf8_general_ci;




INSERT INTO `sle_user` VALUES (1,'sys','123455','开发者用户',1404088414);
INSERT INTO `sle_role` VALUES (1,'developer','开发组',1404099929);
INSERT INTO `sle_role_user` VALUES (1,1);