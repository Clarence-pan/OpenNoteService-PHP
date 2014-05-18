<?php
/**
 *	Project name: OpenNote
 * 	Author: Jacob Liscom
 *	Version: 13.7.0
 * 
 * Handles the java script to php calls
**/

//TODO restful service
	//javascript consumes the php service
	//then have multple back ends.(NodeJS?)
//TODO default value

	include_once dirname(__FILE__)."/../vendor/autoload.php";
	include_once dirname(__FILE__)."/Config.php";
	
	//clean input
		\controller\Util::cleanPost();
		\controller\Util::cleanGets();
	
	$app = new \Slim\Slim();
	$app->contentType("application/json");
	 
	/** 
	 * REST scheme
	 * GET to retrieve and search data
	 * POST to add data
	 * PUT to update data
	 * DELETE to delete data
	 */
	//auth
		//check username availability 
			$app->get("/user/:user", function($user) use ($app){
					$app->response->setStatus(\controller\Authenticater::checkAvailability($user, Config::getModel()));
			});
		
		//register  
			$app->post("/user/:user&:password", function ($user, $password)  use ($app){
				if(\Config::$registrationEnabled){//dont allow them to execute this call if it is disabled
					$app->response->setStatus(503); //return error code
				}
					
				try{
					$app->response->setBody(json_encode(\controller\Authenticater::login($user,$password, Config::getModel())));
				}
				catch(\controller\ServiceException $e){
					$app->response->setStatus($e->getCode()); //return error code
					return;
				}
				catch(\Exception $e){
					$app->response->setStatus(500); //return error code
				}
			});
			
		//login  
			$app->post("/token/:user&:password", function ($user, $password)  use ($app){
				$ip = $_SERVER["REMOTE_ADDR"];
				
				try{
					$app->response->setBody(json_encode(\controller\Authenticater::login($user,$password, $ip, Config::getModel())));
				}
				catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                }
			});
		
	//notes
		//get note
			$app->get("/note/:id", function ($id) use ($app) {
				try{
				    $token = $app->request->headers->get("token");
					$tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
					
					$note = \controller\NoteBook::getNote(Config::getModel(), $tokenServer, $id); //get note
					$app->response->setBody(json_encode($note)); //return it
				}
				catch(\controller\ServiceException $e){
					$app->response->setStatus($e->getCode()); //return error code
					return;				
				}
				catch(\Exception $e){
					$app->response->setStatus(500); //return error code
					return;
				}
			});
	
		//save note
			$app->post("/note/", function () use ($app){ 
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
                    
                    $note = json_decode($app->request->getBody());
                    $note = \controller\NoteBook::saveNote(Config::getModel(), $tokenServer, $note); 
                   
                    $app->response->setBody(json_encode($note)); //return it
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }               
			});
        
        //delete note
            $app->delete("/note/:id", function ($id) use ($app){ 
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
                    
                    $note = \controller\NoteBook::getNote(Config::getModel(), $tokenServer, $id); //get note
                    $note = \controller\NoteBook::removeNote(Config::getModel(), $tokenServer, $note); 
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
                
            });
	
	//Folder 
        //Get Folder
            $app->get("/folder/", function () use ($app) {
                try{
                	//get query
	                	$id = $app->request()->get("id");
	                	$levels = $app->request()->get("levels") != null ? $app->request()->get("levels") : 0; //default
	                	$includeNotes = $app->request()->get("includeNotes");
                	
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
                    
                    $folder = \controller\NoteBook::getFolder(Config::getModel(), $tokenServer, $id, $levels, $includeNotes); //get folder
                    
                    $app->response->setBody(json_encode($folder)); //return it
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
            });
            
		//Save folder
		  $app->post("/folder/", function () use ($app) {
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
                    
                    $folder = json_decode($app->request->getBody());
                    $folder = \controller\NoteBook::saveFolder(Config::getModel(), $tokenServer, $folder); //get folder
                    
                    $app->response->setBody(json_encode($folder)); //return it
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
            });
            
        //Update folder
          $app->put("/folder/", function () use ($app) {
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
                    
                    $folder = json_decode($app->request->getBody());
                    $folder = \controller\NoteBook::updateFolder(Config::getModel(), $tokenServer, $folder); //get folder
                    
                    $app->response->setBody(json_encode($folder)); //return it
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
            }); 
         
        //Delete folder
          $app->delete("/folder/:id", function ($id) use ($app) {
                try{
                    $token = $app->request->headers->get("token");
                    $tokenServer = \controller\Authenticater::validateToken($token, $_SERVER["REMOTE_ADDR"], Config::getModel()); //replace token with validated one
                    
                    $folder = \controller\NoteBook::getFolder(Config::getModel(), $tokenServer, $id); //get note
                    \controller\NoteBook::removeFolder(Config::getModel(), $tokenServer, $folder); //remove folder
                }
                catch(\controller\ServiceException $e){
                    $app->response->setStatus($e->getCode()); //return error code
                    return;             
                }
                catch(\Exception $e){
                    $app->response->setStatus(500); //return error code
                    return;
                }
            });   
		
	$app->run();
		
		
	//search
		if(isset($_POST["search"],$_POST["searchString"]))
			NoteBook::search(Config::getModel(), $_POST["searchString"]);
?>	