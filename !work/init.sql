/*
SQLyog Ultimate v12.4.3 (64 bit)
MySQL - 5.7.19-log : Database - 201710_tyadmin_bnb
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `tybnb_admin` */

DROP TABLE IF EXISTS `tybnb_admin`;

CREATE TABLE `tybnb_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `token` varchar(59) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='管理员表';

/*Data for the table `tybnb_admin` */

insert  into `tybnb_admin`(`id`,`username`,`nickname`,`password`,`salt`,`avatar`,`email`,`loginfailure`,`logintime`,`createtime`,`updatetime`,`token`,`status`) values (1,'admin','Admin','0419c28aeb1e70c62689cdbd8809259b','X9et8j','/assets/img/avatar.png','admin@admin.com',0,1508719646,1492186163,1508719646,'a81826a6-ea8d-4fcf-98ed-9b1a11a076a5','normal');

/*Table structure for table `tybnb_admin_log` */

DROP TABLE IF EXISTS `tybnb_admin_log`;

CREATE TABLE `tybnb_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管理员日志表';

/*Data for the table `tybnb_admin_log` */

/*Table structure for table `tybnb_area` */

DROP TABLE IF EXISTS `tybnb_area`;

CREATE TABLE `tybnb_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '省份名称',
  `province_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '省份编号',
  `city` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '城市名称',
  `city_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '城市编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿城市';

/*Data for the table `tybnb_area` */

/*Table structure for table `tybnb_attachment` */

DROP TABLE IF EXISTS `tybnb_attachment`;

CREATE TABLE `tybnb_attachment` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物理路径',
  `imagewidth` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '宽度',
  `imageheight` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '宽度',
  `imagetype` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片类型',
  `imageframes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '透传数据',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建日期',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `uploadtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `storage` enum('local','upyun','qiniu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='附件表';

/*Data for the table `tybnb_attachment` */

insert  into `tybnb_attachment`(`id`,`url`,`imagewidth`,`imageheight`,`imagetype`,`imageframes`,`filesize`,`mimetype`,`extparam`,`createtime`,`updatetime`,`uploadtime`,`storage`,`sha1`) values (1,'/assets/img/qrcode.png','150','150','png',0,21859,'image/png','',1499681848,1499681848,1499681848,'local','17163603d0263e4838b9387ff2cd4877e8b018f6');

/*Table structure for table `tybnb_auth_group` */

DROP TABLE IF EXISTS `tybnb_auth_group`;

CREATE TABLE `tybnb_auth_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名',
  `rules` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则ID',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分组表';

/*Data for the table `tybnb_auth_group` */

insert  into `tybnb_auth_group`(`id`,`pid`,`name`,`rules`,`createtime`,`updatetime`,`status`) values (1,0,'Admin group','*',1490883540,149088354,'normal');

/*Table structure for table `tybnb_auth_group_access` */

DROP TABLE IF EXISTS `tybnb_auth_group_access`;

CREATE TABLE `tybnb_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '会员ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限分组表';

/*Data for the table `tybnb_auth_group_access` */

insert  into `tybnb_auth_group_access`(`uid`,`group_id`) values (1,1);

/*Table structure for table `tybnb_auth_rule` */

DROP TABLE IF EXISTS `tybnb_auth_rule`;

CREATE TABLE `tybnb_auth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('menu','file') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `condition` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '条件',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `ismenu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='节点表';

/*Data for the table `tybnb_auth_rule` */

insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (1,'file',0,'dashboard','Dashboard','fa fa-dashboard\r','','Dashboard tips',1,1497429920,1497429920,85,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (2,'file',0,'general','General','fa fa-cogs','','',1,1497429920,1497430169,80,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (3,'file',0,'category','Category','fa fa-list\r','','Category tips',1,1497429920,1497429920,7,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (5,'file',0,'auth','Auth','fa fa-group','','',1,1497429920,1497430092,13,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (6,'file',2,'general/config','Config','fa fa-cog','','Config tips',1,1497429920,1497430683,34,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (7,'file',2,'general/attachment','Attachment','fa fa-file-image-o','','Attachment tips',1,1497429920,1497430699,41,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (8,'file',2,'general/profile','Profile','fa fa-user\r','','',1,1497429920,1497429920,47,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (9,'file',5,'auth/admin','Admin','fa fa-user','','Admin tips',1,1497429920,1497430320,14,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (10,'file',5,'auth/adminlog','Admin log','fa fa-list-alt','','Admin log tips',1,1497429920,1497430307,19,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (11,'file',5,'auth/group','Group','fa fa-group','','Group tips',1,1497429920,1497429920,23,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (12,'file',5,'auth/rule','Rule','fa fa-bars','','Rule tips',1,1497429920,1497430581,28,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (13,'file',1,'dashboard/index','View','fa fa-circle-o','','',0,1497429920,1497429920,8,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (14,'file',1,'dashboard/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,9,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (15,'file',1,'dashboard/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,11,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (16,'file',1,'dashboard/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,10,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (17,'file',1,'dashboard/multi','Multi','fa fa-circle-o','','',0,1497429920,1497429920,12,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (18,'file',6,'general/config/index','View','fa fa-circle-o','','',0,1497429920,1497429920,42,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (19,'file',6,'general/config/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,43,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (20,'file',6,'general/config/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,44,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (21,'file',6,'general/config/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,45,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (22,'file',6,'general/config/multi','Multi','fa fa-circle-o','','',0,1497429920,1497429920,46,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (23,'file',7,'general/attachment/index','View','fa fa-circle-o','','',0,1497429920,1497429920,35,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (24,'file',7,'general/attachment/select','Select attachment','fa fa-circle-o','','',0,1497429920,1497429920,36,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (25,'file',7,'general/attachment/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,37,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (26,'file',7,'general/attachment/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,38,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (27,'file',7,'general/attachment/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,39,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (28,'file',7,'general/attachment/multi','Multi','fa fa-circle-o','','',0,1497429920,1497429920,40,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (29,'file',8,'general/profile/index','View','fa fa-circle-o','','',0,1497429920,1497429920,48,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (30,'file',8,'general/profile/update','Update profile','fa fa-circle-o','','',0,1497429920,1497429920,49,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (31,'file',8,'general/profile/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,50,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (32,'file',8,'general/profile/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,51,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (33,'file',8,'general/profile/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,52,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (34,'file',8,'general/profile/multi','Multi','fa fa-circle-o','','',0,1497429920,1497429920,53,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (35,'file',3,'category/index','View','fa fa-circle-o','','',0,1497429920,1497429920,2,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (36,'file',3,'category/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,3,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (37,'file',3,'category/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,4,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (38,'file',3,'category/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,5,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (39,'file',3,'category/multi','Multi','fa fa-circle-o','','',0,1497429920,1497429920,6,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (40,'file',9,'auth/admin/index','View','fa fa-circle-o','','',0,1497429920,1497429920,15,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (41,'file',9,'auth/admin/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,16,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (42,'file',9,'auth/admin/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,17,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (43,'file',9,'auth/admin/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,18,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (44,'file',10,'auth/adminlog/index','View','fa fa-circle-o','','',0,1497429920,1497429920,20,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (45,'file',10,'auth/adminlog/detail','Detail','fa fa-circle-o','','',0,1497429920,1497429920,21,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (46,'file',10,'auth/adminlog/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,22,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (47,'file',11,'auth/group/index','View','fa fa-circle-o','','',0,1497429920,1497429920,24,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (48,'file',11,'auth/group/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,25,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (49,'file',11,'auth/group/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,26,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (50,'file',11,'auth/group/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,27,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (51,'file',12,'auth/rule/index','View','fa fa-circle-o','','',0,1497429920,1497429920,29,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (52,'file',12,'auth/rule/add','Add','fa fa-circle-o','','',0,1497429920,1497429920,30,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (53,'file',12,'auth/rule/edit','Edit','fa fa-circle-o','','',0,1497429920,1497429920,31,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (54,'file',12,'auth/rule/del','Delete','fa fa-circle-o','','',0,1497429920,1497429920,32,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (66,'file',0,'user','会员管理','fa fa-user-o','','用于管理前台注册的会员',1,1507963757,1507963757,80,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (67,'file',66,'user/index','查看','fa fa-circle-o','','',0,1507963757,1507963757,84,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (68,'file',66,'user/edit','修改','fa fa-circle-o','','',0,1507963757,1507963757,83,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (69,'file',66,'user/del','删除','fa fa-circle-o','','',0,1507963758,1507963758,82,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (70,'file',66,'user/multi','批量更新','fa fa-circle-o','','',0,1507963758,1507963758,81,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (71,'file',0,'wechat','微信管理','fa fa-wechat','','',1,1507963765,1507963765,33,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (72,'file',71,'wechat/autoreply','自动回复管理','fa fa-reply-all','','',1,1507963765,1507963765,79,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (73,'file',72,'wechat/autoreply/index','查看','fa fa-circle-o','','',0,1507963765,1507963765,78,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (74,'file',72,'wechat/autoreply/add','添加','fa fa-circle-o','','',0,1507963765,1507963765,77,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (75,'file',72,'wechat/autoreply/edit','修改','fa fa-circle-o','','',0,1507963765,1507963765,76,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (76,'file',72,'wechat/autoreply/del','删除','fa fa-circle-o','','',0,1507963765,1507963765,75,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (77,'file',72,'wechat/autoreply/multi','批量更新','fa fa-circle-o','','',0,1507963765,1507963765,74,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (78,'file',71,'wechat/config','配置管理','fa fa-cog','','',1,1507963765,1507963765,73,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (79,'file',78,'wechat/config/index','查看','fa fa-circle-o','','',0,1507963765,1507963765,72,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (80,'file',78,'wechat/config/add','添加','fa fa-circle-o','','',0,1507963765,1507963765,71,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (81,'file',78,'wechat/config/edit','修改','fa fa-circle-o','','',0,1507963765,1507963765,70,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (82,'file',78,'wechat/config/del','删除','fa fa-circle-o','','',0,1507963765,1507963765,69,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (83,'file',78,'wechat/config/multi','批量更新','fa fa-circle-o','','',0,1507963766,1507963766,68,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (84,'file',71,'wechat/menu','菜单管理','fa fa-list','','',1,1507963766,1507963766,67,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (85,'file',84,'wechat/menu/index','查看','fa fa-circle-o','','',0,1507963766,1507963766,66,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (86,'file',84,'wechat/menu/add','添加','fa fa-circle-o','','',0,1507963766,1507963766,65,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (87,'file',84,'wechat/menu/edit','修改','fa fa-circle-o','','',0,1507963766,1507963766,64,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (88,'file',84,'wechat/menu/del','删除','fa fa-circle-o','','',0,1507963766,1507963766,63,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (89,'file',84,'wechat/menu/sync','同步','fa fa-circle-o','','',0,1507963766,1507963766,62,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (90,'file',84,'wechat/menu/multi','批量更新','fa fa-circle-o','','',0,1507963766,1507963766,61,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (91,'file',71,'wechat/response','资源管理','fa fa-list-alt','','',1,1507963766,1507963766,60,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (92,'file',91,'wechat/response/index','查看','fa fa-circle-o','','',0,1507963766,1507963766,59,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (93,'file',91,'wechat/response/add','添加','fa fa-circle-o','','',0,1507963766,1507963766,58,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (94,'file',91,'wechat/response/edit','修改','fa fa-circle-o','','',0,1507963766,1507963766,57,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (95,'file',91,'wechat/response/del','删除','fa fa-circle-o','','',0,1507963766,1507963766,56,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (96,'file',91,'wechat/response/select','选择','fa fa-circle-o','','',0,1507963767,1507963767,55,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (97,'file',91,'wechat/response/multi','批量更新','fa fa-circle-o','','',0,1507963767,1507963767,54,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (104,'file',0,'page','单页管理','fa fa-circle-o\r','','',1,1508465712,1508465712,0,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (105,'file',104,'page/index','查看','fa fa-circle-o','','',0,1508465712,1508465712,0,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (106,'file',104,'page/add','添加','fa fa-circle-o','','',0,1508465712,1508465712,0,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (107,'file',104,'page/edit','编辑','fa fa-circle-o','','',0,1508465712,1508465712,0,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (108,'file',104,'page/del','删除','fa fa-circle-o','','',0,1508465712,1508465712,0,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (109,'file',104,'page/multi','批量更新','fa fa-circle-o','','',0,1508465712,1508465712,0,'normal');
insert  into `tybnb_auth_rule`(`id`,`type`,`pid`,`name`,`title`,`icon`,`condition`,`remark`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`) values (110,'file',0,'demo','AdminLTE示例','fa fa-circle-o','','',1,1508483836,1508483836,0,'normal');

/*Table structure for table `tybnb_bnb` */

DROP TABLE IF EXISTS `tybnb_bnb`;

CREATE TABLE `tybnb_bnb` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=下架,1=正常,2=推荐',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `bnb_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '民宿名称',
  `bnb_adwords` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '广告语',
  `area_province_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_city_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feathers_ids` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提供设施',
  `room_people` int(11) NOT NULL DEFAULT '1' COMMENT '最多入住人数',
  `room_bedroom` int(11) NOT NULL DEFAULT '1' COMMENT '卧室数量',
  `room_livingroom` int(11) NOT NULL DEFAULT '1' COMMENT '客厅数量',
  `room_bed` int(11) NOT NULL DEFAULT '1' COMMENT '床数量',
  `room_toilet` int(11) NOT NULL DEFAULT '1' COMMENT '卫生间数量',
  `bnb_content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房源介绍',
  `map_lng` decimal(15,12) NOT NULL DEFAULT '0.000000000000' COMMENT '地图 经度',
  `map_lat` decimal(15,12) NOT NULL DEFAULT '0.000000000000' COMMENT '地图 纬度',
  `bnb_image` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '展示图片url',
  `landlord_id` int(11) NOT NULL DEFAULT '0' COMMENT '房东id',
  `manager_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理人员id',
  `in_hour` decimal(4,2) NOT NULL DEFAULT '15.00' COMMENT '入住时间 ',
  `out_hour` decimal(4,2) NOT NULL DEFAULT '12.00' COMMENT '退房时间',
  `fee_clean` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '清洁费',
  `fee_deposit` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '保证金',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿';

/*Data for the table `tybnb_bnb` */

/*Table structure for table `tybnb_bnb_info` */

DROP TABLE IF EXISTS `tybnb_bnb_info`;

CREATE TABLE `tybnb_bnb_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `bnb_id` int(11) NOT NULL COMMENT '民宿id',
  `numbers_view` int(11) NOT NULL DEFAULT '0' COMMENT '浏览数量',
  `numbers_order` int(11) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `numbers_favourite` int(11) NOT NULL DEFAULT '0' COMMENT '收藏数量',
  `numbers_score` int(11) NOT NULL DEFAULT '0' COMMENT '评分数量',
  `score_point` decimal(4,2) NOT NULL DEFAULT '5.00' COMMENT '评分',
  `lastorder_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后下订单时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿信息';

/*Data for the table `tybnb_bnb_info` */

/*Table structure for table `tybnb_bnb_specailprice` */

DROP TABLE IF EXISTS `tybnb_bnb_specailprice`;

CREATE TABLE `tybnb_bnb_specailprice` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `bnb_id` int(11) NOT NULL COMMENT '民宿id',
  `price_type` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '价格类型:1=时间段,2=特定时间',
  `begin_date` date NOT NULL COMMENT '开始日期',
  `end_date` date NOT NULL COMMENT '结束日期',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '设置价格: 0.00=房间不可用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿特殊价格\n';

/*Data for the table `tybnb_bnb_specailprice` */

/*Table structure for table `tybnb_bnb_weekprice` */

DROP TABLE IF EXISTS `tybnb_bnb_weekprice`;

CREATE TABLE `tybnb_bnb_weekprice` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `bob_id` int(11) NOT NULL COMMENT '民宿id',
  `price_w1` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周一价格',
  `price_w2` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周二价格',
  `price_w3` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周三价格',
  `price_w4` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周四价格',
  `price_w5` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周五价格',
  `price_w6` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周六价格',
  `price_w7` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '周日价格',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿平日价格';

/*Data for the table `tybnb_bnb_weekprice` */

/*Table structure for table `tybnb_category` */

DROP TABLE IF EXISTS `tybnb_category`;

CREATE TABLE `tybnb_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `flag` set('hot','index','recommend') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键字',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '自定义名称',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `weigh` (`weigh`,`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='分类表';

/*Data for the table `tybnb_category` */

insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (1,0,'page','官方新闻','news','recommend','/assets/img/qrcode.png','','','news',1495262190,1495262190,1,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (2,0,'page','移动应用','mobileapp','hot','/assets/img/qrcode.png','','','mobileapp',1495262244,1495262244,2,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (3,2,'page','微信公众号','wechatpublic','index','/assets/img/qrcode.png','','','wechatpublic',1495262288,1495262288,3,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (4,2,'page','Android开发','android','recommend','/assets/img/qrcode.png','','','android',1495262317,1495262317,4,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (5,0,'page','软件产品','software','recommend','/assets/img/qrcode.png','','','software',1495262336,1499681850,5,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (6,5,'page','网站建站','website','recommend','/assets/img/qrcode.png','','','website',1495262357,1495262357,6,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (7,5,'page','企业管理软件','company','index','/assets/img/qrcode.png','','','company',1495262391,1495262391,7,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (8,6,'page','PC端','website-pc','recommend','/assets/img/qrcode.png','','','website-pc',1495262424,1495262424,8,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (9,6,'page','移动端','website-mobile','recommend','/assets/img/qrcode.png','','','website-mobile',1495262456,1495262456,9,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (10,7,'page','CRM系统 ','company-crm','recommend','/assets/img/qrcode.png','','','company-crm',1495262487,1495262487,10,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (11,7,'page','SASS平台软件','company-sass','recommend','/assets/img/qrcode.png','','','company-sass',1495262515,1495262515,11,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (12,0,'test','测试1','test1','recommend','/assets/img/qrcode.png','','','test1',1497015727,1497015727,12,'normal');
insert  into `tybnb_category`(`id`,`pid`,`type`,`name`,`nickname`,`flag`,`image`,`keywords`,`description`,`diyname`,`createtime`,`updatetime`,`weigh`,`status`) values (13,0,'test','测试2','test2','recommend','/assets/img/qrcode.png','','','test2',1497015738,1497015738,13,'normal');

/*Table structure for table `tybnb_config` */

DROP TABLE IF EXISTS `tybnb_config`;

CREATE TABLE `tybnb_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量名',
  `group` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分组',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '变量值',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '变量字典数据',
  `rule` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置';

/*Data for the table `tybnb_config` */

insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (1,'name','basic','Site name','请填写站点名称','string','sdty admin','','required','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (2,'beian','basic','Beian','粤ICP备15054802号-4','string','','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (3,'cdnurl','basic','Cdn url','如果使用CDN云储存请配置该值','string','','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (4,'version','basic','Version','如果静态资源有变动请重新配置该值','string','20171020','','required','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (5,'timezone','basic','Timezone','','string','Asia/Shanghai','','required','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (6,'forbiddenip','basic','Forbidden ip','一行一条记录','text','','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (7,'languages','basic','Languages','','array','{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}','','required','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (8,'fixedpage','basic','Fixed page','请尽量输入左侧菜单栏存在的链接','string','dashboard','','required','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (9,'categorytype','dictionary','Cateogry type','','array','{\"default\":\"Default\",\"page\":\"Page\",\"article\":\"Article\",\"test\":\"Test\"}','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (10,'configgroup','dictionary','Config group','','array','{\"basic\":\"Basic\",\"email\":\"Email\",\"dictionary\":\"Dictionary\",\"user\":\"User\",\"example\":\"Example\"}','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (11,'mail_type','email','Mail type','选择邮件发送方式','select','1','[\"Please select\",\"SMTP\",\"Mail\"]','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (12,'mail_smtp_host','email','Mail smtp host','错误的配置发送邮件会导致服务器超时','string','smtp.qq.com','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (13,'mail_smtp_port','email','Mail smtp port','(不加密默认25,SSL默认465,TLS默认587)','string','465','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (14,'mail_smtp_user','email','Mail smtp user','（填写完整用户名）','string','10000','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (15,'mail_smtp_pass','email','Mail smtp password','（填写您的密码）','string','password','','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (16,'mail_verify_type','email','Mail vertify type','（SMTP验证方式[推荐SSL]）','select','2','[\"None\",\"TLS\",\"SSL\"]','','');
insert  into `tybnb_config`(`id`,`name`,`group`,`title`,`tip`,`type`,`value`,`content`,`rule`,`extend`) values (17,'mail_from','email','Mail from','','string','10000@qq.com','','','');

/*Table structure for table `tybnb_feathers` */

DROP TABLE IF EXISTS `tybnb_feathers`;

CREATE TABLE `tybnb_feathers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常显示',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '设施名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿设施';

/*Data for the table `tybnb_feathers` */

/*Table structure for table `tybnb_image_class` */

DROP TABLE IF EXISTS `tybnb_image_class`;

CREATE TABLE `tybnb_image_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常显示',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '设施名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿图片类型';

/*Data for the table `tybnb_image_class` */

/*Table structure for table `tybnb_images` */

DROP TABLE IF EXISTS `tybnb_images`;

CREATE TABLE `tybnb_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常显示',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `bnb_id` int(11) NOT NULL COMMENT '民宿id',
  `image_class_id` int(11) NOT NULL COMMENT '图片类型id',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '设施名称',
  `url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿图片';

/*Data for the table `tybnb_images` */

/*Table structure for table `tybnb_landlordinfo` */

DROP TABLE IF EXISTS `tybnb_landlordinfo`;

CREATE TABLE `tybnb_landlordinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `users_id` int(11) NOT NULL,
  `avator` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '房东头像',
  `work` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职业',
  `demo` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '房东简介，100字以内',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿房东信息';

/*Data for the table `tybnb_landlordinfo` */

/*Table structure for table `tybnb_model` */

DROP TABLE IF EXISTS `tybnb_model`;

CREATE TABLE `tybnb_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿模板';

/*Data for the table `tybnb_model` */

/*Table structure for table `tybnb_page` */

DROP TABLE IF EXISTS `tybnb_page`;

CREATE TABLE `tybnb_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int(10) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关键字',
  `flag` set('hot','index','recommend') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标志',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击',
  `comments` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='单页表';

/*Data for the table `tybnb_page` */

insert  into `tybnb_page`(`id`,`category_id`,`title`,`keywords`,`flag`,`image`,`content`,`icon`,`views`,`comments`,`createtime`,`updatetime`,`weigh`,`status`) values (1,4,'test','test','index','/assets/img/qrcode.png','<p>test</p>','',0,0,1499769482,1508477955,0,'normal');

/*Table structure for table `tybnb_user` */

DROP TABLE IF EXISTS `tybnb_user`;

CREATE TABLE `tybnb_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `prevtime` int(10) NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(11) NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录IP',
  `joinip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '加入IP',
  `jointime` int(10) NOT NULL DEFAULT '0' COMMENT '加入时间',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `token` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Token',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='会员表';

/*Data for the table `tybnb_user` */

insert  into `tybnb_user`(`id`,`username`,`nickname`,`password`,`salt`,`email`,`mobile`,`avatar`,`level`,`gender`,`birthday`,`score`,`prevtime`,`loginfailure`,`logintime`,`loginip`,`joinip`,`jointime`,`createtime`,`updatetime`,`token`,`status`) values (3,'admin','admin','c13f62012fd6a8fdf06b3452a94430e5','rpR6Bv','admin@163.com','13888888888','/assets/img/avatar.png',0,0,NULL,0,1491822015,0,1491822038,'127.0.0.1','127.0.0.1',1491461418,0,0,'','normal');

/*Table structure for table `tybnb_userinfo` */

DROP TABLE IF EXISTS `tybnb_userinfo`;

CREATE TABLE `tybnb_userinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `users_id` int(11) NOT NULL,
  `map_lng` decimal(15,12) NOT NULL DEFAULT '0.000000000000' COMMENT '地图经度',
  `map_lat` decimal(15,12) NOT NULL DEFAULT '0.000000000000' COMMENT '地图纬度',
  `mapupdate_time` int(11) NOT NULL DEFAULT '0' COMMENT '地理位置更新时间',
  `lastlogin_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `lastlogin_ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '最后登录ip地址',
  `login_numbers` int(11) NOT NULL DEFAULT '0' COMMENT '登陆次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿用户信息\n';

/*Data for the table `tybnb_userinfo` */

/*Table structure for table `tybnb_users` */

DROP TABLE IF EXISTS `tybnb_users`;

CREATE TABLE `tybnb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `status` enum('-1','0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-1' COMMENT '状态:-1=已删除, 0=隐藏,1=正常',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wx_openid` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '微信openid',
  `user_nickname` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信用户名,用户昵称',
  `user_avator` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信头像url，用户头像',
  `user_sex` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '用户性别:1=男,2=女',
  `user_class` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '用户类型:1=普通用户,2=已认证用户',
  `user_truename` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_mobile` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `user_idcard_number` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证号码',
  `user_idcard_image` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证扫描件url',
  `is_landlord` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '是否房东:0=否,1=是',
  `is_cleaner` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '是否保洁:0=否,1=是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='民宿用户';

/*Data for the table `tybnb_users` */

/*Table structure for table `tybnb_wechat_autoreply` */

DROP TABLE IF EXISTS `tybnb_wechat_autoreply`;

CREATE TABLE `tybnb_wechat_autoreply` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `text` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '触发文本',
  `eventkey` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '响应事件',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='微信自动回复表';

/*Data for the table `tybnb_wechat_autoreply` */

insert  into `tybnb_wechat_autoreply`(`id`,`title`,`text`,`eventkey`,`remark`,`createtime`,`updatetime`,`status`) values (1,'输入hello','hello','58c7d908c4570','123',1493366855,1493366855,'normal');
insert  into `tybnb_wechat_autoreply`(`id`,`title`,`text`,`eventkey`,`remark`,`createtime`,`updatetime`,`status`) values (2,'输入你好','你好','58fdfaa9e1965','sad',1493704976,1493704976,'normal');

/*Table structure for table `tybnb_wechat_config` */

DROP TABLE IF EXISTS `tybnb_wechat_config`;

CREATE TABLE `tybnb_wechat_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置标题',
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置值',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='微信配置表';

/*Data for the table `tybnb_wechat_config` */

insert  into `tybnb_wechat_config`(`id`,`name`,`title`,`value`,`createtime`,`updatetime`) values (1,'menu','微信菜单','[{\"name\":\"SDTY\",\"sub_button\":[{\"name\":\"官网\",\"type\":\"view\",\"url\":\"http:\\/\\/www.SDTY.test\"},{\"name\":\"在线演示\",\"type\":\"view\",\"url\":\"http:\\/\\/test.bnb.test:71\"},{\"name\":\"文档\",\"type\":\"view\",\"url\":\"http:\\/\\/doc.sdty.test\"}]},{\"name\":\"在线客服\",\"type\":\"click\",\"key\":\"58fdfaa9e1965\"},{\"name\":\"关于我们\",\"type\":\"click\",\"key\":\"58c7d908c4570\"}]',1497398820,1508719838);
insert  into `tybnb_wechat_config`(`id`,`name`,`title`,`value`,`createtime`,`updatetime`) values (2,'service','客服配置','{\"onlinetime\":\"09:00-18:00\",\"offlinemsg\":\"请在工作时间联系客服！\",\"nosessionmsg\":\"当前没有客服在线！请稍后重试！\",\"waitformsg\":\"请问有什么可以帮到您？\"}',1497429674,1497429674);
insert  into `tybnb_wechat_config`(`id`,`name`,`title`,`value`,`createtime`,`updatetime`) values (3,'signin','连续登录配置','{\"s1\":\"100\",\"s2\":\"200\",\"s3\":\"300\",\"sn\":\"500\"}',1497429711,1497429711);

/*Table structure for table `tybnb_wechat_context` */

DROP TABLE IF EXISTS `tybnb_wechat_context`;

CREATE TABLE `tybnb_wechat_context` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `openid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `eventkey` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `command` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '内容',
  `refreshtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后刷新时间',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='微信上下文表';

/*Data for the table `tybnb_wechat_context` */

insert  into `tybnb_wechat_context`(`id`,`openid`,`type`,`eventkey`,`command`,`message`,`refreshtime`,`createtime`,`updatetime`) values (1,'olwqlxMRpLi0tmuADud8giXi7Tpg','','58c7d908c4570','','',1508720093,1508720093,1508720093);

/*Table structure for table `tybnb_wechat_response` */

DROP TABLE IF EXISTS `tybnb_wechat_response`;

CREATE TABLE `tybnb_wechat_response` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '资源名',
  `eventkey` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件',
  `type` enum('text','image','news','voice','video','music','link','app') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text' COMMENT '类型',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event` (`eventkey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='微信资源表';

/*Data for the table `tybnb_wechat_response` */

insert  into `tybnb_wechat_response`(`id`,`title`,`eventkey`,`type`,`content`,`remark`,`createtime`,`updatetime`,`status`) values (1,'签到送积分','58adaf7876aab','app','{\"app\":\"signin\"}','',1487777656,1487777656,'normal');
insert  into `tybnb_wechat_response`(`id`,`title`,`eventkey`,`type`,`content`,`remark`,`createtime`,`updatetime`,`status`) values (2,'关于我们','58bf944aa0777','app','{\"app\":\"page\",\"id\":\"1\"}','',1488950346,1488950346,'normal');
insert  into `tybnb_wechat_response`(`id`,`title`,`eventkey`,`type`,`content`,`remark`,`createtime`,`updatetime`,`status`) values (3,'自动回复1','58c7d908c4570','text','{\"content\":\"world\"}','',1489492232,1489492232,'normal');
insert  into `tybnb_wechat_response`(`id`,`title`,`eventkey`,`type`,`content`,`remark`,`createtime`,`updatetime`,`status`) values (4,'联系客服','58cb852984970','app','{\"app\":\"service\"}','',1489732905,1489732905,'normal');
insert  into `tybnb_wechat_response`(`id`,`title`,`eventkey`,`type`,`content`,`remark`,`createtime`,`updatetime`,`status`) values (5,'自动回复2','58fdfaa9e1965','text','{\"content\":\"!\"}','',1493039785,1493039785,'normal');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
