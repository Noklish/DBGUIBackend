<?php
use Slim\Http\Request;
use Slim\Http\Response;
// Routes


$app->get('/[{userID}]', function (Request $request, Response $response, array $args) {
	$sth = $this->db->prepare("SELECT userId, userName, email FROM accounts");
	$sth->execute();
	$accounts = $sth->fetchAll();
	return $this->response->withJson($accounts);
});

$app->post('/accounts', function ($request, $response) {
	$input = $request->getParsedBody();
	$sql = "INSERT INTO accounts (userID, userName, email, pass, typeFlag) VALUES (:userID, :userName, :email, :pass, :typeFlag)";
	$sth = $this->db->prepare($sql);
	//auto-increment the ID
	$lastID = "SELECT max(userID) FROM accounts";
	$result = $this->db->prepare($lastID);
	$uID = $result->execute() + 1;
	
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
	$sth->bindParam("userID", $uID);
	$sth->bindParam("userName", $userNameSelected);
	$sth->bindParam("email", $emailSelected);
	$sth->bindParam("pass", $input['pass']);
	$sth->bindParam("typeFlag",$input['typeFlag']);
	$sth->execute();
	
	//If new account is an anchor
	if($input['typeFlag'] != 0)
	{
		$anchorInsert = "INSERT INTO anchorDetails (userID, points) VALUES (:userID, :points)";
		$sth2 = $this->db->prepare($anchorInsert);
		$pts = 0;
		$sth2->bindParam("userID",$uID);
		$sth2->bindParam("points",$pts);
		$sth2->execute();
	}
	return $this->response->withJson($input);
});

$app->put('/updatePoints', function($request, $response){
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