<?php

namespace Wrench\Application\ChatApplication;

class UserNotFoundException extends \RuntimeException {
	public function __construct() {
		parent::__construct("ログインしていないユーザーからの操作です");
	}
}
