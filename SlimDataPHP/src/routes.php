<?php
use Slim\Http\Request;
use Slim\Http\Response;
// Routes

$app->get('/',function ($request, $response, $args){
	return "Welcome to Anchor Management! This is the homepage, and there's nothing here!";
});

$app->group('/accounts', function () use ($app) {
	$app->get('/[{userID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT userId, userName, email FROM accounts WHERE userID=:userID");
		$sth->bindParam("userID", $args['userID']);
		$sth->execute();
		$accounts = $sth->fetchAll();
		return $this->response->withJson($accounts);
	});

	$app->post('/newAccount', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO accounts (userName, email, pass, typeFlag) VALUES (:userName, :email, :pass, :typeFlag)";
		$sth = $this->db->prepare($sql);
		
		//ensure username and email are not already in use
		$userNameSelected = $input['userName'];
		$emailSelected = $input['email'];
		$result = $this->db->prepare("SELECT userName FROM accounts WHERE userName = '$userNameSelected'");
		$result->execute();
		if($result->rowCount() != 0)
		{
			echo "Username already taken, please select another.";
			return;
		}
		$result = $this->db->prepare("SELECT email FROM accounts WHERE email = '$emailSelected'");
		$result->execute();
		if($result->rowCount() != 0)
		{
			echo "Email already in use, please select another or login to your existing account.";
			return;
		}
		$sth->bindParam("userName", $userNameSelected);
		$sth->bindParam("email", $emailSelected);
		$sth->bindParam("pass", $input['pass']);
		$sth->bindParam("typeFlag",$input['typeFlag']);
		$sth->execute();
		
		//If new account is an anchor
		if($input['typeFlag'] != 0)
		{
			$uID = $this->db->lastInsertID();
			$anchorInsert = "INSERT INTO anchorDetails (userID, points) VALUES (:userID, :points)";
			$sth2 = $this->db->prepare($anchorInsert);
			$pts = 0;
			$sth2->bindParam("userID",$uID);
			$sth2->bindParam("points",$pts);
			$sth2->execute();
		}
		return $this->response->withJson($input);
	});

	$app->put('/updatePoints/[{userID}]', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "UPDATE anchorDetails SET points= points + :points WHERE userID=:userID";
		$sth = $this->db->prepare($sql);
		
		$sth->bindParam("userID", $input['userID']);
		$sth->bindParam("points", $input['points']);
		$sth->execute();
		return $this->response->withJson($input);
	});

	$app->put('/changePassword/[{userID}]', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "update accounts set pass=:pass where userID=:userID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("userID", $input['userID']);
		$sth->bindParam("pass", $input['pass']);
		$sth->execute();
		return $this->response->withJson($input);
	});

	$app->put('/changeUserInfo/[{userID}]', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "update accounts set userName=:userName, email=:email where userID=:userID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("userID", $input['userID']);
		$sth->bindParam("userName", $input['userName']);
		$sth->bindParam("email", $input['email']);
		$sth->execute();
		return $this->response->withJson($input);
	});
});

$app->group('/stories', function () use ($app) {
	$app->get('/story/[{storyID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT storyTopic, a.userName FROM stories s JOIN accounts a ON s.anchorID = a.userID WHERE storyID = :storyID");
		$sth->bindParam("storyID", $args['storyID']);
		$sth->execute();
		$accounts = $sth->fetchAll();
		return $this->response->withJson($accounts);
	});

	$app->get('/upcoming', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT s.*, a.userName FROM stories s JOIN accounts a ON s.anchorID = a.userID WHERE storyDate >= CURDATE() ORDER BY storyDate, startTime");
		$sth->execute();
		$stories = $sth->fetchAll();
		return $this->response->withJson($stories);
	});

	$app->get('/specificStory/[{storyDate}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM stories WHERE storyDate = :storyDate ORDER BY startTime");
		$sth->bindParam("storyDate", $args['storyDate']);
		$sth->execute();
		$stories = $sth->fetchAll();
		return $this->response->withJson($stories);
	});

	$app->post('/createNew', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO stories (storyTopic, storyDate, startTime, endTime, anchorID, description) VALUES (:storyTopic, :storyDate, :startTime, :endTime, :anchorID, :description)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyTopic", $input['storyTopic']);
		$sth->bindParam("storyDate", $input['storyDate']);
		$sth->bindParam("startTime", $input['startTime']);
		$sth->bindParam("endTime", $input['endTime']);
		$sth->bindParam("anchorID", $input['anchorID']);
		$sth->bindParam("description", $input['description']);
		$sth->execute();
		return $this->response->withJson($input);
	});


	$app->put('/coverEvent', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "UPDATE stories set storyTopic=:storyTopic, storyDate=:storyDate, startTime=:startTime, endTime=:endTime, description=:description where storyID=:storyID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyTopic", $input['storyTopic']);
		$sth->bindParam("storyDate", $input['storyDate']);
		$sth->bindParam("startTime", $input['startTime']);
		$sth->bindParam("endTime", $input['endTime']);
		$sth->bindParam("storyID", $input['storyID']);
		$sth->bindParam("description", $input['description']);
		$sth->execute();
		return $this->response->withJson($input);
	});

	$app->put('/assignAnchor', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "UPDATE stories SET anchorID=:anchorID WHERE storyID=:storyID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("anchorID",$input['anchorID']);
		$sth->bindParam("storyID",$input['storyID']);
		$sth->execute();
		return $this->response->withJson($input);
	});

	$app->delete('/deleteEvent/[{storyID}]', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM stories WHERE storyID = :storyID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $input['storyID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
});

$app->group('/equipment', function () use ($app) {
	$app->get('/filter/[{type}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM equipment WHERE equipType = :type ORDER BY equipName");
		$sth->bindParam("type", $args['type']);
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});
	
	$app->get('/search/[{conditions}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM equipment WHERE equipID = :conditions or equipName = :conditions or equipType = :conditions");
		$sth->bindParam("conditions", $args['conditions']);
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});
	
	$app->get('/available/[{storyDate}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT e.equipName, e.equipType FROM equipment e LEFT OUTER JOIN equipReservations er on e.equipID = er.equipID 
		LEFT OUTER JOIN stories s on er.storyID = s.storyID WHERE s.storyID IS NULL AND e.equipID
		NOT IN (SELECT er.equipID FROM equipReservations er JOIN stories st on er.storyID = st.storyID WHERE st.storyDate = :storyDate AND st.startTime >= :startTime AND st.endTime <= :endTime)");
		$sth->bindParam("storyDate",$args['storyDate']);
		$sth->bindParam("startTime", $args['startTime']);
		$sth->bindParam("endTime", $args['endTime']);
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});
	
	$app->post('/reserve', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "INSERT INTO equipReservations(equipID, storyID) values (:equipID, :storyID)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID",$input['storyID']);
		$sth->bindParam("equipID",$input['equipID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->post('/add', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO equipment (equipName, equipType) VALUES (:equipName, :equipType)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("equipName", $input['equipName']);
		$sth->bindParam("equipType", $input['equipType']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->delete('/deleteReservation', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM equipReservations WHERE storyID = :storyID or equipID = :equipID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $input['storyID']);
		$sth->bindParam("equipID", $input['equipID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
});
	
$app->group('/vehicles', function () use ($app) {
	$app->get('/filter/[{conditions}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM vehicles WHERE vehicleType = :conditions OR color = :conditions OR capacity = :conditions ORDER BY vehicleName");
		$sth->bindParam("conditions", $args['conditions']);
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);
	});
	
	$app->get('/search/[{conditions}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM vehicles WHERE vehicleID = :conditions or vehicleName = :conditions or vehicleType = :conditions");
		$sth->bindParam("conditions", $args['conditions']);
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);	
	});

	$app->get('/available/[{storyDate}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT v.vehicleName, v.vehicleType, v.model, v.capacity FROM vehicles v LEFT OUTER JOIN vehicleReservations vr on v.vehicleID = vr.vehicleID LEft Outer JOIN stories s on vr.storyID = s.storyID WHERE s.storyID IS NULL AND v.vehicleID
		NOT IN (SELECT vr.vehicleID FROM vehicleReservations vr JOIN stories st on vr.storyID = st.storyID WHERE st.storyDate = :storyDate AND st.startTime >= :startTime AND st.endTime <= :endTime)");
		$sth->bindParam("storyDate",$args['storyDate']);
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);
	});
	
	$app->post('/reserve', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "INSERT INTO vehicleReservations(vehicleID, storyID) values (:vehicleID, :storyID)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID",$input['storyID']);
		$sth->bindParam("vehicleID",$input['vehicleID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->post('/add', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO vehicles (vehicleName, vehicleType, color, model, capacity, storyID) VALUES (:vehicleName, :vehicleType, :color, :model, :capacity)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("vehicleName", $input['vehicleName']);
		$sth->bindParam("vehicleType", $input['vehicleType']);
		$sth->bindParam("color", $input['color']);
		$sth->bindParam("model", $input['model']);
		$sth->bindParam("capacity", $input['capacity']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->delete('/deleteReservation', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM vehicleReservations WHERE storyID = :storyID or vehicleID = :vehicleID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $input['storyID']);
		$sth->bindParam("vehicleID", $input['vehicleID']);
		$sth->execute();
		return $this->response->withJson($input);	
	});
	
});

$app->group('/experts', function () use ($app) {
	$app->get('/filter/[{conditions}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM experts WHERE expertTopic = :conditions");
		$sth->bindParam("conditions", $args['conditions']);
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);
	});
	
	$app->get('/search/[{conditions}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM vehicles WHERE expertID = :conditions or expertName = :conditions or expertTopic = :conditions");
		$sth->bindParam("conditions", $args['conditions']);
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);	
	});

	$app->get('/available/[{storyDate}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT e.expertName, e.expertTopic FROM experts e LEFT OUTER JOIN expertReservations er on e.expertID = er.expertID LEft Outer JOIN stories s on er.storyID = s.storyID WHERE s.storyID IS NULL AND v.vehicleID
		NOT IN (SELECT er.expertID FROM expertReservations er JOIN stories st on er.storyID = st.storyID WHERE st.storyDate = :storyDate AND st.startTime >= :startTime AND st.endTime <= :endTime)");
		$sth->bindParam("storyDate",$args['storyDate']);
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);
	});
	
	$app->post('/reserve', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "INSERT INTO expertReservations(expertID, storyID) values (:expertID, :storyID)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID",$input['storyID']);
		$sth->bindParam("expertID",$input['expertID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->post('/add', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO experts(expertName, expertTopic) VALUES (:expertName, :expertTopic)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("expertName", $input['expertName']);
		$sth->bindParam("expertTopic", $input['expertTopic']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->delete('/deleteReservation', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM expertReservations WHERE storyID = :storyID AND expertID = :expertID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $input['storyID']);
		$sth->bindParam("expertID", $input['expertID']);
		$sth->execute();
		return $this->response->withJson($input);	
	});
	
});