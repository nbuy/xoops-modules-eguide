# Event Guide Module for XOOPS
# 

#
# Table structure for table `eguide`
#

CREATE TABLE eguide (
  eid     INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  uid     INT(5)          NOT NULL DEFAULT '0',
  title   VARCHAR(255)             DEFAULT NULL,
  cdate   INT(10)         NOT NULL DEFAULT '0',
  edate   INT(10)         NOT NULL DEFAULT '0',
  ldate   INT(10)         NOT NULL DEFAULT '0',
  mdate   INT(10)         NOT NULL DEFAULT '0',
  expire  INT(10)         NOT NULL DEFAULT '0',
  style   TINYINT(1)      NOT NULL DEFAULT '0',
  status  TINYINT(1)      NOT NULL DEFAULT '0',
  summary TEXT            NOT NULL,
  body    TEXT            NOT NULL,
  counter INT(8) UNSIGNED NOT NULL DEFAULT '0',
  topicid INT(8) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (eid)
);

#
# Table structure for table `eguide_category`
#

CREATE TABLE eguide_category (
  catid   INTEGER      NOT NULL AUTO_INCREMENT,
  catname VARCHAR(40)  NOT NULL,
  catimg  VARCHAR(255) NOT NULL DEFAULT '',
  catdesc TEXT,
  catpri  INTEGER      NOT NULL DEFAULT '0',
  weight  INTEGER      NOT NULL DEFAULT '0',
  PRIMARY KEY (catid)
);

# --------------------------------------------------------

INSERT INTO eguide_category (catid, catname, catdesc) VALUES (1, '', 'Default category (you can edit this)');
# -- Default Category (Noname)

#
# Table structure for table `eguide_extent`
#

CREATE TABLE eguide_extent (
  exid      INTEGER         NOT NULL AUTO_INCREMENT,
  eidref    INTEGER         NOT NULL,
  exdate    INTEGER         NOT NULL,
  expersons INTEGER,
  reserved  INT(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (exid)
);

#
# Table structure for table `eguide_opt`
#

CREATE TABLE eguide_opt (
  eid         INT(8)          NOT NULL,
  reservation TINYINT(1),
  strict      TINYINT(1),
  autoaccept  TINYINT(1),
  notify      TINYINT(1),
  persons     INT(8) UNSIGNED NOT NULL DEFAULT '0',
  reserved    INT(8) UNSIGNED NOT NULL DEFAULT '0',
  closetime   INTEGER         NOT NULL DEFAULT '0',
  optfield    TEXT,
  optvars     TEXT,
  PRIMARY KEY (eid)
);

#
# Table structure for table `eguide_reserv`
#

CREATE TABLE eguide_reserv (
  rvid     INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  eid      INT(8)          NOT NULL,
  exid     INT(8)          NOT NULL,
  uid      INT(8),
  operator INT(8),
  rdate    INTEGER,
  email    VARCHAR(60),
  info     TEXT,
  status   TINYINT(1),
  confirm  VARCHAR(8),
  PRIMARY KEY (rvid)
);
