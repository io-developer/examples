<?php

namespace progorod\modules\likes\db\tables;

use progorod\db\IDb;
use progorod\db\schema\Schema;
use progorod\db\schema\SchemaBuilder;

/**
 * @author Sergey Sedyshev
 */
class LikeTable
{
    const NAME = "likes";
    
    /**
     * @return LikeTableFields
     */
    public static function fields()
    {
        return new LikeTableFields();
    }
    
    /**
     * @return Schema
     */
    public static function schema()
    {
        $f = self::fields();
        
        return (new SchemaBuilder())
            ->setTable(self::NAME)
            
            ->column($f->userId, "INT")
            ->column($f->anonymousSessionId, "VARCHAR", 32, "")
            ->column($f->matType, "VARCHAR", 32, "")
            ->column($f->matId, "INT")
            ->column($f->date, "INT")
            ->column($f->isLike, "TINYINT", 0, 1)
            
            ->primaryKeyCombined([
                    $f->userId
                    , $f->anonymousSessionId
                    , $f->matType
                    , $f->matId
                ])
            ->indexCombined("mat_like", [ $f->matType, $f->matId, $f->isLike ])
            ->index($f->date)
            
            ->setEngine("InnoDb")
            ->getSchema();
    }
    
    
    public function __construct( IDb $db )
    {
        $this->_db = $db;
    }
    
    
    /** @var IDb */
    private $_db;
    
    
    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * @return LikeTableFields
     */
    public function getFields()
    {
        return self::fields();
    }
    
    /**
     * @return Schema
     */
    public function getSchema()
    {
        return self::schema();
    }
}
