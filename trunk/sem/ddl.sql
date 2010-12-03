
CREATE TABLE web_session (
	sesskey char(32) not null default '',
	actime int(10) unsigned not null default 0,
	ipaddr int(10) unsigned not null default 0,
	sessval varchar(512) binary,
	UNIQUE KEY uk_sess_key (sesskey)
) ENGINE = MEMORY DEFAULT CHARSET=UTF8;

CREATE TABLE useracct (
	userid int(10) unsigned not null auto_increment PRIMARY KEY,
	usertype smallint(5) unsigned not null default 0,
	userstat smallint(5) unsigned not null default 0,
	password char(32) not null default '',
	username varchar(64) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	email varchar(200) not null default '',
	UNIQUE KEY uk_user_name (username)
) ENGINE = InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE user_permission (
	autokid int(10) unsigned not null auto_increment PRIMARY KEY,
	userid int(10) unsigned not null default 0,
	pm_stat smallint(5) unsigned not null default 0,
	pm_type smallint(5) unsigned not null default 0,
	pm_func char(10) not null default '',
	se_name char(10) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	begdate date not null default '0000-00-00',
	enddate date not null default '0000-00-00',
	se_user varchar(128) not null default '',
	KEY idx_perm_token (se_user, se_name)
) ENGINE = InnoDB DEFAULT CHARSET=UTF8;

INSERT INTO useracct (username) VALUES ('zhangxc83@sohu.com');
INSERT INTO user_permission (pm_func, perm_se, begdate, enddate, se_name) VALUES('base', 'baidu', '2010-01-01', '2010-12-31', '百度登录名');

-- 查询权限, 同时传入apitoken和se_name，避免泄露apitoken,甚至可以考虑绑定机器名
SELECT pm_stat,begdate,enddate FROM useracct LEFT JOIN user_permission ON a.userid = b.userid WHERE apitoken = ? AND perm_se = 'baidu' AND se_name = ?

-- 客户端与账户绑定表
CREATE TABLE agent_machine (
	agentid int(10) unsigned not null auto_increment PRIMARY KEY,
	userid int(10) unsigned not null default 0,
	aclstat smallint(5) unsigned not null default 0,
	se_name char(10) not null default '',
	machine varchar(64) not null default '',
	nodename varchar(100) not null default '',
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	KEY idx_machine_uid (userid, machine, se_name)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- 客户端在线情况
-- 可根据prov_name 来解决分地域排名问题
CREATE TABLE agent_session (
	sessid char(32) not null default '' PRIMARY KEY,
	userid int(10) unsigned not null default 0,
	heartbeat int(10) unsigned not null default 0,
	addtime datetime not null default '0000-00-00 00:00:00',
	prov_name varchar(10) not null default '',
	city_name varchar(10) not null default '',
	ipaddr varchar(15) not null default '',
	machine varchar(64) not null default '',
	nodename varchar(100) not null default '',
	KEY idx_agentsess_beat (heartbeat),
	KEY idx_agentsess_prov (prov_name)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;


---- 以下为各搜索引擎使用的表
-- Q值
CREATE TABLE baidu_word_q (
	keywid int(10) not null default 0,
	qold smallint(5) unsigned not null default 0,
	qvalue smallint(5) unsigned not null default 0,
	modtime datetime not null default '0000-00-00 00:00:00',
	UNIQUE KEY uk_q_word (keywid)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

-- 实时排名
CREATE TABLE baidu_word_rank (
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

CREATE TABLE soft_download (
	autokid int(10) unsigned not null auto_increment PRIMARY KEY,
	downcnt int(10) unsigned not null default 0,
	addtime datetime not null default '0000-00-00 00:00:00',
	modtime datetime not null default '0000-00-00 00:00:00',
	ipaddr varchar(15) not null default '',
	uagent varchar(100) not null default '',
	UNIQUE KEY uk_download_ip (ipaddr),
	KEY idx_download_time (modtime)
) ENGINE = MyISAM DEFAULT CHARSET=UTF8;

