<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UsersMigration_108
 */
class UsersMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('users', [
                'columns' => [
                    new Column(
                        'id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 20,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'id'
                        ]
                    ),
                    new Column(
                        'password',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 255,
                            'after' => 'email'
                        ]
                    ),
                    new Column(
                        'firstname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'password'
                        ]
                    ),
                    new Column(
                        'lastname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'firstname'
                        ]
                    ),
                    new Column(
                        'user_role',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'lastname'
                        ]
                    ),
                    new Column(
                        'displayname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'user_role'
                        ]
                    ),
                    new Column(
                        'registered',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'displayname'
                        ]
                    ),
                    new Column(
                        'lastvisit',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'registered'
                        ]
                    ),
                    new Column(
                        'dob',
                        [
                            'type' => Column::TYPE_DATE,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'lastvisit'
                        ]
                    ),
                    new Column(
                        'sex',
                        [
                            'type' => Column::TYPE_CHAR,
                            'default' => "U",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'dob'
                        ]
                    ),
                    new Column(
                        'timezone',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'default' => "America/New_York",
                            'notNull' => true,
                            'size' => 128,
                            'after' => 'sex'
                        ]
                    ),
                    new Column(
                        'city_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 8,
                            'after' => 'timezone'
                        ]
                    ),
                    new Column(
                        'state_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 10,
                            'after' => 'city_id'
                        ]
                    ),
                    new Column(
                        'country_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 5,
                            'after' => 'state_id'
                        ]
                    ),
                    new Column(
                        'profile_privacy',
                        [
                            'type' => Column::TYPE_CHAR,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'country_id'
                        ]
                    ),
                    new Column(
                        'interests',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'profile_privacy'
                        ]
                    ),
                    new Column(
                        'profile_image',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'interests'
                        ]
                    ),
                    new Column(
                        'profile_remote_image',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 255,
                            'after' => 'profile_image'
                        ]
                    ),
                    new Column(
                        'profile_header',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 192,
                            'after' => 'profile_remote_image'
                        ]
                    ),
                    new Column(
                        'profile_header_mobile',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 192,
                            'after' => 'profile_header'
                        ]
                    ),
                    new Column(
                        'user_active',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'profile_header_mobile'
                        ]
                    ),
                    new Column(
                        'user_level',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'user_active'
                        ]
                    ),
                    new Column(
                        'user_login_tries',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'user_level'
                        ]
                    ),
                    new Column(
                        'user_last_login_try',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'size' => 20,
                            'after' => 'user_login_tries'
                        ]
                    ),
                    new Column(
                        'session_time',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'size' => 20,
                            'after' => 'user_last_login_try'
                        ]
                    ),
                    new Column(
                        'session_page',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'session_time'
                        ]
                    ),
                    new Column(
                        'welcome',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'session_page'
                        ]
                    ),
                    new Column(
                        'user_activation_key',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 64,
                            'after' => 'welcome'
                        ]
                    ),
                    new Column(
                        'user_activation_email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 64,
                            'after' => 'user_activation_key'
                        ]
                    ),
                    new Column(
                        'user_activation_forgot',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 100,
                            'after' => 'user_activation_email'
                        ]
                    ),
                    new Column(
                        'language',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 5,
                            'after' => 'user_activation_forgot'
                        ]
                    ),
                    new Column(
                        'modified_at',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 18,
                            'after' => 'language'
                        ]
                    ),
                    new Column(
                        'karma',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'modified_at'
                        ]
                    ),
                    new Column(
                        'votes',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'size' => 10,
                            'after' => 'karma'
                        ]
                    ),
                    new Column(
                        'votes_points',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 11,
                            'after' => 'votes'
                        ]
                    ),
                    new Column(
                        'banned',
                        [
                            'type' => Column::TYPE_CHAR,
                            'default' => "N",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'votes_points'
                        ]
                    ),
                    new Column(
                        'created_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'banned'
                        ]
                    ),
                    new Column(
                        'update_at',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'created_at'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['id'], 'PRIMARY'),
                    new Index('unq1', ['email'], 'UNIQUE'),
                    new Index('unq2', ['displayname'], 'UNIQUE'),
                    new Index('idx1', ['city_id'], null),
                    new Index('idx2', ['state_id'], null),
                    new Index('idx3', ['country_id'], null)
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '25',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_general_ci'
                ],
            ]
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
