<?php

namespace progorod\modules\likes\model;

use progorod\modules\likes\db\tables\LikeTable;
use progorod\modules\likes\LikesModule;

/**
 * @author Sergey Sedyshev
 */
class ActionController
{
    public function __construct( LikesModule $module )
    {
        $this->_module = $module;
    }
    
    
    /** @var LikesModule */
    private $_module;
    
    
    /**
     * @param int $userId
     * @param string $anonSessId
     * @param string $matType
     * @param int $matId
     */
    public function like( $userId, $anonSessId, $matType, $matId )
    {
        $this->_addOrChange($userId, $anonSessId, $matType, $matId, true);
    }
    
    /**
     * @param int $userId
     * @param string $anonSessId
     * @param string $matType
     * @param int $matId
     */
    public function dislike( $userId, $anonSessId, $matType, $matId )
    {
        $this->_addOrChange($userId, $anonSessId, $matType, $matId, false);
    }
    
    /**
     * @param int $userId
     * @param string $anonSessId
     * @param string $matType
     * @param int $matId
     */
    public function unrate( $userId, $anonSessId, $matType, $matId )
    {
        $f = LikeTable::fields();
        
        $this->_module->db()->sqlQuery(<<<_____
            DELETE
            FROM `:table`
            WHERE (
                `:f_userId` = :userId
                AND `:f_anonSessId` = :anonSessId
                AND `:f_matType` = :matType
                AND `:f_matId` = :matId
            )
_____
            , [
                "table" => LikeTable::NAME
                , "f_userId" => $f->userId
                , "f_anonSessId" => $f->anonymousSessionId
                , "f_matType" => $f->matType
                , "f_matId" => $f->matId
                , "userId" => $userId ? (int)$userId : 0
                , "anonSessId" => $userId ? "" : "".$anonSessId
                , "matType" => "".$matType
                , "matId" => (int)$matId
            ]
        );
        $this->_module->cleanup();
    }
    
    /**
     * @param int $userId
     * @param string $anonSessId
     * @param string $matType
     * @param int $matId
     * @param bool $isLike
     */
    private function _addOrChange( $userId, $anonSessId, $matType, $matId, $isLike )
    {
        $f = LikeTable::fields();
        
        $this->_module->db()->sqlQuery(<<<_____
            INSERT INTO `:table` (
                `:f_userId`
                , `:f_anonSessId`
                , `:f_matType`
                , `:f_matId`
                , `:f_date`
                , `:f_isLike`
            ) VALUES (
                :userId
                , :anonSessId
                , :matType
                , :matId
                , :date
                , :isLike
            )
            ON DUPLICATE KEY UPDATE `:f_isLike` = :isLike
_____
            , [
                "table" => LikeTable::NAME
                , "f_userId" => $f->userId
                , "f_anonSessId" => $f->anonymousSessionId
                , "f_matType" => $f->matType
                , "f_matId" => $f->matId
                , "f_date" => $f->date
                , "f_isLike" => $f->isLike
                , "userId" => $userId ? (int)$userId : 0
                , "anonSessId" => $userId ? "" : "".$anonSessId
                , "matType" => "".$matType
                , "matId" => (int)$matId
                , "date" => time()
                , "isLike" => $isLike ? 1 : 0
            ]
        );
        $this->_module->cleanup();
    }
}
