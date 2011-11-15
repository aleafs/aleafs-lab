
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

INSERT INTO test_table_list (addtime,modtime,backups,loadtype,tabname,split_threshold,split_drift,route_method,route_fields) VALUES (NOW(),NOW(),2,1,'mirror',1000,0.2,0,''), (NOW(),NOW(),2,0,'numsplit',1000,0.4,1,'{thedate:date,cid:int}');

DROP TABLE IF EXISTS test_table_column;
CREATE TABLE test_table_column (
	autokid int(10) unsigned not null auto_increment,
	colseqn smallint(5) unsigned not null default 0,
	isextra tinyint(2) unsigned not null default 0,
	tabname varchar(64) not null default '',
	colname varchar(64) not null default '',
	excepts varchar(100) not null default '',
	coltype varchar(15) not null default '',
	dfltval varchar(100),
	coldesc varchar(100) not null default '',
	sqlchar varchar(200) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	PRIMARY KEY pk_column_id (autokid),
	UNIQUE KEY uk_column_name (tabname,colname,excepts),
	KEY idx_column_order (tabname,colseqn)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO test_table_column (colseqn,tabname,colname,coltype,dfltval,coldesc,sqlchar,addtime,modtime) VALUES (1,'numsplit','thedate','date','0000-00-00','数据日期',"thedate date not null default '0000-00-00'", NOW(), NOW());
INSERT INTO test_table_column (colseqn,tabname,colname,coltype,dfltval,coldesc,sqlchar,addtime,modtime) VALUES (2,'numsplit','cid','uint','0','类目ID',"cid int(10) unsigned not null default 0", NOW(), NOW());
INSERT INTO test_table_column (colseqn,tabname,colname,coltype,dfltval,coldesc,sqlchar,addtime,modtime) VALUES (3,'numsplit','num1','uint','0','整数',"num1 int(10) unsigned not null default 0", NOW(), NOW());
INSERT INTO test_table_column (colseqn,tabname,colname,coltype,dfltval,coldesc,sqlchar,addtime,modtime) VALUES (4,'numsplit','num2','float','0.00','浮点数',"num2 decimal(20,14) not null default 0.00", NOW(), NOW());
INSERT INTO test_table_column (colseqn,tabname,colname,coltype,dfltval,coldesc,sqlchar,addtime,modtime) VALUES (5,'numsplit','char1','char','','字符串',"char1 varchar(32) not null default ''", NOW(), NOW());
INSERT INTO test_table_column (colseqn,tabname,colname,coltype,dfltval,coldesc,sqlchar,addtime,modtime,isextra) VALUES (100,'numsplit','autokid','uint','0','自增列',"autokid int(10) unsigned not null auto_increment", NOW(), NOW(), 1);

DROP TABLE IF EXISTS test_table_index;
CREATE TABLE test_table_index (
	autokid int(10) unsigned not null auto_increment,
	idxseqn smallint(5) unsigned not null default 0,
	idxtype varchar(32) not null default '',
	tabname varchar(64) not null default '',
	idxname varchar(64) not null default '',
	excepts varchar(100) not null default '',
	idxchar varchar(1000) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	PRIMARY KEY pk_index_id (autokid),
	UNIQUE KEY uk_index_name (tabname,idxname,excepts),
	KEY idx_index_order (tabname,idxseqn)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;
INSERT INTO test_table_index (idxseqn,idxtype,tabname,idxname,idxchar,addtime,modtime) VALUES (1, '', 'numsplit','idx_split_cid','cid', NOW(), NOW());
INSERT INTO test_table_index (idxseqn,idxtype,tabname,idxname,idxchar,addtime,modtime) VALUES (2, 'PRIMARY', 'numsplit','pk_split_id','autokid', NOW(), NOW());

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

-- 准入权限表
DROP TABLE IF EXISTS test_auth_list;
CREATE TABLE IF NOT EXISTS test_auth_list (
	autokid int(10) unsigned not null auto_increment,
	appname varchar(64) not null default '',
	actname varchar(64) not null default '',
	ipaddr varchar(20) not null default '',
	authvalue int(10) unsigned not null default 0,
	addtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	modtime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	remark varchar(100) not null default '',
	PRIMARY KEY pk_user_id (autokid),
	UNIQUE KEY uk_auth_token (appname, actname)
)ENGINE = MyISAM DEFAULT CHARSET=UTF8;
