-- there are restrictions on num of characters for a valid userid, password and other fields, but it's easier to take care of that on the front end
DROP SCHEMA IF EXISTS is212_spm_data;
CREATE SCHEMA is212_spm_data;

DROP TABLE IF EXISTS student;
CREATE TABLE student
(	userid varchar(300) NOT NULL PRIMARY KEY,
	password varchar(300) NOT NULL,
    name varchar(300) NOT NULL,
    school varchar(300) NOT NULL,
    edollar decimal(10,2) NOT NULL
);

DROP TABLE IF EXISTS course;
CREATE TABLE course
(	course varchar(300) NOT NULL PRIMARY KEY,
	school varchar(300) NOT NULL,
    title varchar(300) NOT NULL,
    description varchar(1000) NOT NULL,
    exam_date date NOT NULL,
    exam_start time NOT NULL,
    exam_end time NOT NULL
);

DROP TABLE IF EXISTS section;
CREATE TABLE section
(	course varchar(300) NOT NULL,
	section varchar(300) NOT NULL,
    day int(5) NOT NULL,
    start time NOT NULL,
    end time NOT NULL,
    instructor varchar(300) NOT NULL,
    venue varchar(300) NOT NULL,
    size int(5) NOT NULL,
    CONSTRAINT section_pk PRIMARY KEY (course, section),
    CONSTRAINT section_fk FOREIGN KEY (course) REFERENCES course(course)
);

DROP TABLE IF EXISTS bid;
CREATE TABLE bid
(	userid varchar(300) NOT NULL,
	amount decimal(10,2) NOT NULL,
    code varchar(300) NOT NULL,
    section varchar(300) NOT NULL,
    status varchar(300) NOT NULL,
    CONSTRAINT bid_pk PRIMARY KEY (userid, code, section),
    CONSTRAINT bid_fk1 FOREIGN KEY(userid) REFERENCES student(userid),
    CONSTRAINT bid_fk2 FOREIGN KEY(code, section) REFERENCES section(course, section)
);

DROP TABLE IF EXISTS course_completed;
CREATE TABLE course_completed
(	userid varchar(300) NOT NULL,
	code varchar(300) NOT NULL,
    CONSTRAINT course_completed_pk PRIMARY KEY (userid, code),
    CONSTRAINT course_completed_fk1 FOREIGN KEY(userid) REFERENCES student(userid),
    CONSTRAINT course_completed_fk2 FOREIGN KEY(code) REFERENCES course(course)
);

DROP TABLE IF EXISTS prerequisite;
CREATE TABLE prerequisite
(	course varchar(300) NOT NULL,
	prerequisite varchar(300) NOT NULL,
    CONSTRAINT prerequisite_pk PRIMARY KEY (course, prerequisite),
    CONSTRAINT prerequisite_fk1 FOREIGN KEY(course) REFERENCES course(course),
    CONSTRAINT prerequisite_fk2 FOREIGN KEY(prerequisite) REFERENCES course(course)
);

DROP TABLE IF EXISTS admin;
CREATE TABLE admin
(	userid varchar(300) NOT NULL PRIMARY KEY,
	password varchar(300) NOT NULL
);

DROP TABLE IF EXISTS round;
CREATE TABLE round
(   round_num varchar(300) NOT NULL,
    status varchar(300) NOT NULL,
);
