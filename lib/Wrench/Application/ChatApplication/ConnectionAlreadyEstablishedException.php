<?php

namespace Wrench\Application\ChatApplication;

class ConnectionAlreadyEstablishedException extends \RuntimeException {
	public function __construct() {
		parent::__construct("ログインしようとしているユーザーはすでに別のユーザーとしてコネクションを張っています");
	}
}
