<?php 

namespace Wrench\Application\ChatApplication;

use Wrench\Application\ChatApplication\ChatRoom;

class ChatRoom {

	private $userTable = array();

	public function loginUser($client, $username) {
		if (isset($this->userTable[$client->getId()])) {
			throw new ConnectionAlreadyEstablishedException();
		}

		$this->userTable[$client->getId()]['username'] = $username;
		$this->userTable[$client->getId()]['client'] = $client;

		$loginNotification = json_encode(array("type" => "login notify", "username" => $username));
		$usernames = array();
		foreach ($this->userTable as $user) {
			array_push($usernames, ($user['username']));
			if ($user['client'] !== $client) {
				$user['client']->send($loginNotification);
			}
		}
		$client->send(json_encode(array("type" => "login accept", "usernames" => $usernames)));
		return true;

	}

	public function logoutUser($client) {
		if (!isset($this->userTable[$client->getId()])) {
			throw new UserNotFoundException();
		}
		$logoutNotification = json_encode(array("type" => "logout notify", "username" => $this->userTable[$client->getId()]['username']));
		unset($this->userTable[$client->getId()]);
		foreach ($this->userTable as $user) {
			$user['client']->send($logoutNotification);
		}
		return true;
	}

	public function sendMessage($client, $message) {
		if (!isset($this->userTable[$client->getId()])) {
			throw new UserNotFoundException();
		}
		$data = json_encode(array("type" => "message", "username" => $this->userTable[$client->getId()]['username'], "body" => $message));
		foreach($this->userTable as $user) {
			$user['client']->send($data);
		}
		return true;
	}
}



