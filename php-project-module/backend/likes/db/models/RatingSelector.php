<?php

namespace progorod\modules\likes\db\models;

use progorod\db\IDb;
use progorod\db\queries\DbQuery;
use progorod\db\queries\DbQuerySelect;
use progorod\modules\likes\db\tables\LikeTable;
use progorod\modules\likes\db\tables\LikeTableFields;

/**
 * @author Sergey Sedyshev
 */
class RatingSelector
{
    /**
     * @param array $data
     * @return Rating
     */
    public static function map( $data )
    {
        $m = new Rating();
        $m->matType = $data["__matType"];
        $m->matId = (int)$data["__matId"];
        $m->numLikes = (int)$data["__numLikes"];
        $m->numDislikes = (int)$data["__numDislikes"];
        $m->rating = (int)$data["__rating"];
        $m->likedByMe = (bool)(int)$data["__likedByMe"];
        $m->dislikedByMe = (bool)(int)$data["__dislikedByMe"];
        $m->ratedByMe = (bool)(int)$data["__ratedByMe"];
        return $m;
    }
    
    /**
     * @param IDb $db
     */
    public function __construct( IDb $db )
    {
        $this->_db = $db;
        
        $f = $this->_f = LikeTable::fields();
        
        $this->_subquery = DbQuery::select()
            ->select([
                $f->matType => "matType"
                , $f->matId => "matId"
            ])
            ->selectFnAs("sum", [ $f->isLike ], "likes")
            ->selectRaw("(SUM(1) - SUM(`{$f->isLike}`)) AS dislikes")
            ->selectRaw("(2 * SUM(`{$f->isLike}`) - SUM(1)) AS rating")
            ->selectValueAs(0, "likedByMe")
            ->selectValueAs(0, "dislikedByMe")
            ->selectValueAs(0, "ratedByMe")
            ->from(LikeTable::NAME)
            ->groupBy([ $f->matType, $f->matId ]);
        
        $this->_query = DbQuery::select()
            ->select([
                "a.matType" => "__matType"
                , "a.matId" => "__matId"
                , "a.likes" => "__numLikes"
                , "a.dislikes" => "__numDislikes"
                , "a.rating" => "__rating"
                , "a.likedByMe" => "__likedByMe"
                , "a.dislikedByMe" => "__dislikedByMe"
                , "a.ratedByMe" => "__ratedByMe"
            ])
            ->fromSelectAs($this->_subquery, "a");
        
        $this->_updateSubqueryUser();
    }
    
    /**
     * 
     */
    private function _updateSubqueryUser()
    {
        if ($this->_currentUserId < 1 && $this->_currentSessId == "") {
            $this->_subquery
                ->popSelect()
                ->popSelect()
                ->popSelect()
                ->selectValueAs(0, "likedByMe")
                ->selectValueAs(0, "dislikedByMe")
                ->selectValueAs(0, "ratedByMe");
            return;
        }
        
        $f = $this->_f;
        
        if ($this->_currentUserId > 0) {
            $cond = ""
                . "`{$f->userId}` = " . (int)$this->_currentUserId
                . " AND `{$f->anonymousSessionId}` = ''";
        } elseif ($this->_currentSessId != "") {
            $cond = ""
                . "`{$f->userId}` = 0"
                . " AND `{$f->anonymousSessionId}` = '" . addslashes($this->_currentSessId) . "'";
        }
        
        $this->_subquery
            ->popSelect()
            ->popSelect()
            ->popSelect()
            ->selectRaw("SUM(`{$f->isLike}` = 1 AND {$cond}) AS likedByMe")
            ->selectRaw("SUM(`{$f->isLike}` = 0 AND {$cond}) AS disLikedByMe")
            ->selectRaw("SUM({$cond}) AS ratedByMe");
    }
    
    
    /** @var IDb */
    private $_db;
    
    /** @var DbQuerySelect */
    private $_query;
    
    /** @var DbQuerySelect */
    private $_subquery;
    
    /** @var LikeTableFields */
    private $_f;
    
    /** @var int */
    private $_currentUserId = 0;
    
    /** @var string */
    private $_currentSessId = "";
    
    
    /**
     * @param int $id
     * @return RatingSelector
     */
    public function setCurrentUserId( $id )
    {
        $this->_currentUserId = (int)$id;
        $this->_updateSubqueryUser();
        return $this;
    }
    
    /**
     * @param string $id
     * @return RatingSelector
     */
    public function setCurrentSessId( $id )
    {
        $this->_currentSessId = $id;
        $this->_updateSubqueryUser();
        return $this;
    }
    
    
    /**
     * @return DbQuerySelect
     */
    public function getQuery()
    {
        return clone $this->_query;
    }
    
    /**
     * @return Rating[]
     */
    public function fetch()
    {
        $models = [];
        $arr = $this->_db->queryAssoc($this->_query);
        foreach ($arr as $data) {
            $models[] = self::map($data);
        }
        return $models;
    }
    
    /**
     * @return Rating
     */
    public function fetchOne()
    {
        $arr = $this->fetch();
        return count($arr) > 0 ? $arr[0] : null; 
    }
    
    /**
     * @return int
     */
    public function fetchCount()
    {
        $q = $this->getQuery();
        $q->resetSelect()
            ->resetOrderBy()
            ->resetLimit()
            ->selectFnAs("count", ["*"], "__count");
        
        $r = $this->_db->queryAssocFirst($q);
        return $r ? (int)$r["__count"] : 0;
    }
    
    /**
     * @param string $type
     * @return RatingSelector
     */
    public function whereMatType( $type )
    {
        $f = $this->_f;   
        $this->_subquery
            ->whereField($f->matType, "=", $type);
        return $this;
    }
    
    /**
     * @param string $type
     * @return RatingSelector
     */
    public function whereMatTypeNot( $type )
    {
        $f = $this->_f;   
        $this->_subquery
            ->whereField($f->matType, "!=", $type);
        return $this;
    }
    
    /**
     * @param int $id
     * @return RatingSelector
     */
    public function whereMatId( $id )
    {
        $f = $this->_f;   
        $this->_subquery
            ->whereField($f->matId, "=", (int)$id);
        return $this;
    }
    
    /**
     * @param int[] $ids
     * @return RatingSelector
     */
    public function whereMatIdIn( $ids )
    {
        $f = $this->_f;   
        $this->_subquery
            ->whereField($f->matId, "in", $ids);
        return $this;
    }
    
    /**
     * @param int[] $ids
     * @return RatingSelector
     */
    public function whereMatIdNotIn( $ids )
    {
        $f = $this->_f;   
        $this->_subquery
            ->whereField($f->matId, "not in", $ids);
        return $this;
    }
    
    /**
     * @param int $rating
     * @return RatingSelector
     */
    public function whereRatingEQ( $rating )
    {
        $this->_query
            ->whereField("a.rating", "=", (int)$rating);
        return $this;
    }
    
    /**
     * @param int $rating
     * @return RatingSelector
     */
    public function whereRatingLT( $rating )
    {
        $this->_query
            ->whereField("a.rating", "<", (int)$rating);
        return $this;
    }
    
    /**
     * @param int $rating
     * @return RatingSelector
     */
    public function whereRatingLE( $rating )
    {
        $this->_query
            ->whereField("a.rating", "<=", (int)$rating);
        return $this;;
    }
    
    /**
     * @param int $rating
     * @return RatingSelector
     */
    public function whereRatingGT( $rating )
    {
        $this->_query
            ->whereField("a.rating", ">", (int)$rating);
        return $this;
    }
    
    /**
     * @param int $rating
     * @return RatingSelector
     */
    public function whereRatingGE( $rating )
    {
        $this->_query
            ->whereField("a.rating", ">=", (int)$rating);
        return $this;
    }
    
    /**
     * @return RatingSelector
     */
    public function whereIsLikedByMe()
    {
        $this->_query
            ->whereField("a.likedByMe", "=", 1);
        return $this;
    }
    
    /**
     * @return RatingSelector
     */
    public function whereIsDislikedByMe()
    {
        $this->_query
            ->whereField("a.dislikedByMe", "=", 1);
        return $this;
    }
    
    /**
     * @return RatingSelector
     */
    public function whereIsRatedByMe()
    {
        $this->_query
            ->whereField("a.ratedByMe", "=", 1);
        return $this;
    }
    
    /**
     * @param bool $asc
     * @return RatingSelector
     */
    public function orderByMatType( $asc=true )
    {
        $this->_query
            ->orderByField("a.matType", $asc ? "asc" : "desc");
        return $this;
    }
    
    /**
     * @param bool $asc
     * @return RatingSelector
     */
    public function orderByMatId( $asc=true )
    {
        $this->_query
            ->orderByField("a.matId", $asc ? "asc" : "desc");
        return $this;
    }
    
    /**
     * @param bool $asc
     * @return RatingSelector
     */
    public function orderByNumLikes( $asc=true )
    {
        $this->_query
            ->orderByField("a.likes", $asc ? "asc" : "desc");
        return $this;
    }
    
    /**
     * @param bool $asc
     * @return RatingSelector
     */
    public function orderByNumDislikes( $asc=true )
    {
        $this->_query
            ->orderByField("a.likes", $asc ? "asc" : "desc");
        return $this;
    }
    
    /**
     * @param bool $asc
     * @return RatingSelector
     */
    public function orderByRating( $asc=true )
    {
        $this->_query
            ->orderByField("a.rating", $asc ? "asc" : "desc");
        return $this;
    }
    
    
    /**
     * @param int $offset
     * @param int $total
     * @return RatingSelector
     */
    public function limit( $offset, $total )
    {
        $this->_query
            ->limit($offset, $total);
        return $this;
    }
}
