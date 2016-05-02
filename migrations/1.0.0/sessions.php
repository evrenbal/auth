<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SessionsMigration_100
 */
class SessionsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('sessions', array(
                'columns' => array(
                    new Column(
                        'session_id',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'first' => true
                        )
                    ),
                    new Column(
                        'user_id',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 20,
                            'after' => 'session_id'
                        )
                    ),
                    new Column(
                        'start',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'notNull' => true,
                            'size' => 20,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'time',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'start'
                        )
                    ),
                    new Column(
                        'ip',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 39,
                            'after' => 'time'
                        )
                    ),
                    new Column(
                        'page',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'ip'
                        )
                    ),
                    new Column(
                        'logged_in',
                        array(
                            'type' => Column::TYPE_CHAR,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'page'
                        )
                    ),
                    new Column(
                        'is_admin',
                        array(
                            'type' => Column::TYPE_CHAR,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'logged_in'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('session_id'), null),
                    new Index('user_id', array('user_id'), null),
                    new Index('time', array('time'), null),
                    new Index('logged_in', array('logged_in'), null),
                    new Index('start', array('start'), null)
                ),
                'options' => array(
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '',
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
