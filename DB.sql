drop database if exists AnchorManAgement;
create database AnchorManAgement;
use AnchorManAgement;


drop table if exists accounts;
create table accounts (userID int not null auto_increment, userName varchar(50), email varchar(50), pass varchar(50), typeFlag int, primary key(userID));
#Dummy anchors
insert into accounts (userName, email, pass, typeFlag) values("Ron Burgundy", "Ron@mail.com", "password", 1);
insert into accounts (userName, email, pass, typeFlag) values("Brian Fantana", "MyBiggestFan@Fantana.com", "best", 1);

#Dummy mgrs
insert into accounts (userName, email, pass, typeFlag) values("Manager", "mgmt@anchormanagement.com", "manager", 0);

drop table if exists anchorDetails;
create table anchorDetails (userID int, points int, managerID int);
insert into anchorDetails values (1, 104, 3);
insert into anchorDetails values (2, 0, 3);

drop table if exists stories;
create table 
stories (storyID int not null auto_increment, storyTopic varchar (100), storyDate date, startTime time, endTime time, anchorID int, description varchar (200), points int, primary key(storyID));
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("Christmas", '2018-12-25', '09:10:20','11:30:00', 5,"Wish audience a merry Christmas and give info about local events");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("New Years Eve", '2018-12-31', '23:00:00','23:50:00',10,"Coverage of the New York Ball Drop");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("State of the Union", '2019-01-16', '12:00:00','4:00:00',20,"Good luck");

drop table if exists equipment;
create table equipment (equipID int not null auto_increment, equipName varchar(50), equipType varchar(50),  primary key (equipID));
insert into equipment (equipName, equipType) values ("Boom Mic 1", "Boom Mic");
insert into equipment (equipName, equipType) values ("Camera 1", "Mounted Camera");
insert into equipment (equipName, equipType) values ("Camera 2", "Handheld Camera");

drop table if exists vehicles;
create table vehicles (vehicleID int not null auto_increment, vehicleName varchar(50), vehicleType varchar(50),color varchar(10), model varchar(50), capacity int, primary key (vehicleID));
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Big Red Ford", "Van", "Red", "Ford", 7);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Green Chevy", "Car", "Green", "Chevrolet", 5);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Richard's Van", "Van", "White", "Toyota", 8);


drop table if exists vehicleReservations;
create table vehicleReservations (vehicleID int, storyID int, foreign key(storyID) references stories(storyID) ON DELETE CASCADE);

drop table if exists equipReservations;
create table equipReservations (equipID int, storyID int, foreign key(storyID) references stories(storyID) ON DELETE CASCADE);

drop table if exists experts;
create table experts (expertID int not null auto_increment, expertName varchar(50), expertTopic varchar(50), primary key(expertID));
insert into experts (expertName, expertTopic) values ("Josh Peck", "Weather");
insert into experts (expertName, expertTopic) values ("Derrick", "Databases");
insert into experts (expertName, expertTopic) values ("John Smith", "Financial");

drop table if exists expertReservations;
create table expertReservations (expertID int, storyID int, foreign key(storyID) references stories(storyID) ON DELETE CASCADE);