<?php

namespace Baka\Auth\Models;

class Banlist extends \Phalcon\Mvc\Model
{
    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $email;
}
