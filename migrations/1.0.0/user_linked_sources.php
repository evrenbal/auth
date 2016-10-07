<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UserLinkedSourcesMigration_100
 */
class UserLinkedSourcesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('user_linked_sources', array(
                'columns' => array(
                    new Column(
                        'user_id',
                        array(
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 20,
                            'first' => true
                        )
                    ),
                    new Column(
                        'source_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 5,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'source_user_id',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 30,
                            'after' => 'source_id'
                        )
                    ),
                    new Column(
                        'source_user_id_text',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 255,
                            'after' => 'source_user_id'
                        )
                    ),
                    new Column(
                        'source_username',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'source_user_id_text'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('user_id', 'source_id'), null),
                    new Index('user_id', array('user_id'), null),
                    new Index('source_user_id', array('source_user_id'), null),
                    new Index('source_user_id_text', array('source_user_id_text'), null),
                    new Index('source_username', array('source_username'), null),
                    new Index('user_id_2', array('user_id', 'source_user_id_text'), null)
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
