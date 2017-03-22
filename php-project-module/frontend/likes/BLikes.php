<?php

namespace redesign\pageblocks\common\likes;

use progorod\templating\Template;

/**
 * @author Sergey Sedyshev
 */
class BLikes
{
    /**
     * @param Template $t
     * @return BLikes
     */
    public static function forTemplate( $t )
    {
        return new BLikes($t);
    }
    
    /**
     * @param Template $t
     */
    public function __construct( $t )
    {
        $this->_t = $t;
    }

    
    /** @var Template */
    private $_t;
    
    /** @var string */
    private $_matType = "";
    
    /** @var int */
    private $_matId = 0;
    
    
    /**
     * @param string $type
     * @param int $id
     * @return BLikes
     */
    public function setMaterial( $type, $id )
    {
        $this->_matType = $type;
        $this->_matId = (int)$id;
        return $this;
    }
    
    /**
     * @return string
     */
    public function renderDefer()
    {
        return $this->_t->render(
            __DIR__."/templates/rating-defer.html.php"
            , [
                "matType" => $this->_matType
                , "matId" => $this->_matId
            ]
        );
    }
}
