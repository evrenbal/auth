<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class SessionKeys extends Model
{
    /**
     * @var string
     */
    public $session_id;

    /**
     * @var integer
     */
    public $users_id;

    /**
     * @var string
     */
    public $last_ip;

    /**
     * @var string
     */
    public $last_login;
}
