<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SessionsMigration_108
 */
class SessionsMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('sessions', [
                'columns' => [
                    new Column(
                        'session_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'users_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 20,
                            'after' => 'session_id'
                        ]
                    ),
                    new Column(
                        'start',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'notNull' => true,
                            'size' => 20,
                            'after' => 'users_id'
                        ]
                    ),
                    new Column(
                        'time',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'start'
                        ]
                    ),
                    new Column(
                        'ip',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 39,
                            'after' => 'time'
                        ]
                    ),
                    new Column(
                        'page',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'ip'
                        ]
                    ),
                    new Column(
                        'logged_in',
                        [
                            'type' => Column::TYPE_CHAR,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'page'
                        ]
                    ),
                    new Column(
                        'is_admin',
                        [
                            'type' => Column::TYPE_CHAR,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'logged_in'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['session_id'], 'PRIMARY'),
                    new Index('user_id', ['users_id'], null),
                    new Index('time', ['time'], null),
                    new Index('logged_in', ['logged_in'], null),
                    new Index('start', ['start'], null)
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '',
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
