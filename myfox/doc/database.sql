
-- 节点表
DROP TABLE IF EXISTS test_node_list;
CREATE TABLE test_node_list (
	node_id smallint(5) unsigned not null auto_increment,
	node_type tinyint(2) unsigned not null default 0,
	node_name char(16) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	PRIMARY KEY pk_node_id (node_id),
	UNIQUE KEY uk_node_name (node_name)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO test_node_list (node_id,node_type,node_name,addtime,modtime) VALUES (1,0,'online_01',NOW(),NOW());
INSERT INTO test_node_list (node_id,node_type,node_name,addtime,modtime) VALUES (2,0,'online_02',NOW(),NOW());
INSERT INTO test_node_list (node_id,node_type,node_name,addtime,modtime) VALUES (3,1,'archive_01',NOW(),NOW());

-- 机器表
DROP TABLE IF EXISTS test_host_list;
CREATE TABLE test_host_list (
	host_id int(10) unsigned not null auto_increment,
	node_id smallint(5) unsigned not null default 0,
	host_type tinyint(2) unsigned not null default 0,
	host_stat tinyint(2) unsigned not null default 0,
	host_name char(16) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	conn_host varchar(64) not null default '',
	conn_port smallint(5) unsigned not null default 0,
	user_rw varchar(128) not null default '',
	user_ro varchar(128) not null default '',
	PRIMARY KEY pk_host_id (host_id),
	UNIQUE KEY uk_host_name (host_name),
	KEY idx_host_node (node_id,host_stat,host_type)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- 配置表
DROP TABLE IF EXISTS test_table_list;
CREATE TABLE test_table_list (
	autokid int(10) unsigned not null auto_increment,
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	backups tinyint(2) unsigned not null default 1,
	loadtype tinyint(2) unsigned not null default 0,
	split_threshold int(10) unsigned not null default 0,
	split_drift decimal(5,2) unsigned not null default 0.00,
	route_method tinyint(2) unsigned not null default 0,
	route_fields varchar(128) not null default '',
	tabname varchar(64) not null default '',
	tabdesc varchar(128) not null default '',
	filemd5 varchar(32) not null default '',
	PRIMARY KEY pk_table_id (autokid),
	UNIQUE KEY uk_table_name (tabname)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO test_table_list (addtime,modtime,loadtype,tabname,split_threshold,split_drift,route_method,route_fields) VALUES (NOW(),NOW(),1,'mirror',1000,0.2,0,''), (NOW(),NOW(),0,'numsplit',1000,0.4,1,'{thedate:date,cid:int}');

-- 路由表
DROP TABLE IF EXISTS test_route_info;
CREATE TABLE IF NOT EXISTS test_route_info (
	autokid int(10) unsigned not null auto_increment,
	thedate int(10) unsigned not null default 0,
	idxsign int(10) unsigned not null default 0,
	isarchive tinyint(2) unsigned not null default 0,
	useflag tinyint(2) unsigned not null default 0,
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	tabname varchar(64) not null default '',
	routes varchar(1024) not null default '',
	split_info text,
	split_temp text,
	PRIMARY KEY pk_route_id (autokid),
	KEY idx_route_sign (idxsign, useflag),
	KEY idx_route_date (thedate, useflag)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- 系统状态表
DROP TABLE IF EXISTS test_settings;
CREATE TABLE IF NOT EXISTS test_settings (
	autokid int(10) unsigned not null auto_increment,
	cfgname varchar(32) not null default '',
	ownname varchar(32) not null default '',
	cfgvalue varchar(255) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	PRIMARY KEY pk_setting_id (autokid),
	UNIQUE KEY uk_setting_name (ownname,cfgname)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- 任务队列表
DROP TABLE IF EXISTS test_task_queque;
CREATE TABLE IF NOT EXISTS test_task_queque (
	autokid bigint(20) unsigned not null auto_increment,
	agentid smallint(5) unsigned not null default 0,
	priority smallint(5) unsigned not null default 0,
	trytimes tinyint(2) unsigned not null default 0,
	addtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	begtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	endtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	task_flag tinyint(2) unsigned not null default 0,
	task_type smallint(5) unsigned not null default 0,
	adduser varchar(100) not null default '',
	last_error varchar(200) not null default '',
	task_info text,
	PRIMARY KEY pk_queque_id (autokid),
	KEY idx_queque_flag (agentid, task_flag, trytimes),
	KEY idx_queque_prio (priority),
	KEY idx_queque_time (addtime)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- SELECT ... FROM test_task_queque WHERE task_flag = ? AND trytimes < ? ORDER BY priority ASC, trytimes ASC, autokid ASC-- SELECT ... FROM test_task_queque WHERE addtime < ?

