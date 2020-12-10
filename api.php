<?php

/*POST /author/add
POST /author/update
POST /author/delete
GET /author/list - здесь опционально клиент может передать параметры «page» - номер страницы и «perPage» - количество записей на страницу.

POST /magazine/add
POST /magazine/update
POST /magazine/delete
GET /magazine/list - по параметрам page,perPage по аналогии с описанным list выше.
*/

$db_host = 'localhost';
$db_name = 'api';
$db_username = 'root';
$db_password = '';


$mysqli_connection = new mysqli($db_host, $db_username, $db_password);
if ($mysqli_connection->connect_error) {
	die("Подключение не удалось: " . $mysqli_connection->connect_error);
} 

$link = mysqli_init();
$success = mysqli_real_connect(
   $link, 
   $db_host, 
   $db_username, 
   $db_password, 
   $db_name
);


$controllers = array('author','magazine');
$actions = array('add','update','delete','list');

$routes = explode('/', $_SERVER['REQUEST_URI']);
if(!empty($routes[2]))
	$controller_name = $routes[2];
if(!empty($routes[3]))
	$action_name = strstr($routes[3], '.', true);

if(in_array($controller_name, $controllers) && in_array($action_name, $actions)) {
	switch ($action_name) {
		case 'add':
			if($controller_name == "magazine") {
				if(check_post_add()) {
					$query = $link->query("INSERT INTO api (name, description, img, authors, date) VALUES ('".$_GET['name']."', '".$_GET['description']."', '".$_GET['img']."', '".$_GET['authors']."', '".$_GET['date']."')");
					if($query) 
						print_r(json_encode(array("success" => "true",
									"response_code" => "200")));
					else
						print_r(json_encode(array("error_message" => "Unknown error!",
									"error_code" => "500")));
				}
			}
			else {
				if(check_author_add()) {
					$query = $link->query("SELECT authors FROM api WHERE name='".$_GET['name']."'");
					if($query) {
						$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
						$authors = json_decode($row['authors']);
						
						$authors[] = $_GET['authors'];
						$query = $link->query("UPDATE api SET authors='".$authors."' WHERE name='".$_GET['name']."'");
						if($query) 
							print_r(json_encode(array("success" => "true",
										"response_code" => "200")));
						else
							print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));
					}
					else 
						print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));
				}
			}
			break;
		case 'update':
			if($controller_name == "magazine") {
				if(check_post_add()) {
					$query = $link->query("UPDATE api SET description='".$_GET['description']."',img='".$_GET['img']."',authors='".$_GET['authors']."',date='".$_GET['date']."' WHERE name='".$_GET['name']."'");
	
					if($query) {
						print_r(json_encode(array("success" => "true",
												"response_code" => "200")));
					}
					else 
						print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));
				}	
			}
			else {
				if(check_author_add()) {
					$query = $link->query("SELECT authors FROM api WHERE name='".$_GET['name']."'");
					if($query) {
						$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
						$authors = json_decode($row['authors']);
						
						$get_authors = json_decode($_GET['authors']);
						print_r(json_encode($authors));
						
						for($i = 0; $i < count($authors); ++$i) 
							if($authors[$i]->sname == $get_authors[0]->sname) {
								$authors[$i] = $get_authors[0];
								$query = $link->query("UPDATE api SET authors='".json_encode($authors)."' WHERE name='".$_GET['name']."'");
	
								if($query) 
									print_r(json_encode(array("success" => "true",
												"response_code" => "200")));
								else
									print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
												"error_code" => "500")));		
							}
						
					}
					else 
						print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));
				}	

			
			}
			break;
		case 'delete':
			if($controller_name == "magazine") {
				if(isset($_GET['name']) && $_GET['name'] != "")
					$query = $link->query("DELETE FROM api WHERE name='".$_GET['name']."'");
				else 
					print_r(json_encode(array("error_message" => "Invalid parameter 'name'",
										"error_code" => "400")));
			}
			else {
				if(isset($_GET['name']) && $_GET['name'] != "") {
					$query = $link->query("SELECT authors FROM api WHERE name='".$_GET['name']."'");
					if($query) {
						$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
						$authors = json_decode($row['authors']);
						if(unlink($authors[array_search($_GET['authors'], $authors))])
							print_r(json_encode(array("success" => "true",
										"response_code" => "200")));
						else 
							print_r(json_encode(array("error_message" => "Unknown error or not found author!",
										"error_code" => "500")));
						$query = $link->query("UPDATE api SET authors='".$authors."' WHERE name='".$_GET['name']."'");
						if($query) 
							print_r(json_encode(array("success" => "true",
										"response_code" => "200")));
						else
							print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));		
					}
					else 
						print_r(json_encode(array("error_message" => "Invalid parameter 'name'",
										"error_code" => "400")));		
				}
			}
			break;
		case 'list':
			if($controller_name == "magazine") {
				if(isset($_GET['name']) && $_GET['name'] != "") {
					$query = $link->query("SELECT * FROM api WHERE name='".$_GET['name']."'");
					$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
					if($row)
						print_r(json_encode($row));
					else 
						print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));
				}
				else 
					print_r(json_encode(array("error_message" => "Invalid parameter 'name'",
										"error_code" => "400")));
			}
			else {
				if(isset($_GET['name']) && $_GET['name'] != "") {
					$query = $link->query("SELECT name, authors FROM api WHERE name='".$_GET['name']."'");
					$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
					if($row)
						print_r(json_encode($row));
					else 
						print_r(json_encode(array("error_message" => "Unknown error or not found magazine!",
										"error_code" => "500")));
				}
				else 
					print_r(json_encode(array("error_message" => "Invalid parameter 'name'",
										"error_code" => "400")));
			}
			break;
	}
}
else
	print_r(json_encode(array("error_message" => "Unknown request!",
								"error_code" => "404")));




function check_post_add() {
	$flag = 0;
	if(isset($_GET['name']) && isset($_GET['img']) && isset($_GET['authors']) && isset($_GET['date'])) {
		if($_GET['name'] == "" || $_GET['img'] == "" || $_GET['authors'] == "" || $_GET['date'] == "")
			$flag = 1;
		else {
			if(json_decode($_GET['authors'])) {
				$authors = json_decode($_GET['authors']);
				foreach($authors as $author) {
					if(isset($author->sname) && isset($author->name)) {
						if(mb_strlen($author->sname) < 3)
							$flag = 2;
					}
					else 
						$flag = 3;
				}
			}
			else
				$flag = 3;
			if(!is_numeric(strtotime($_GET['date'])))
				$flag = 4;
		}
	}
	else
		print_r(json_encode(array("error_message" => "Invalid parameter. 'name', 'img', 'authors' fields are required!",
					"error_code" => "400")));

	if($flag == 0)
		return true;
	else {
		switch ($flag) {
			case 1:
				print_r(json_encode(array("error_message" => "Invalid parameter 'name', 'img', 'authors' fields must be filled!",
							"error_code" => "400")));
				break;
			case 2:
				print_r(json_encode(array("error_message" => "Invalid parameter in 'author' 'name'! Name length must be greater than 3",
								"error_code" => "400")));
				break;
			case 3:
				print_r(json_encode(array("error_message" => "Invalid parameter 'author'! You must use template: [{'name':'', 'sname':'', 'patronymic':''},{'name':'', 'sname':''},...]",
							"error_code" => "400")));
				break;
			case 4:
				print_r(json_encode(array("error_message" => "Invalid parameter 'date'! You must use pattern YYYY-MM-DD",
							"error_code" => "400")));
				break;
		}
		return false;
	}
}

function check_author_add() {
	$flag = 0;
	if(isset($_GET['name'])) {
		if($_GET['name'] == "")
			$flag = 1;
		else {
			if(json_decode($_GET['authors'])) {
				$authors = json_decode($_GET['authors']);
				foreach($authors as $author) {
					if(isset($author->sname) && isset($author->name)) {
						if(mb_strlen($author->sname) < 3)
							$flag = 2;
					}
					else 
						$flag = 3;
				}
			}
			else
				$flag = 3;
		}
	}
	else
		print_r(json_encode(array("error_message" => "Invalid parameter. 'name', 'img', 'authors' fields are required!",
					"error_code" => "400")));

	if($flag == 0)
		return true;
	else {
		switch ($flag) {
			case 1:
				print_r(json_encode(array("error_message" => "Invalid parameter 'name' fields must be filled!",
							"error_code" => "400")));
				break;
			case 2:
				print_r(json_encode(array("error_message" => "Invalid parameter in 'author' 'name'! Name length must be greater than 3",
								"error_code" => "400")));
				break;
			case 3:
				print_r(json_encode(array("error_message" => "Invalid parameter 'author'! You must use template: [{'name':'', 'sname':'', 'patronymic':''},{'name':'', 'sname':''},...]",
							"error_code" => "400")));
				break;
		}
		return false;
	}
}

?>
