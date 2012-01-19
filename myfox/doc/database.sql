-- xxx
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

-- 机器表
DROP TABLE IF EXISTS test_host_list;
CREATE TABLE test_host_list (
	host_id int(10) unsigned not null auto_increment,
	host_type tinyint(2) unsigned not null default 0,
	host_stat tinyint(2) unsigned not null default 0,
	host_pos int(10) unsigned not null default 0,
	host_name char(16) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	conn_host varchar(64) not null default '',
	conn_port smallint(5) unsigned not null default 0,
	read_user varchar(64) not null default '',
	read_pass varchar(64) not null default '',
	write_user varchar(64) not null default '',
	write_pass varchar(64) not null default '',
	PRIMARY KEY pk_host_id (host_id),
	UNIQUE KEY uk_host_name (host_name),
	KEY idx_host_stat (host_stat, host_type),
	KEY idx_host_pos (host_pos)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO test_host_list (host_id,host_type,host_stat,host_name,conn_host,conn_port,read_user,read_pass,write_user,write_pass) VALUES (1, 1, 0, 'edp1_9801', '10.232.132.78', 9801, 'db_read', '123456', 'db_write', '123456');
INSERT INTO test_host_list (host_id,host_type,host_stat,host_name,conn_host,conn_port,read_user,read_pass,write_user,write_pass) VALUES (2, 0, 0, 'edp1_9901', '10.232.132.78', 9901, 'db_read', '123456', 'db_write', '123456');
INSERT INTO test_host_list (host_id,host_type,host_stat,host_name,conn_host,conn_port,read_user,read_pass,write_user,write_pass) VALUES (3, 1, 0, 'edp2_9902', '10.232.36.110', 9902, 'db_read', '123456', 'db_write', '123456');
INSERT INTO test_host_list (host_id,host_type,host_stat,host_name,conn_host,conn_port,read_user,read_pass,write_user,write_pass) VALUES (4, 2, 0, 'edp2_8510', '10.232.36.110', 8510, 'db_read', '123456', 'db_write', '123456');

UPDATE test_host_list SET host_pos = INET_ATON(conn_host), addtime = NOW(), modtime = NOW();

-- 配置表
DROP TABLE IF EXISTS test_table_list;
CREATE TABLE test_table_list (
	autokid int(10) unsigned not null auto_increment,
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	backups tinyint(2) unsigned not null default 1,
	max_index_num tinyint(2) unsigned not null default 0,
	split_threshold int(10) unsigned not null default 0,
	split_drift decimal(5,2) unsigned not null default 0.00,
	load_type tinyint(2) unsigned not null default 0,
	route_type tinyint(2) unsigned not null default 0,
	table_name varchar(64) not null default '',
	table_desc varchar(128) not null default '',
	unique_key varchar(256) not null default '',
	xml_filemd varchar(32) not null default '',
	sql_import text not null default '',
	PRIMARY KEY pk_table_id (autokid),
	UNIQUE KEY uk_table_name (table_name)
) ENGINE = InnoDB DEFAULT CHARSET=UTF8;

-- 路由字段表
DROP TABLE IF EXISTS test_table_route;
CREATE TABLE test_table_route (
	autokid int(10) unsigned not null auto_increment,
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	table_name varchar(64) not null default '',
	column_name varchar(64) not null default '',
	tidy_method varchar(64) not null default '',
	tidy_return tinyint(2) unsigned not null default 0,
	is_primary tinyint(2) unsigned not null default 0,
	PRIMARY KEY pk_auto_kid (autokid),
	UNIQUE KEY uk_table_column (table_name, column_name)
) ENGINE = InnoDB DEFAULT CHARSET=UTF8;

-- 表字段配置表
DROP TABLE IF EXISTS test_table_column;
CREATE TABLE test_table_column (
	autokid int(10) unsigned not null auto_increment,
	column_order smallint(5) unsigned not null default 0,
	addtime int(10) unsigned not null default 0,
	modtime int(10) unsigned not null default 0,
	table_name varchar(64) not null default '',
	column_name varchar(64) not null default '',
	column_extra varchar(256) not null default '',
	column_type varchar(64) not null default '',
	column_size varchar(64) not null default '',
	default_value varchar(64) not null default '',
	column_desc varchar(256) not null default '',
	PRIMARY KEY pk_column_id (autokid),
	UNIQUE KEY uk_column_name (table_name,column_name,column_extra),
	KEY idx_column_order (table_name(10), column_order)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;
-- auto_increment字段采用单独的column_type

DROP TABLE IF EXISTS test_table_index;
CREATE TABLE test_table_index (
	autokid int(10) unsigned not null auto_increment,
	addtime int(10) unsigned not null default 0,
	modtime int(10) unsigned not null default 0,
	create_type tinyint(2) unsigned not null default 0,
	table_name varchar(64) not null default '',
	index_name varchar(64) not null default '',
	index_extra varchar(256) not null default '',
	index_text varchar(1024) not null default '',
	PRIMARY KEY pk_index_id (autokid),
	UNIQUE KEY uk_table_index (table_name, index_name, index_extra)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- xxx:以下还没review
-- 路由表
DROP TABLE IF EXISTS test_route_info;
CREATE TABLE test_route_info (
	autokid int(10) unsigned not null auto_increment,
	idxsign int(10) unsigned not null default 0,
	isarchive tinyint(2) unsigned not null default 0,
	useflag tinyint(2) unsigned not null default 0,
	addtime int(10) unsigned not null default 0,
	modtime int(10) unsigned not null default 0,
	hittime int(10) unsigned not null default 0,
	table_name varchar(64) not null default '',
	real_table varchar(128) not null default '',
	hosts_list varchar(1024) not null default '',
	route_text varchar(1024) not null default '',
	PRIMARY KEY pk_route_id (autokid),
	KEY idx_route_sign (idxsign, useflag)
) ENGINE = InnoDB DEFAULT CHARSET=UTF8;

-- 任务队列表
-- namespace支持
DROP TABLE IF EXISTS test_task_queque;
CREATE TABLE IF NOT EXISTS test_task_queque (
	autokid bigint(20) unsigned not null auto_increment,
	agentpos smallint(5) unsigned not null default 0,
	priority smallint(5) unsigned not null default 0,
	trytimes tinyint(2) unsigned not null default 0,
	addtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	begtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	endtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	task_flag tinyint(2) unsigned not null default 0,
	task_type smallint(5) unsigned not null default 0,
	adduser varchar(100) not null default '',
	last_error varchar(200) not null default '',
	tmp_status varchar(1000) not null default '',
	task_info text,
	PRIMARY KEY pk_queque_id (autokid),
	KEY idx_queque_flag (task_flag, trytimes),
	KEY idx_queque_prio (priority),
	KEY idx_queque_time (addtime)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

