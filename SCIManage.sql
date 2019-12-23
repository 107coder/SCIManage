/*
Navicat MySQL Data Transfer

Source Server         : aLiYin
Source Server Version : 50643
Source Host           : 39.107.113.192:3306
Source Database       : SCIManage

Target Server Type    : MYSQL
Target Server Version : 50643
File Encoding         : 65001

Date: 2019-12-23 12:58:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sci_article
-- ----------------------------
DROP TABLE IF EXISTS `sci_article`;
CREATE TABLE `sci_article` (
  `accession_number` varchar(30) NOT NULL DEFAULT '' COMMENT '入藏号',
  `title` text COMMENT '论文标题',
  `author` text NOT NULL COMMENT '论文作者',
  `claim_author` varchar(255) DEFAULT NULL COMMENT '认领作者',
  `other_author` varchar(255) DEFAULT NULL COMMENT '其他作者',
  `source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `article_type` varchar(30) DEFAULT NULL COMMENT '文章类型',
  `address` text COMMENT '通讯地址',
  `email` text COMMENT '电子邮箱',
  `organization` varchar(1000) DEFAULT NULL COMMENT '作者单位',
  `quite_time` int(10) DEFAULT '0' COMMENT '引用次数',
  `source_shorthand` varchar(100) DEFAULT NULL COMMENT '来源简写',
  `is_top` varchar(255) DEFAULT NULL COMMENT '是否为top',
  `roll` varchar(255) DEFAULT NULL COMMENT '卷',
  `period` varchar(255) DEFAULT NULL COMMENT '期',
  `date` varchar(20) DEFAULT NULL COMMENT '月日',
  `year` varchar(20) DEFAULT NULL COMMENT '年份',
  `page` varchar(30) DEFAULT '' COMMENT '页码',
  `is_first_inst` varchar(255) DEFAULT NULL COMMENT '是否为第一机构',
  `impact_factor` float DEFAULT NULL COMMENT '影响因子',
  `subject` varchar(255) DEFAULT NULL COMMENT '学科门类,所属大类',
  `zk_type` int(5) DEFAULT NULL COMMENT '中科院大类分区',
  `is_cover` int(2) DEFAULT NULL COMMENT '是否为封面论文',
  `sci_type` varchar(255) NOT NULL COMMENT 'sci分区，论文分类',
  `reward_point` int(8) DEFAULT NULL COMMENT '奖励分值',
  `other_info` varchar(255) DEFAULT NULL COMMENT '其他信息',
  `articleStatus` int(3) NOT NULL DEFAULT '0' COMMENT '论文状态\r\n总共5个状态\r\n\r\n0 未认领\r\n1 学院审核中\r\n2 学院不通过\r\n3 学校未审核\r\n4 学校不通过\r\n5 审核通过\r\n增加一个状态\r\n6 批量审核通过',
  `owner` varchar(20) DEFAULT NULL COMMENT '认领人工号',
  `claim_time` varchar(20) DEFAULT NULL COMMENT '认领时间',
  `owner_name` varchar(255) DEFAULT NULL COMMENT '认领人姓名',
  `claimer_unit` varchar(255) DEFAULT NULL COMMENT '认领人工作单位',
  `save` tinyint(2) DEFAULT '0' COMMENT '是否为封存其他来的,0->表示未封存，1->表示封存',
  `add_method` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '文章添加的方式0-》表示通过表格导入，1-》通过手动添加',
  PRIMARY KEY (`accession_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sci_author
-- ----------------------------
DROP TABLE IF EXISTS `sci_author`;
CREATE TABLE `sci_author` (
  `aId` int(11) NOT NULL AUTO_INCREMENT COMMENT '作为主键，对应作者的id',
  `aType` varchar(20) DEFAULT NULL COMMENT '作者类型',
  `aFull_spell` varchar(20) DEFAULT NULL COMMENT '作者姓名全拼',
  `aName` varchar(40) NOT NULL DEFAULT '' COMMENT '作者姓名',
  `aJobNumber` varchar(20) DEFAULT '' COMMENT '作者工号',
  `sSex` varchar(10) NOT NULL DEFAULT '' COMMENT '性别',
  `aEduBackground` varchar(100) DEFAULT '' COMMENT '学历',
  `aJobTitle` varchar(100) DEFAULT '' COMMENT '职称',
  `aisAddress` varchar(10) NOT NULL DEFAULT '0' COMMENT '是否为通讯作者',
  `aUnit` varchar(255) DEFAULT '' COMMENT '作者单位',
  `aArticleNumber` varchar(30) NOT NULL DEFAULT '' COMMENT '作者对应的文章wos号码',
  `aIsClaim` int(3) DEFAULT '0' COMMENT '是否为认领人',
  PRIMARY KEY (`aId`),
  KEY `articleNumber` (`aArticleNumber`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=546 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sci_citation
-- ----------------------------
DROP TABLE IF EXISTS `sci_citation`;
CREATE TABLE `sci_citation` (
  `citation_number` varchar(30) NOT NULL COMMENT '他引文章wos号码 ',
  `title` text NOT NULL COMMENT '他引文章标题',
  `author` text NOT NULL COMMENT '文章作者',
  `reprint_author` text NOT NULL COMMENT '通讯作者',
  `claim_author` varchar(255) DEFAULT NULL COMMENT '文章可认领作者',
  `organization` text NOT NULL COMMENT '作者单位',
  `type` varchar(255) DEFAULT NULL COMMENT ' 文章类型',
  `source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `source_shorthand` varchar(255) DEFAULT NULL COMMENT '文章来源简写',
  `publication_number` varchar(200) DEFAULT NULL COMMENT '期刊号',
  `email` varchar(300) DEFAULT NULL COMMENT '作者邮箱',
  `quote_time` int(10) DEFAULT NULL COMMENT '引用次数',
  `date` varchar(40) DEFAULT NULL COMMENT '论文发表时间',
  `year` varchar(10) DEFAULT NULL COMMENT '论文发表年份',
  `roll` varchar(20) DEFAULT NULL COMMENT '卷号',
  `period` varchar(20) DEFAULT NULL COMMENT '期',
  `page` varchar(100) DEFAULT NULL COMMENT '页范围',
  `is_first_inst` varchar(10) DEFAULT NULL COMMENT '是否为第一机构',
  `impact_factor` float(10,3) DEFAULT NULL COMMENT '影响因子',
  `subject` varchar(255) NOT NULL COMMENT '所属学科',
  `is_top` varchar(10) DEFAULT NULL COMMENT ' 是否为top期刊',
  `zk_type` varchar(20) DEFAULT NULL COMMENT '中科院分类',
  `citation_time` text COMMENT '他引次数',
  `other_info` varchar(255) DEFAULT NULL COMMENT '其他信息',
  `status` int(5) DEFAULT '0' COMMENT '论文状态\r\n0 未认领\r\n1 已认领',
  `claimer_name` varchar(255) DEFAULT NULL COMMENT '认领人姓名',
  `claimer_number` varchar(255) DEFAULT NULL COMMENT '认领人工号',
  `claimer_unit` varchar(255) DEFAULT NULL COMMENT '认领人所属单位',
  `claim_time` varchar(30) DEFAULT NULL COMMENT '认领时间',
  `add_method` int(5) DEFAULT NULL COMMENT '论文添加的方法 0->数据自动导入，1-》手动添加',
  PRIMARY KEY (`citation_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sci_student
-- ----------------------------
DROP TABLE IF EXISTS `sci_student`;
CREATE TABLE `sci_student` (
  `sno` varchar(30) NOT NULL COMMENT '学号',
  `name` varchar(64) NOT NULL COMMENT '姓名',
  `gender` varchar(80) DEFAULT NULL COMMENT '性别',
  `academy` varchar(64) DEFAULT NULL COMMENT '学院',
  `profession` varchar(64) DEFAULT NULL COMMENT '专业',
  PRIMARY KEY (`sno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sci_subject
-- ----------------------------
DROP TABLE IF EXISTS `sci_subject`;
CREATE TABLE `sci_subject` (
  `subject_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sci_user
-- ----------------------------
DROP TABLE IF EXISTS `sci_user`;
CREATE TABLE `sci_user` (
  `job_number` varchar(20) NOT NULL COMMENT '工号',
  `name` varchar(64) NOT NULL COMMENT '姓名',
  `gender` varchar(8) DEFAULT NULL COMMENT '性别',
  `academy` varchar(64) DEFAULT NULL COMMENT '学院',
  `birthday` varchar(16) DEFAULT NULL COMMENT '出生日期',
  `edu_background` varchar(64) DEFAULT NULL COMMENT '学历',
  `degree` varchar(64) DEFAULT NULL COMMENT '学位',
  `job_title` varchar(64) DEFAULT NULL COMMENT '职称',
  `job_title_rank` varchar(16) DEFAULT NULL COMMENT '职称级别',
  `job_title_series` varchar(64) DEFAULT NULL COMMENT '职称系列',
  `full_spell` varchar(32) DEFAULT NULL COMMENT '姓名全拼',
  `password` varchar(64) NOT NULL DEFAULT '$2a$08$MJu0RrP2Lb6Jl9RUkAhb3.zLIi8s5ISVJCVDjiND0FCyY5BSjF222' COMMENT '密码',
  `identity` int(2) NOT NULL DEFAULT '0' COMMENT '0=>普通用户, 1=>院级管理员，2=>校级管理员 ',
  PRIMARY KEY (`job_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
