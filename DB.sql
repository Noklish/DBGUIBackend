create database AnchorManAgement;
use AnchorManAgement;


drop table if exists accounts;
create table accounts (userID int, userName varchar(50), email varchar(50), pass varchar(50), typeFlag bit);
#insert into accounts values(1, "Name", "email@somewhere.com", "password", 1);

drop table if exists anchorDetails;
create table anchorDetails (userID int, points int, managerID int);
#insert into anchorDetails values (1, 120, 2);


drop table if exists stories;
create table 
stories (storyID int not null auto_increment, storyTopic varchar (100), storyDate date, startTime time, endTime time, anchorID int, description varchar (200), points int, primary key(storyID));
#insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, description) values ( "I don't know.", '2018-11-21', '23:40:20', '23:50:20', 3,"Please see here!");
#insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, description) values ( "ok.", '2018-10-24', '09:10:20','11:30:00', 5,"Please !");
#insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, description) values ( "0.0", '2018-10-24', '08:10:20','10:20:00',6,"here!");
#select * from stories;



drop table if exists equipements;
create table equipements (equipID int not null auto_increment, equipName varchar(50), equipType varchar(50),  primary key (equipID));
#insert into equipements (equipName, equipType) values ("Mic", "microphone");
#insert into equipements (equipName, equipType) values ("Mic", "microphone");


drop table if exists vehicles;
create table vehicles (vehicleID int not null auto_increment, vehicleName varchar(50), vehicleType varchar(50),color varchar(10), model varchar(50), capacity int, primary key (vehicleID));
#insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Car", "car", "white", "model", 4);
#insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Car", "car", "black", "model", 5);
#insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Car", "car", "yellow", "model", 3);


drop table if exists vehicleReservations;
create table vehicleReservations (vehicleID int, storyID int);
#select * from vehicleReservations;

drop table if exists equipReservations;
create table equipReservations (equipID int, storyID int);
#insert into equipReservations values(1,5);
#insert into equipReservations values(1,6);
#select * from equipReservations;

drop table if exists experts;
create table experts (expertID int not null auto_increment, expertName varchar(50), expertTopic varchar(50), primary key(expertID));
select * from experts;

drop table if exists expertReservations;
create table expertReservations (expertID int, storyID int);
select * from expertReservations;