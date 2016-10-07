<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UserConfigMigration_100
 */
class UserConfigMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('user_config', array(
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
                        'name',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'value',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'name'
                        )
                    ),
                    new Column(
                        'added_date',
                        array(
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'value'
                        )
                    ),
                    new Column(
                        'updated_date',
                        array(
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'added_date'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('user_id', 'name'), null)
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
