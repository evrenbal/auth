<?php

namespace Baka\Auth\Models;

class SessionKeys extends \Phalcon\Mvc\Model
{
    /**
     * @var string
     */
    public $session_id;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $last_ip;

    /**
     * @var string
     */
    public $last_login;
}
