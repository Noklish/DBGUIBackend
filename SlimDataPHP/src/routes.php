<?php
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;
header("Access-Control-Allow-Origin: *");
// Routes

$app->options('/{routes:.+}', function($request, $response, $args){
	return $response;
	});
	
	$app->add(function ($req, $res, $next) {
	$response = $next($req, $res);
	return $response
	->withHeader('Access-Control-Allow-Origin', '*')
	->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
	->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	});

$app->get('/',function ($request, $response, $args){
	return "Welcome to Anchor Management! This is the homepage, and there's nothing here!";
});

$app->post('/login', function ($request, $response) {
	$input = $request->getParsedBody();
	$sth = $this->db->prepare("SELECT * FROM accounts WHERE email = :email AND pass = :pass");
	$sth->bindParam("email", $input['email']);
	$sth->bindParam("pass", $input['pass']);
	$sth->execute();
	$log = $sth->fetchObject();
	if($sth->rowCount() != 0)
	{
		$settings = $this->get('settings');
		$token = JWT::encode(['userID' => $log->userID], $settings['jwt']['secret'], "HS256");
		return $this->response->withJson(array(1,$log,$token));
	}
	else
	{
		return $this->response->withJson(0);
	}
});

$app->post('/newAccount', function ($request, $response) {
	$input = $request->getParsedBody();
	$sql = "INSERT INTO accounts (userName, email, pass, typeFlag) VALUES (:userName, :email, :pass, :typeFlag)";
	$sth = $this->db->prepare($sql);
	
	//ensure username and email are not already in use
	$userNameSelected = $input['userName'];
	$emailSelected = $input['email'];
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
	$uID = $this->db->lastInsertID();
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
	return $this->response->withJson($uID);
});

$app->group('/accounts', function () use ($app) {

	$app->get('/unmanagedAnchors', function(Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT a.userName, a.userID, ad.points FROM accounts a JOIN anchorDetails ad ON a.userID = ad.userID WHERE ad.managerID IS NULL OR ad.managerID = 0");
		$sth->execute();
		$anchors = $sth->fetchAll();
		return $this->response->withJson($anchors);
	});

	$app->get('/[{userID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM accounts WHERE userID=:userID");
		$sth->bindParam("userID", $args['userID']);
		$sth->execute();
		$accounts = $sth->fetchAll();
		return $this->response->withJson($accounts);
	});

	$app->get('/myAnchors/[{userID}]', function(Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT a.*, ad.points FROM accounts a LEFT OUTER JOIN anchorDetails ad on a.userID = ad.userID WHERE ad.managerID = :userID");
		$sth->bindParam("userID", $args['userID']);
		$sth->execute();
		$ancs = $sth->fetchAll();
		return $this->response->withJson($ancs);
	});

	$app->get('/myManager/[{userID}]', function(Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT a.* FROM accounts a JOIN anchorDetails ad ON ad.managerID = a.userID WHERE ad.userID = :userID");
		$sth->bindParam("userID", $args['userID']);
		$sth->execute();
		$mgr = $sth->fetchAll();
		return $this->response->withJson($mgr);
	});

	$app->put('/updatePoints/[{userID}]', function($request, $response, $args){
		$input = $request->getParsedBody();
		$safeOff = $this->db->prepare("SET SQL_SAFE_UPDATES=0");
		//$safeOff->execute();
		$sql = "UPDATE anchorDetails ad JOIN stories s ON s.anchorID = ad.userID SET ad.points = ad.points + s.points WHERE ad.userID = :userID AND s.storyID = :storyID";
		$sth = $this->db->prepare($sql);	
		$sth->bindParam("storyID", $input['storyID']);
		$sth->bindParam("userID", $args['userID']);
		$sth->execute();
		$safeOn = $this->db->prepare("SET SQL_SAFE_UPDATES=1");
		//$safeOn->execute();
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

	$app->put('/assignAnchor', function($request, $response) {
		$input = $request->getParsedBody();
		$sth = $this->db->prepare("UPDATE anchorDetails SET managerID = :managerID WHERE userID = :userID");
		$sth->bindParam("managerID", $input['managerID']);
		$sth->bindParam("userID", $input['userID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
	
	$app->put('/unassignFromStory/[{storyID}]', function($request, $response, $args){
		$input = $request->getParsedBody();
		$sql = "UPDATE stories SET anchorID = NULL WHERE storyID=:storyID";
		$sth = $this->db->prepare($sql);	
		$sth->bindParam("storyID", $args['storyID']);
		$sth->execute();
		return $this->response->withJson($input);
	});

	$app->put('/unassignFromManager/{userID}', function($request, $response, $args){
		$input = $request->getParsedBody();
		$sql = "UPDATE anchorDetails SET managerID = 0 WHERE userID=:userID";
		$sth = $this->db->prepare($sql);	
		$sth->bindParam("userID", $args['userID']);
		$sth->execute();
		return $this->response->withJson($input);
	});
});

$app->group('/stories', function () use ($app) {
	$app->get('/story/[{storyID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT s.*, a.* FROM stories s JOIN accounts a ON s.anchorID = a.userID WHERE storyID = :storyID");
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

	$app->get('/myStories/[{userID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM stories WHERE anchorID = :userID AND storyDate >= CURDATE()");
		$sth->bindParam("userID", $args['userID']);
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

	$app->get('/unclaimed', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM stories WHERE anchorID IS NULL AND storyDate >= CURDATE()");
		$sth->execute();
		$stories = $sth->fetchAll();
		return $this->response->withJson($stories);
	});

	$app->get('/reservedEquipment/[{storyID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT e.* FROM equipment e JOIN equipReservations er on e.equipID = er.equipID WHERE er.storyID = :storyID");
		$sth->bindParam("storyID", $args['storyID']);
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});

	$app->get('/reservedVehicles/[{storyID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT v.* FROM vehicles v JOIN vehicleReservations vr on v.vehicleID = vr.vehicleID WHERE vr.storyID = :storyID");
		$sth->bindParam("storyID", $args['storyID']);
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});

	$app->get('/reservedExperts/[{storyID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT e.* FROM experts e JOIN expertReservations er on e.expertID = er.expertID WHERE er.storyID = :storyID");
		$sth->bindParam("storyID", $args['storyID']);
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});

	$app->get('/availableEquipment/[{storyID}]', function (Request $request, Response $response, array $args) {
		$date = $this->db->prepare("SELECT storyDate FROM stories WHERE storyID = :storyID");
		$date->bindParam("storyID",$args['storyID']);
		$date->execute();
		$selectedDate = $date->fetchColumn();

		$start = $this->db->prepare("SELECT startTime FROM stories WHERE storyID = :storyID");
		$start->bindParam("storyID",$args['storyID']);
		$start->execute();
		$selectedStart = $start->fetchColumn();
	
		$end = $this->db->prepare("SELECT endTime FROM stories WHERE storyID = :storyID");
		$end->bindParam("storyID",$args['storyID']);
		$end->execute();
		$selectedEnd = $end->fetchColumn();

		$sth = $this->db->prepare("SELECT e.* FROM equipment e LEFT OUTER JOIN equipReservations er on e.equipID = er.equipID LEFT OUTER JOIN stories s on er.storyID = s.storyID WHERE e.equipID
		NOT IN (SELECT er.equipID FROM equipReservations er JOIN stories st on er.storyID = st.storyID WHERE st.storyDate = '$selectedDate' AND (st.startTime <= '$selectedStart' AND '$selectedStart' <= st.endTime) OR (st.endTime >= '$selectedEnd' AND '$selectedEnd' >= st.startTime));");
		$sth->execute();
		$equipment = $sth->fetchAll();
		return $this->response->withJson($equipment);
	});

	$app->get('/availableVehicles/[{storyID}]', function (Request $request, Response $response, array $args) {
		$date = $this->db->prepare("SELECT storyDate FROM stories WHERE storyID = :storyID");
		$date->bindParam("storyID",$args['storyID']);
		$date->execute();
		$selectedDate = $date->fetchColumn();

		$start = $this->db->prepare("SELECT startTime FROM stories WHERE storyID = :storyID");
		$start->bindParam("storyID",$args['storyID']);
		$start->execute();
		$selectedStart = $start->fetchColumn();
	
		$end = $this->db->prepare("SELECT endTime FROM stories WHERE storyID = :storyID");
		$end->bindParam("storyID",$args['storyID']);
		$end->execute();
		$selectedEnd = $end->fetchColumn();

		$sth = $this->db->prepare("SELECT v.* FROM vehicles v LEFT OUTER JOIN vehicleReservations vr on v.vehicleID = vr.vehicleID LEft Outer JOIN stories s on vr.storyID = s.storyID WHERE v.vehicleID
		NOT IN (SELECT vr.vehicleID FROM vehicleReservations vr JOIN stories st on vr.storyID = st.storyID WHERE st.storyDate = '$selectedDate' AND (st.startTime <= '$selectedStart' AND '$selectedStart' <= st.endTime) OR (st.endTime >= '$selectedEnd' AND '$selectedEnd' >= st.startTime));");
		$sth->execute();
		$vehicles = $sth->fetchAll();
		return $this->response->withJson($vehicles);
	});

	$app->get('/availableExperts/[{storyID}]', function (Request $request, Response $response, array $args) {
		$date = $this->db->prepare("SELECT storyDate FROM stories WHERE storyID = :storyID");
		$date->bindParam("storyID",$args['storyID']);
		$date->execute();
		$selectedDate = $date->fetchColumn();

		$start = $this->db->prepare("SELECT startTime FROM stories WHERE storyID = :storyID");
		$start->bindParam("storyID",$args['storyID']);
		$start->execute();
		$selectedStart = $start->fetchColumn();
	
		$end = $this->db->prepare("SELECT endTime FROM stories WHERE storyID = :storyID");
		$end->bindParam("storyID",$args['storyID']);
		$end->execute();
		$selectedEnd = $end->fetchColumn();

		$sth = $this->db->prepare("SELECT e.* FROM experts e LEFT OUTER JOIN expertReservations er on e.expertID = er.expertID LEFT OUTER JOIN stories s on er.storyID = s.storyID WHERE e.expertID
		NOT IN (SELECT er.expertID FROM expertReservations er JOIN stories st on er.storyID = st.storyID WHERE st.storyDate = '$selectedDate' AND (st.startTime <= '$selectedStart' AND '$selectedStart' <= st.endTime) OR (st.endTime >= '$selectedEnd' AND '$selectedEnd' >= st.startTime));");
		$sth->execute();
		$experts = $sth->fetchAll();
		return $this->response->withJson($experts);
	});

	$app->post('/createNew', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO stories (storyTopic, storyDate, startTime, endTime, description, points) VALUES (:storyTopic, :storyDate, :startTime, :endTime, :description, :points)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyTopic", $input['storyTopic']);
		$sth->bindParam("storyDate", $input['storyDate']);
		$sth->bindParam("startTime", $input['startTime']);
		$sth->bindParam("endTime", $input['endTime']);
		$sth->bindParam("points", $input['points']);
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

	$app->delete('/deleteEvent/[{storyID}]', function($request, $response, $args){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM stories WHERE storyID = :storyID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $args['storyID']);
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

	$app->get('/reserved/[{anchorID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT e.*, s.* FROM equipment e JOIN equipReservations er ON e.equipID = er.equipID JOIN stories s ON s.storyID = er.storyID WHERE s.anchorID = :anchorID");
		$sth->bindParam("anchorID",$args['anchorID']);
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
	
	$app->delete('/deleteReservation[/{storyID}[/{equipID}]]', function($request, $response, $args){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM equipReservations WHERE equipID = :equipID AND storyID = :storyID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $args['storyID']);
		$sth->bindParam("equipID", $args['equipID']);
		$sth->execute();
		//$result = $sth->fetchAll();
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

	$app->get('/reserved/[{anchorID}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT v.*, s.* FROM vehicles v JOIN vehicleReservations vr ON v.vehicleID = vr.vehicleID JOIN stories s ON s.storyID = vr.storyID WHERE s.anchorID = :anchorID");
		$sth->bindParam("anchorID",$args['anchorID']);
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
	
	$app->delete('/deleteReservation[/{storyID}[/{vehicleID}]]', function($request, $response, $args){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM vehicleReservations WHERE storyID = :storyID AND vehicleID = :vehicleID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $args['storyID']);
		$sth->bindParam("vehicleID", $args['vehicleID']);
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
		$sth = $this->db->prepare("SELECT * FROM experts WHERE expertID = :conditions or expertName = :conditions or expertTopic = :conditions");
		$sth->bindParam("conditions", $args['conditions']);
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
	$app->delete('/deleteReservation[/{storyID}[/{expertID}]]', function($request, $response, $args){
		$input = $request->getParsedBody();
		$sql = "DELETE FROM expertReservations WHERE storyID = :storyID AND expertID = :expertID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyID", $args['storyID']);
		$sth->bindParam("expertID", $args['expertID']);
		$sth->execute();
		return $this->response->withJson($input);	
	});
	
});

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});