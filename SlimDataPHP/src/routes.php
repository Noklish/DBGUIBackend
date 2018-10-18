<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{userID}]', function (Request $request, Response $response, array $args) {
	$sth = $this->db->prepare("SELECT userId, lastName, firstName, userName, email, points FROM accounts");
	$sth->execute();
	$accounts = $sth->fetchAll();
	return $this->response->withJson($accounts);
});

$app->post('/accounts', function ($request, $response) {
	$input = $request->getParsedBody();
	$sql = "INSERT INTO accounts (userID, lastName, firstName, userName, email, pass, points) VALUES (:userID, :lastName, :firstName, :userName, :email, :pass, :points)";
	$sth = $this->db->prepare($sql);
	$uID = floor(rand(0,10000));
	$existCheck = "SELECT userID FROM accounts WHERE userID = '$uID'";
	while(true){
		$result = $this->db->prepare($existCheck);
		$result->execute();
		if($result->rowCount() != 0)
		{
			$uID = floor(rand(0,10000));
			$existCheck = "SELECT userID FROM accounts WHERE userID = '$uID'";
			continue;
		}
		else
		{
			break;
		}
	}
	$userNameSelected = $input['userName'];
	$result = $this->db->prepare("SELECT userName FROM accounts WHERE userName = '$userNameSelected'");
	$result->execute();
	if($result->rowCount() != 0)
	{
		echo "Username already taken!";
		//$userNameSelected = $input['userName'];
		return;
	}
	$sth->bindParam("userID", $uID);
	$sth->bindParam("lastName", $input['lastName']);
	$sth->bindParam("firstName", $input['firstName']);
	$sth->bindParam("userName", $userNameSelected);
	$sth->bindParam("email", $input['email']);
	$sth->bindParam("pass", $input['pass']);
	$pts = 0;
	$sth->bindParam("points",$pts);
	$sth->execute();
	return $this->response->withJson($input);
});

$app->put('/updatePoints/[{accounts}]', function($request, $response){
	$input = $request->getParsedBody();
	$sql = "update accounts set points=:points where userID=:userID";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("userID", $input['userID']);
	$sth->bindParam("points", $input['points']);
	$sth->execute();
	return $this->response->withJson($input);
});

$app->put('/changePassword/[{accounts}]', function($request, $response){
	$input = $request->getParsedBody();
	$sql = "update accounts set pass=:pass where userID=:userID";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("userID", $input['userID']);
	$sth->bindParam("pass", $input['pass']);
	$sth->execute();
	return $this->response->withJson($input);
});

$app->put('/changeUserInfo/[{accounts}]', function($request, $response){
	$input = $request->getParsedBody();
	$sql = "update accounts set userName=:userName, lastName=:lastName, firstName=:firstName, email=:email where userID=:userID";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("userID", $input['userID']);
	$sth->bindParam("userName", $input['userName']);
	$sth->bindParam("lastName", $input['lastName']);
	$sth->bindParam("firstName", $input['firstName']);
	$sth->bindParam("email", $input['email']);
	$sth->execute();
	return $this->response->withJson($input);
});