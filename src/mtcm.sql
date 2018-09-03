BEGIN;
INSERT INTO datatypes VALUES ( 2, 'bibnum', 'Bib Number', false, false);
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
INSERT INTO enumtypes VALUES ( 2, 0, 'Crossed Finish Line');
INSERT INTO enumtypes VALUES ( 3, 0, 'In Med Tent');
INSERT INTO enumtypes VALUES ( 4, 0, 'Left Med Tent');
INSERT INTO enumtypes VALUES ( 5, 0, 'EMS Transport');
INSERT INTO enumtypes VALUES ( 6, 0, 'Dropped Out');

INSERT INTO defaults VALUES ( 'none', 'bibnum', 'status' );
COMMIT;
