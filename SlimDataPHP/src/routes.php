<?php
use Slim\Http\Request;
use Slim\Http\Response;
// Routes

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
		$sth = $this->db->prepare("SELECT s.*, a.userName FROM stories s JOIN accounts a ON s.anchorID = a.userID WHERE storyDate >= CURDATE() ORDER BY storyDate, storyTime");
		$sth->execute();
		$stories = $sth->fetchAll();
		return $this->response->withJson($stories);
	});

	$app->get('/specificStory/[{storyDate}]', function (Request $request, Response $response, array $args) {
		$sth = $this->db->prepare("SELECT * FROM stories WHERE storyDate = :storyDate ORDER BY storyTime");
		$sth->bindParam("storyDate", $args['storyDate']);
		$sth->execute();
		$stories = $sth->fetchAll();
		return $this->response->withJson($stories);
	});

	$app->post('/createNew', function ($request, $response) {
		$input = $request->getParsedBody();
		$sql = "INSERT INTO stories (storyTopic, storyDate, storyTime, anchorID, description) VALUES (:storyTopic, :storyDate, :storyTime, :anchorID, :description)";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyTopic", $input['storyTopic']);
		$sth->bindParam("storyDate", $input['storyDate']);
		$sth->bindParam("storyTime", $input['storyTime']);
		$sth->bindParam("anchorID", $input['anchorID']);
		$sth->bindParam("description", $input['description']);
		$sth->execute();
		return $this->response->withJson($input);
	});


	$app->put('/coverEvent', function($request, $response){
		$input = $request->getParsedBody();
		$sql = "UPDATE stories set storyTopic=:storyTopic, storyDate=:storyDate, storyTime=:storyTime, description=:description where storyID=:storyID";
		$sth = $this->db->prepare($sql);
		$sth->bindParam("storyTopic", $input['storyTopic']);
		$sth->bindParam("storyDate", $input['storyDate']);
		$sth->bindParam("storyTime", $input['storyTime']);
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
