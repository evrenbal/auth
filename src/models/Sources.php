<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class Sources extends Model
{
    /**
     * @var integer
     */
    public $source_id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $url;

    /**
     * @var int
     */
    public $pv_order;

    /**
     * @var int
     */
    public $ep_order;

    /**
     * @var string
     */
    public $added_date;

    /**
     * @var string
     */
    public $updated_date;

    /**
     * get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->source_id;
    }

    /**
     * Get a source by its title
     */
    public static function getByTitle($title)
    {
        $sourceData = self::findFirstByTitle($title);

        if (!$sourceData) {
            throw new \Exception(_('Importing site is not currently supported.'));
        }

        return $sourceData;
    }
}
