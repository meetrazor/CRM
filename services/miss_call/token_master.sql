/*
SQLyog Ultimate v8.55 
MySQL - 5.5.32 : Database - alliance_partner
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`alliance_partner` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `alliance_partner`;

/*Table structure for table `token_master` */

DROP TABLE IF EXISTS `token_master`;

CREATE TABLE `token_master` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(100) DEFAULT NULL,
  `token_validity` datetime DEFAULT '0000-00-00 00:00:00',
  `created_at` datetime DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(20) DEFAULT NULL,
  `browser` varchar(20) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `device_type` varchar(10) DEFAULT NULL,
  `token_for` varchar(100) DEFAULT NULL,
  `no_of_request` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT '0000-00-00 00:00:00',
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `token_master` */

insert  into `token_master`(`token_id`,`token`,`token_validity`,`created_at`,`ip`,`browser`,`platform`,`device_type`,`token_for`,`no_of_request`,`is_active`,`created_by`,`updated_at`,`updated_by`) values (1,'65455dasdasd54','2018-05-11 00:00:00','2017-05-11 00:00:00',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'0000-00-00 00:00:00',NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
