<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SessionKeysMigration_100
 */
class SessionKeysMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('session_keys', array(
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
                        'last_ip',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 39,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'last_login',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'last_ip'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('session_id', 'user_id'), null),
                    new Index('last_login', array('last_login'), null),
                    new Index('user_id', array('user_id'), null),
                    new Index('session_id', array('session_id'), null)
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
