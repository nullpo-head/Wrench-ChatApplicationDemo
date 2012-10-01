<?php

namespace Wrench\Application;

use Wrench\Application\Application;
use Wrench\Application\ChatApplication\ChatRoom;
use Wrench\Application\ChatApplication\UserNotFoundException;
use Wrench\Application\ChatApplication\ConnectionAlreadyEstablishedException;

class ChatApplication extends Application
{
	private $clienId2room = array(); //clientとチャットルームのインスタンスのテーブル
	private $roomName2room = array(); //チャットルームの名前とその名前のチャットルームのインスタンスのテーブル
    
    private function sendError($errorMessage, $client) {
        $client->send(json_encode(array("type" => "error", "message" => $errorMessage)));
    }	    
	
    // $connection: connection型	
    public function onDisconnect($connection) {
	    if (isset($this->clienId2room[$connection->getId()])) {
		    $this->clienId2room[$connection->getId()]->logoutUser($connection);
	    }
    }

    // $client: connection型
    public function onData($json, $client)
    {
	    $data = json_decode($json, true);
	    if ($data === NULL || !(isset($data['type'])) || (!isset($this->clienId2room[$client->getId()]) && $data['type'] !== "participate")){
		    $this->sendError("不正なデータを受信しました：無意味なメッセージ", $client);
		    return;
	    }

	    try {
		    switch ($data['type']) {
		    case "participate":
			    
			    if (!isset($data['roomId']) || !isset($data['userId'])) {
				    $this->sendError("ログインに必要な情報がたりません", $client);
				    return;
			    }
			    $data['roomId'] = trim($data['roomId']);
			    $data['userId'] = trim($data['userId']);
			    if (!isset($this->roomName2room[$data['roomId']])) {
				    $this->roomName2room[$data['roomId']] = new ChatRoom();
			    }
			    $this->clienId2room[$client->getId()] = $this->roomName2room[$data['roomId']];
			    $this->clienId2room[$client->getId()]->loginUser($client, $data['userId']);
				    
			    break;
		    case "message":
			    if (!isset($data['body'])) {
				    $this->sendError("不正なデータを受信しました：内容のないチャット送信", $client);
				    return;
			    }
			    $this->clienId2room[$client->getId()]->sendMessage($client, $data['body']);
			    break;
		    case "logout":
			    $this->clienId2room[$client->getId()]->logoutUser($client);
			    unset($this->clienId2room[$client->getId()]);
			    break;
			    
			    
		    default:
			    sendError("不正なデータを受信しました：無効な種類のメッセージ", $client);
			    return;
		    }

	    } catch (UserNotFoundException $e) {
		    $this->sendError($e->getMessage(), $client);
		    return;
	    } catch (ConnectionAlreadyEstablishedException $e) {
		    $this->sendError($e->getMessage(), $client);
		    return;
	    }




    }
}
