BEGIN;
INSERT INTO datatypes VALUES ( 2, 'bibnum', 'Bib Number', true, false);
INSERT INTO datatypes VALUES ( 3, 'firstname', 'First Name', false, false);
INSERT INTO datatypes VALUES ( 4, 'lastname', 'Last Name', false, false);

INSERT INTO datatypes VALUES ( 5, 'sex', 'Sex', true, true);
INSERT INTO enumtypes VALUES ( 1, 5, 'Male');
INSERT INTO enumtypes VALUES ( 2, 5, 'Female');

INSERT INTO datatypes VALUES ( 6, 'race', 'Race', true, true);
INSERT INTO enumtypes VALUES ( 1, 6, 'Marathon');
INSERT INTO enumtypes VALUES ( 2, 6, '10 Mile');

INSERT INTO enumtypes VALUES ( 0, 0, 'Unknown');
INSERT INTO enumtypes VALUES ( 1, 0, 'Running');
INSERT INTO enumtypes VALUES ( 2, 0, 'Waiting for SAG');
INSERT INTO enumtypes VALUES ( 3, 0, 'In Med Tent');
INSERT INTO enumtypes VALUES ( 4, 0, 'Left Med Tent');
INSERT INTO enumtypes VALUES ( 5, 0, '🚑 EMS Transport');
INSERT INTO enumtypes VALUES ( 6, 0, 'Dropped Out');

INSERT INTO enumtypes VALUES ( 10, 0, '🚍 On SAG Bus 1');
INSERT INTO enumtypes VALUES ( 11, 0, '🚍 On SAG Bus 2');
INSERT INTO enumtypes VALUES ( 12, 0, '🚍 On SAG Bus 3');
INSERT INTO enumtypes VALUES ( 13, 0, '🚍 On SAG Bus 4');
INSERT INTO enumtypes VALUES ( 14, 0, '🚍 On SAG Bus 5');

INSERT INTO enumtypes VALUES ( 15, 0, 'SAG Drop-off');

INSERT INTO defaults VALUES ( 'none', 'bibnum', 'status' );

INSERT INTO quickmesg VALUES ( 'Runner crossed finish line' );
INSERT INTO quickmesg VALUES ( 'Dropped out' );
INSERT INTO quickmesg VALUES ( 'Waiting for SAG' );
INSERT INTO quickmesg VALUES ( 'SAG dropoff' );
INSERT INTO quickmesg VALUES ( '🚑 Transported to Regions' );
INSERT INTO quickmesg VALUES ( '🚑 Transported to United' );
INSERT INTO quickmesg VALUES ( '🚑 Transported to Abbott Northwestern' );
INSERT INTO quickmesg VALUES ( '🚑 Transported to UofM' );
INSERT INTO quickmesg VALUES ( '🚑 Transported to Southdale' );
INSERT INTO quickmesg VALUES ( '🚑 Transported to North Memorial' );

CREATE TABLE latchtypes ( id int not null primary key, label varchar not null );
INSERT INTO latchtypes VALUES ( 1, '🏁 Crossed finish line' );
INSERT INTO latchtypes VALUES ( 2, '⚕️ Med Tent patient' );

CREATE TABLE latchlog ( personid int not null, latchid int not null, primary key (personid, latchid), constraint latchtype_fk foreign key (latchid) references latchtypes(id), constraint personid_fk foreign key (personid) references people(id) );

COMMIT;
