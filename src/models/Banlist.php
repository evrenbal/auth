<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class Banlist extends Model
{
    /**
     * @var integer
     */
    public $users_id;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $email;
}
