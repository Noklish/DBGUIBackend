drop database if exists AnchorManAgement;
create database AnchorManAgement;
use AnchorManAgement;


drop table if exists accounts;
create table accounts (userID int not null auto_increment, userName varchar(50), email varchar(50), pass varchar(50), typeFlag int, primary key(userID));
#Dummy anchors
insert into accounts (userName, email, pass, typeFlag) values("Ron Burgundy", "Ron@mail.com", "password", 1);
insert into accounts (userName, email, pass, typeFlag) values("Alex Jones", "chemtrails@infowars.com", "frogs", 1);
insert into accounts (userName, email, pass, typeFlag) values("Anchor Man", "ancr@anchormanagement.com", "anchor", 1);
insert into accounts (userName, email, pass, typeFlag) values("Brian Fantana", "MyBiggestFan@Fantana.com", "best", 1);
insert into accounts (userName, email, pass, typeFlag) values("Wes Mantoon", "WesMan@Tooth.com", "better", 1);
insert into accounts (userName, email, pass, typeFlag) values("Brick Tamland", "loveLamp@news.com", "moths", 1);
insert into accounts (userName, email, pass, typeFlag) values("Veronica Corningstone", "VCorn@outlook.com", "blonde", 1);

#Dummy mgrs
insert into accounts (userName, email, pass, typeFlag) values("Manager Man", "mgmt@anchormanagement.com", "manager", 0);
insert into accounts (userName, email, pass, typeFlag) values("Mark Fontenot", "mfonten@lyle.smu.edu", "winston", 0);
insert into accounts (userName, email, pass, typeFlag) values("Richard Johnson", "dickJohnson@gmail.com", "default", 0);

drop table if exists anchorDetails;
create table anchorDetails (userID int, points int, managerID int);
insert into anchorDetails values (1, 104, 8);
insert into anchorDetails values (2, 0, 8);
insert into anchorDetails values (3, 56, 9);
insert into anchorDetails values (4, 42, 9);
insert into anchorDetails values (5, 105, 9);
insert into anchorDetails values (6, 2, 10);
insert into anchorDetails values (7, 78, 10);

drop table if exists stories;
create table 
stories (storyID int not null auto_increment, storyTopic varchar (100), storyDate date, startTime time, endTime time, anchorID int, description varchar (200), points int, primary key(storyID));
insert into stories (storyTopic, storyDate, startTime, endTime, anchorID, points, description) values ("Thanksgiving", '2018-11-22', '20:00:00', '23:00:00', 1, 3,"Turkeys!");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("Christmas", '2018-12-25', '09:10:20','11:30:00', 5,"Presents!");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("Christmas Evening", '2018-12-25', '14:10:20','17:20:00',6,"Santa!");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("New Years Eve", '2018-12-31', '23:00:00','23:50:00',10,"nye");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("Little Timmy's Birthday", '2020-02-29', '10:00:00','12:00:00',14,"hbd");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("Thanksgiving Food Drive", '2018-11-21', '01:00:00','03:30:00',10,"At NTFB");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("Winter Games", '2018-12-10', '3:00:00','6:30:00',5,"Fun at the park");
insert into stories (storyTopic, storyDate, startTime, endTime, points, description) values ("State of the Union", '2019-01-16', '12:00:00','4:00:00',20,"Good luck");


drop table if exists equipment;
create table equipment (equipID int not null auto_increment, equipName varchar(50), equipType varchar(50),  primary key (equipID));
insert into equipment (equipName, equipType) values ("Boom Mic 1", "Boom Mic");
insert into equipment (equipName, equipType) values ("Camera 1", "Mounted Camera");
insert into equipment (equipName, equipType) values ("Camera 2", "Handheld Camera");
insert into equipment (equipName, equipType) values ("Tripod 1", "Medium Tripod");
insert into equipment (equipName, equipType) values ("Tripod 2", "Large Tripod");
insert into equipment (equipName, equipType) values ("Boom Mic 2", "Boom Mic");
insert into equipment (equipName, equipType) values ("Mic 1", "Handheld Mic");
insert into equipment (equipName, equipType) values ("Mic 2", "Handheld Mic");
insert into equipment (equipName, equipType) values ("Mic 3", "Headset Mic");
insert into equipment (equipName, equipType) values ("Applejack", "My Little Pony Doll");

drop table if exists vehicles;
create table vehicles (vehicleID int not null auto_increment, vehicleName varchar(50), vehicleType varchar(50),color varchar(10), model varchar(50), capacity int, primary key (vehicleID));
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Big Red Ford", "Van", "Red", "Ford", 7);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Green Chevy", "Car", "Green", "Chevrolet", 5);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Richard's Van", "Van", "White", "Toyota", 8);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Silver Fox", "Van", "Silver", "Honda", 5);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Blue Steel", "Van", "Blue", "Kia", 6);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("White Thunder", "Car", "White", "Bugatti", 2);
insert into vehicles (vehicleName, vehicleType, color, model, capacity) values ("Big Mike", "SUV", "Gold", "Toyota", 4);


drop table if exists vehicleReservations;
create table vehicleReservations (vehicleID int, storyID int);

drop table if exists equipReservations;
create table equipReservations (equipID int, storyID int);

drop table if exists experts;
create table experts (expertID int not null auto_increment, expertName varchar(50), expertTopic varchar(50), primary key(expertID));
insert into experts (expertName, expertTopic) values ("Barron Trump", "Cyber");
insert into experts (expertName, expertTopic) values ("Michael Watts", "Uncomfortable Hugs");
insert into experts (expertName, expertTopic) values ("Sunjoli Aggarwal", "Github");
insert into experts (expertName, expertTopic) values ("Gordon Ramsey", "THE LAMB SAUCE");
insert into experts (expertName, expertTopic) values ("Josh Peck", "Weather");
insert into experts (expertName, expertTopic) values ("Derrick", "Databases");
insert into experts (expertName, expertTopic) values ("John Smith", "Financial");

drop table if exists expertReservations;
create table expertReservations (expertID int, storyID int);