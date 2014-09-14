drop database if exists sle;
create database if not exists sle collate  utf8_general_ci default character set utf8;

use sle;
set names utf8;

create table  if not exists  sle_user(
  id int auto_increment primary key,
  name varchar(50) not null collate utf8_general_ci  default '' comment '用户名',
  pwd varchar(255) not null  collate utf8_general_ci default '' comment '密码',
  `desc` varchar(255) collate utf8_general_ci default '' comment '简介',
  create_time int unsigned default 0 comment '创建时间'
  )engine=innodb default character set utf8  collate utf8_general_ci comment '用户表';


create table  if not exists  sle_role(
  id int auto_increment primary key,
  name varchar(50) not null collate utf8_general_ci  default '' comment '角色名称',
  `desc` varchar(255) collate utf8_general_ci default '' comment '简介',
  create_time int unsigned default 0 comment '创建时间'
  )engine=innodb default character set utf8  collate utf8_general_ci comment '角色表';


create table  if not exists  sle_menu(
  id int auto_increment primary key,
  name varchar(50) not null collate utf8_general_ci  default '' comment '菜单名称',
  icon    varchar(20) default '' comment '菜单图标',
  `desc` varchar(255) collate utf8_general_ci default '' comment '简介',
  ctl_id int default 0 comment '模块id', 
  act_id int default 0 comment '动作id', 
  parent_id int not null default 0 comment '父id',
  orderby  int default 0 comment '排序',
  create_time int unsigned default 0 comment '创建时间'
)engine=innodb default character set utf8  collate utf8_general_ci comment '菜单表';
insert into  sle_menu values(null,'首页','icon-home','后台首页',1,1,0,0,unix_timestamp());
insert into  sle_menu values(null,'系统管理','icon-cogs','系统管理',0,0,0,10,unix_timestamp());
insert into  sle_menu values(null,'菜单管理','icon-cog','菜单管理',2,1,2,101,unix_timestamp());

create table if not exists sle_controller(
  id int auto_increment primary key,
  name varchar(100) default '' collate utf8_general_ci comment '控制器',
  title varchar(200) default '' collate utf8_general_ci comment '控制器名称',
  `desc` varchar(255) default '' comment '控制器简单描述'
)engine=innodb default character set utf8 collate utf8_general_ci comment '控制器表';
insert into  sle_controller values(null,'Index','首页','首页');
insert into  sle_controller values(null,'Menu','菜单管理','菜单管理');


create table if not exists sle_action(
  id int auto_increment primary key,
  name varchar(100) default '' collate utf8_general_ci comment '动作',
  title varchar(200) default '' collate utf8_general_ci comment '动作名称',
  `desc` varchar(255) default '' comment '动作简单描述'
)engine=innodb default character set utf8 collate utf8_general_ci comment '动作表';
insert into  sle_action values(null,'index','列表','列表');



create table if not exists sle_controller_action(
  id     int  auto_increment primary key,
  ctl_id int not null comment '控制器id',
  act_id int not null comment '动作id'
)engine=innodb default character set utf8 collate utf8_general_ci comment '控制器动作中间表';



create table  if not exists  sle_role_user(
  user_id int not null comment '用户id',
  role_id int not null comment '角色id',
  primary key(user_id,role_id)
)engine=innodb default character set utf8  collate utf8_general_ci comment '角色用户中间表';




INSERT INTO `sle_user` VALUES (1,'sys','123455','开发者用户',1404088414);
INSERT INTO  sle_user values(null,'admin','111','管理员用户',unix_timestamp());
INSERT INTO `sle_role` VALUES (1,'developer','开发组',1404099929);
INSERT INTO `sle_role_user` VALUES (1,1);