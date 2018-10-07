<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class BanlistMigration_108
 */
class BanlistMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('banlist', [
                'columns' => [
                    new Column(
                        'ban_id',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 8,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'users_id',
                        [
                            'type' => Column::TYPE_BIGINTEGER,
                            'default' => "0",
                            'notNull' => true,
                            'size' => 20,
                            'after' => 'ban_id'
                        ]
                    ),
                    new Column(
                        'ip',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'default' => "",
                            'notNull' => true,
                            'size' => 35,
                            'after' => 'users_id'
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 255,
                            'after' => 'ip'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('PRIMARY', ['ban_id'], 'PRIMARY'),
                    new Index('ban_ip_user_id', ['ip', 'users_id'], null)
                ],
                'options' => [
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_bin'
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
