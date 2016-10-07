<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SourcesMigration_100
 */
class SourcesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('sources', array(
                'columns' => array(
                    new Column(
                        'source_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 5,
                            'first' => true
                        )
                    ),
                    new Column(
                        'title',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'source_id'
                        )
                    ),
                    new Column(
                        'url',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'title'
                        )
                    ),
                    new Column(
                        'language_id',
                        array(
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 5,
                            'after' => 'url'
                        )
                    ),
                    new Column(
                        'added_date',
                        array(
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'language_id'
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
                    new Index('PRIMARY', array('source_id'), null),
                    new Index('unq1', array('url'), null)
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
