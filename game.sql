/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : 127.0.0.1:3306
Source Database       : game

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-09-08 18:07:37
*/

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `only_meeting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `token` char(16) DEFAULT NULL COMMENT '百度签到平台会议toke',
  `qrcodeUrl` text COMMENT '登录二维码链接，可用于会议扫码登录',
  `gId` varchar(255) DEFAULT NULL COMMENT '用户所创建会议使用的人脸组ID',
  `name` varchar(150) NOT NULL COMMENT '项目 名称',
  `desc` text NOT NULL COMMENT '项目描述',
  `startTime` int(10) DEFAULT NULL COMMENT '开始时间，秒',
  `endTime` int(10) DEFAULT NULL COMMENT '结束时间，秒',
  `bdImage` text COMMENT '签到背景图',
  `signinSuccessTip` varchar(50) DEFAULT NULL COMMENT '签到成功提示语',
  `signinFailTip` varchar(50) DEFAULT NULL COMMENT '签到失败提示语',
  `current` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 当前的会议 ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;




CREATE TABLE `only_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `meeting_token` char(16) DEFAULT NULL COMMENT '对应的会议',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `telephone` char(11) NOT NULL COMMENT '手机号',
  `faceImage` varchar(255) NOT NULL COMMENT '人脸图片，这里存url',
  `applyId` int(10) NOT NULL DEFAULT '0' COMMENT '签到平台会议报名唯一ID',
  `qrcode` char(32) DEFAULT NULL COMMENT '会议签到备用二维码值',
  `qrcodeUrl` text COMMENT '会议签到备用二维图片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


