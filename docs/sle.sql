create database sle collate  utf8_general_ci default character set utf8;


create table sle_user(id int auto_increment primary key,name varchar(50) not null collate utf8_general_ci  default '',pwd varchar(255) not null  collate utf8_general_ci default '',`desc` varchar(255) collate utf8_general_ci default '' comment '简介')engine=innodb default character set utf8  collate utf8_general_ci;