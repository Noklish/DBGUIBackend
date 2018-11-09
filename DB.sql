drop database if exists AnchorManAgement;
create database AnchorManAgement;
use AnchorManAgement;


drop table if exists accounts;
create table accounts (userID int not null auto_increment, userName varchar(50), email varchar(50), pass varchar(50), typeFlag bit, primary key(userID));
insert into accounts values("Ron Burgundy", "Ron@mail.com", "password", 1);
insert into accounts values("Alex Jones", "chemtrails@infowars.com", "frogs", 1);
insert into accounts values("Manager Man", "mgmt@anchormanagement.com", "manager", 0);

drop table if exists anchorDetails;
create table anchorDetails (userID int, points int, managerID int);
update anchorDetails set managerID = 3 WHERE userID = 1;
update anchorDetails set managerID = 3 WHERE userID = 0;


drop table if exists stories;
create table 
stories (storyID int not null auto_increment, storyTopic varchar (100), storyDate date, startTime time, endTime time, anchorID int, description varchar (200), points int, primary key(storyID));
insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, description) values ( "Thanksgiving", '2018-11-21', '20:00:00', '23:00:00', 3,"Turkeys!");
insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, description) values ( "Christmas", '2018-12-25', '09:10:20','11:30:00', 5,"Presents!");
insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, description) values ( "Christmas Evening", '2018-12-25', '14:10:20','17:20:00',6,"Santa!");
insert into stories (storyTopic, storyDate, startTime, endTime, description) values ( "New Years Eve", '2018-12-31', '23:00:00','23:50:00',10,"nye");



drop table if exists equipment;
create table equipment (equipID int not null auto_increment, equipName varchar(50), equipType varchar(50),  primary key (equipID));
insert into equipements (equipName, equipType) values ("Boom Mic 1", "microphone");
insert into equipements (equipName, equipType) values ("Camera 2", "Handheld Camera");
insert into equipements (equipName, equipType) values ("Tripod 2", "Large Tripod");


drop table if exists vehicles;
create table vehicles (vehicleID int not null auto_increment, vehicleName varchar(50), vehicleType varchar(50),color varchar(10), model varchar(50), capacity int, primary key (vehicleID));
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Big Red Ford", "Van", "Red", "Ford", 7);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Green Chevy", "Car", "Green", "Chevrolet", 5);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Big White Van", "Van", "White", "Toyota", 8);

drop table if exists vehicleReservations;
create table vehicleReservations (vehicleID int, storyID int);
insert into vehicleReservations values (1, 2);

drop table if exists equipReservations;
create table equipReservations (equipID int, storyID int);
insert into equipReservations values(1,1);
insert into equipReservations values(1,2);

drop table if exists experts;
create table experts (expertID int not null auto_increment, expertName varchar(50), expertTopic varchar(50), primary key(expertID));
insert into experts (expertName, expertTopic) values ("Barron Trump", "Cyber");
insert into experts (expertName, expertTopic) values ("Michael Watts", "Uncomfortable Hugs");
insert into experts (expertName, expertTopic) values ("Sunjoli Aggarwal", "Github");

drop table if exists expertReservations;
create table expertReservations (expertID int, storyID int);
insert into expertReservations values (1,2);
insert into expertReservations values (1,3);
insert into expertReservations values (3,1);