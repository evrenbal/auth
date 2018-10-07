<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class SourcesMigration_108
 */
class SourcesMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('sources', [
                'columns' => [
                    new Column(
                        'source_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 5,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'title',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'source_id'
                        ]
                    ),
                    new Column(
                        'url',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'title'
                        ]
                    ),
                    new Column(
                        'language_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 5,
                            'after' => 'url'
                        ]
                    ),
                    new Column(
                        'added_date',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'language_id'
                        ]
                    ),
                    new Column(
                        'updated_date',
                        [
                            'type' => Column::TYPE_DATETIME,
                            'size' => 1,
                            'after' => 'added_date'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['source_id'], 'PRIMARY'),
                    new Index('unq1', ['url'], 'UNIQUE')
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
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
