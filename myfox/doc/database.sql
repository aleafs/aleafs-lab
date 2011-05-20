
-- 路由表
DROP TABLE IF EXISTS route_info;
CREATE TABLE IF NOT EXISTS route_info (
	autokid int(10) unsigned not null auto_increment,
	thedate int(10) unsigned not null default 0,
	idxsign int(10) unsigned not null default 0,
	useflag tinyint(2) unsigned not null default 0,
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	tbname varchar(64) not null default '',
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
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;
