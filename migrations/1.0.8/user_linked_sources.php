<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UserLinkedSourcesMigration_108
 */
class UserLinkedSourcesMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('user_linked_sources', [
                'columns' => [
                    new Column(
                        'users_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 20,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'source_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 5,
                            'after' => 'users_id'
                        ]
                    ),
                    new Column(
                        'source_users_id',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 30,
                            'after' => 'source_id'
                        ]
                    ),
                    new Column(
                        'source_users_id_text',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 255,
                            'after' => 'source_users_id'
                        ]
                    ),
                    new Column(
                        'source_username',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'source_users_id_text'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['users_id', 'source_id'], 'PRIMARY'),
                    new Index('user_id', ['users_id'], null),
                    new Index('source_user_id', ['source_users_id'], null),
                    new Index('source_user_id_text', ['source_users_id_text'], null),
                    new Index('source_username', ['source_username'], null),
                    new Index('user_id_2', ['users_id', 'source_users_id_text'], null)
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
