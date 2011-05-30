
-- 路由表
DROP TABLE IF EXISTS route_info;
CREATE TABLE IF NOT EXISTS route_info (
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
DROP TABLE IF EXISTS settings;
CREATE TABLE IF NOT EXISTS settings ()
ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- 任务队列表
DROP TABLE IF EXISTS task_queque;
CREATE TABLE IF NOT EXISTS task_queque (
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

-- SELECT ... FROM task_queque WHERE task_flag = ? AND trytimes < ? ORDER BY priority ASC, trytimes ASC, autokid ASC-- SELECT ... FROM task_queque WHERE addtime < ?


