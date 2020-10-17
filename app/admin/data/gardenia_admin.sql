/*
 Navicat Premium Data Transfer

 Source Server         : 本地数据库
 Source Server Type    : MySQL
 Source Server Version : 50730
 Source Host           : localhost:3306
 Source Schema         : gardenia_admin

 Target Server Type    : MySQL
 Target Server Version : 50730
 File Encoding         : 65001

 Date: 17/10/2020 15:52:16
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for gardenia_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `gardenia_auth_group`;
CREATE TABLE `gardenia_auth_group`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `rules` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '菜单规则',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分组类型：0=超级管理,1=普通管理',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of gardenia_auth_group
-- ----------------------------
INSERT INTO `gardenia_auth_group` VALUES (8, '测试', 0, '3,4,6,43,45,8,37,41,40,42,46,2,5,7,36,35,50,51,1', 1);
INSERT INTO `gardenia_auth_group` VALUES (6, '超级管理', 1, '', 0);
INSERT INTO `gardenia_auth_group` VALUES (10, '浅香hhh', 1, '3,4,6,43,45,8,37,41,40,42,1,52', 0);

-- ----------------------------
-- Table structure for gardenia_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `gardenia_auth_group_access`;
CREATE TABLE `gardenia_auth_group_access`  (
  `id` int(13) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(13) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uid_group_id`(`uid`, `group_id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of gardenia_auth_group_access
-- ----------------------------
INSERT INTO `gardenia_auth_group_access` VALUES (1, 5, 6);
INSERT INTO `gardenia_auth_group_access` VALUES (2, 6, 6);

-- ----------------------------
-- Table structure for gardenia_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `gardenia_auth_rule`;
CREATE TABLE `gardenia_auth_rule`  (
  `id` int(13) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0：菜单，1：其它',
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `title` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT 0 COMMENT '层级',
  `pid` int(13) NOT NULL DEFAULT 0 COMMENT '父级ID',
  `root_id` int(13) NOT NULL DEFAULT 0 COMMENT '根ID',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `weigh` int(8) NOT NULL DEFAULT 0 COMMENT '权重',
  `condition` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 54 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '认证规则表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of gardenia_auth_rule
-- ----------------------------
INSERT INTO `gardenia_auth_rule` VALUES (3, 0, '/User', '用户管理', 'icon-menu', 1, 0, 0, 1, 7, '');
INSERT INTO `gardenia_auth_rule` VALUES (4, 0, '/User/index', '用户列表', 'icon-menu', 2, 3, 3, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (5, 0, '/Menu/index', '菜单规则', 'icon-menu', 2, 2, 2, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (6, 1, '/User/delete', '删除', 'icon-menu', 3, 4, 3, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (7, 1, 'test_delete', '删除', 'icon-menu', 3, 5, 2, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (8, 0, '/UserGroup/index', '用户组管理', 'icon-menu', 2, 3, 3, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (36, 1, '/Menu/create', '创建', 'icon-menu', 2, 5, 2, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (35, 1, '/Menu/getData', '获取列表数据', 'icon-menu', 2, 5, 2, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (46, 0, '/Mall', '商城管理', 'icon-menu', 1, 0, 0, 1, 9, '');
INSERT INTO `gardenia_auth_rule` VALUES (37, 1, '/UserGroup/getData', '获取数据', 'icon-menu', 0, 8, 3, 1, 1, '');
INSERT INTO `gardenia_auth_rule` VALUES (40, 1, '/UserGroup/1', '获取数据2', 'icon-menu', 2, 3, 3, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (41, 1, '/UserGroup/create', '创建', 'icon-menu', 0, 8, 3, 1, 2, '');
INSERT INTO `gardenia_auth_rule` VALUES (42, 1, '/User/getData', '获取数据', 'icon-menu', 2, 3, 3, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (43, 1, '/User/edit', '编辑', 'icon-menu', 0, 4, 3, 1, 3, '');
INSERT INTO `gardenia_auth_rule` VALUES (45, 1, '/User/create', '创建', 'icon-menu', 0, 4, 3, 1, 4, '');
INSERT INTO `gardenia_auth_rule` VALUES (2, 0, '/System', '系统管理', 'icon-menu', 1, 0, 0, 1, 0, '');
INSERT INTO `gardenia_auth_rule` VALUES (50, 1, '/Menu/edit', '编辑', 'icon-menu', 0, 5, 2, 0, 8, '');
INSERT INTO `gardenia_auth_rule` VALUES (51, 0, '/Log/index', '日志管理', 'icon-menu', 0, 2, 2, 1, 7, '');
INSERT INTO `gardenia_auth_rule` VALUES (1, 0, '/', '首页', 'icon-menu', 1, 0, 0, 1, 10, '');
INSERT INTO `gardenia_auth_rule` VALUES (52, 0, '/Test/index', '测试', 'icon-menu', 0, 0, 0, 1, 0, '');

-- ----------------------------
-- Table structure for gardenia_migrations
-- ----------------------------
DROP TABLE IF EXISTS `gardenia_migrations`;
CREATE TABLE `gardenia_migrations`  (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `start_time` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `end_time` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of gardenia_migrations
-- ----------------------------
INSERT INTO `gardenia_migrations` VALUES (20200712071245, 'Database', '2020-07-12 18:34:36', '2020-07-12 18:34:40', 0);

-- ----------------------------
-- Table structure for gardenia_user
-- ----------------------------
DROP TABLE IF EXISTS `gardenia_user`;
CREATE TABLE `gardenia_user`  (
  `id` int(13) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户名，登陆使用',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '$2y$10$LclTZa3FI4G7b9Vyhxezj.uks8QV09dfhNH4X5.ZgYGQqnEIbnlMG' COMMENT '用户密码，默认123456',
  `login_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '登陆状态',
  `login_code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '排他性登陆标识',
  `last_login_ip` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_login_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '最后登录时间',
  `login_ip` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '当前登录IP',
  `login_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '当前登录时间',
  `p_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `root_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '根ID',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '删除状态，1已删除',
  `create_time` bigint(20) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` bigint(20) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of gardenia_user
-- ----------------------------
INSERT INTO `gardenia_user` VALUES (5, 'admin', '$2y$10$LclTZa3FI4G7b9Vyhxezj.uks8QV09dfhNH4X5.ZgYGQqnEIbnlMG', 1, 'c6889b7007d9f72dc506575955cbd68a', '172.17.0.1', 1602912330, '172.17.0.1', 1602912336, 0, 0, 0, 1598885163, 0);
INSERT INTO `gardenia_user` VALUES (6, 'test', '$2y$10$LclTZa3FI4G7b9Vyhxezj.uks8QV09dfhNH4X5.ZgYGQqnEIbnlMG', 1, 'bb654faa522b88e0d2d8f1bbf067ebda', '172.17.0.1', 1600962725, '172.17.0.1', 1601205604, 0, 5, 0, 1600527951, 0);

SET FOREIGN_KEY_CHECKS = 1;
