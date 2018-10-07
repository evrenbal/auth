<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SessionKeysMigration_108
 */
class SessionKeysMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('session_keys', [
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
                        'last_ip',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 39,
                            'after' => 'users_id'
                        ]
                    ),
                    new Column(
                        'last_login',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'last_ip'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['session_id', 'users_id'], 'PRIMARY'),
                    new Index('last_login', ['last_login'], null),
                    new Index('user_id', ['users_id'], null),
                    new Index('session_id', ['session_id'], null)
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
