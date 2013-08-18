BEGIN;
CREATE TABLE statustypes ( id int PRIMARY KEY, status varchar NOT NULL );
CREATE TABLE people ( id int NOT NULL PRIMARY KEY, status int references statustypes(id) );
CREATE TABLE part97 ( callsign varchar(10) primary key, name varchar(200) );
CREATE TABLE datatypes ( typeid int PRIMARY KEY, name varchar(12) unique, label varchar(32), exact boolean default false );
INSERT INTO datatypes VALUES ( 0, 'status', 'Status', 'f' );
CREATE TABLE defaults ( callsign char(12) primary key, defsearch varchar(12) references datatypes(name), defupdate varchar(12) );
CREATE TABLE persondata ( personid int references people(id) NOT NULL, datatype int references datatypes(typeid) NOT NULL, value varchar(512) );
CREATE TABLE quickmesg ( text varchar(255) );
CREATE TABLE updatesequence ( personid int references people(id) NOT NULL, timestamp int NOT NULL, source varchar(25) NOT NULL, datatype int REFERENCES datatypes(typeid), value varchar(255) NOT NULL );
CREATE TABLE sessions ( sessionid character varying(32) NOT NULL, callsign character varying(25) NOT NULL, "timestamp" bigint, symbol integer DEFAULT 0 NOT NULL, tactical character varying(64));
CREATE TABLE messages ( timestamp int NOT NULL, callsign varchar(25) NOT NULL, message varchar(255) NOT NULL, dest varchar(25) NOT NULL );
CREATE TABLE pidmap ( pid int, callsign varchar(12) );
INSERT INTO people VALUES ( 0 );
CREATE TABLE async ( jobid serial, filename varchar(64) not null, callsign varchar(8) not null, searchtype int REFERENCES datatypes(typeid), updatetype int, data varchar(255) not null, state int DEFAULT 0, progress int DEFAULT 0, timestamp int NOT NULL, status int NULL );
-- setval('async_jobid_seq', 1);
INSERT INTO messages VALUES ( extract(epoch from now() )::integer, 'SysOp', 'Database Setup Complete', 'all' );
COMMIT;
