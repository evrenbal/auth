<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UsersMigration_100
 */
class UsersMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('users', array(
                'columns' => array(
                    new Column(
                        'user_id',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 20,
                            'first' => true
                        )
                    ),
                    new Column(
                        'email',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'password',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 255,
                            'after' => 'email'
                        )
                    ),
                    new Column(
                        'firstname',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'password'
                        )
                    ),
                    new Column(
                        'lastname',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'firstname'
                        )
                    ),
                    new Column(
                        'displayname',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'lastname'
                        )
                    ),
                    new Column(
                        'registered',
                        array(
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'displayname'
                        )
                    ),
                    new Column(
                        'lastvisit',
                        array(
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'registered'
                        )
                    ),
                    new Column(
                        'dob',
                        array(
                            'type' => Column::TYPE_DATE,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'lastvisit'
                        )
                    ),
                    new Column(
                        'sex',
                        array(
                            'type' => Column::TYPE_CHAR,
                            'default' => "U",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'dob'
                        )
                    ),
                    new Column(
                        'timezone',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'default' => "America/New_York",
                            'notNull' => true,
                            'size' => 128,
                            'after' => 'sex'
                        )
                    ),
                    new Column(
                        'city_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 8,
                            'after' => 'timezone'
                        )
                    ),
                    new Column(
                        'state_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 10,
                            'after' => 'city_id'
                        )
                    ),
                    new Column(
                        'country_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 5,
                            'after' => 'state_id'
                        )
                    ),
                    new Column(
                        'profile_privacy',
                        array(
                            'type' => Column::TYPE_CHAR,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'country_id'
                        )
                    ),
                    new Column(
                        'interests',
                        array(
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'profile_privacy'
                        )
                    ),
                    new Column(
                        'profile_image',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'interests'
                        )
                    ),
                    new Column(
                        'profile_remote_image',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 255,
                            'after' => 'profile_image'
                        )
                    ),
                    new Column(
                        'profile_header',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 192,
                            'after' => 'profile_remote_image'
                        )
                    ),
                    new Column(
                        'profile_header_mobile',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 192,
                            'after' => 'profile_header'
                        )
                    ),
                    new Column(
                        'user_active',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'profile_header_mobile'
                        )
                    ),
                    new Column(
                        'user_level',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'user_active'
                        )
                    ),
                    new Column(
                        'user_login_tries',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'user_level'
                        )
                    ),
                    new Column(
                        'user_last_login_try',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'size' => 20,
                            'after' => 'user_login_tries'
                        )
                    ),
                    new Column(
                        'session_time',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'size' => 20,
                            'after' => 'user_last_login_try'
                        )
                    ),
                    new Column(
                        'session_page',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'session_time'
                        )
                    ),
                    new Column(
                        'welcome',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'session_page'
                        )
                    ),
                    new Column(
                        'user_activation_key',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 64,
                            'after' => 'welcome'
                        )
                    ),
                    new Column(
                        'user_activation_email',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 64,
                            'after' => 'user_activation_key'
                        )
                    ),
                    new Column(
                        'user_activation_forgot',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 100,
                            'after' => 'user_activation_email'
                        )
                    ),
                    new Column(
                        'language',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 5,
                            'after' => 'user_activation_forgot'
                        )
                    ),
                    new Column(
                        'modified_at',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 18,
                            'after' => 'language'
                        )
                    ),
                    new Column(
                        'karma',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'modified_at'
                        )
                    ),
                    new Column(
                        'votes',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 10,
                            'after' => 'karma'
                        )
                    ),
                    new Column(
                        'votes_points',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'votes'
                        )
                    ),
                    new Column(
                        'banned',
                        array(
                            'type' => Column::TYPE_CHAR,
                            'default' => "N",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'votes_points'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('user_id'), null),
                    new Index('unq1', array('email'), null),
                    new Index('unq2', array('displayname'), null),
                    new Index('idx1', array('city_id'), null),
                    new Index('idx2', array('state_id'), null),
                    new Index('idx3', array('country_id'), null)
                ),
                'options' => array(
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_general_ci'
                ),
            )
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
