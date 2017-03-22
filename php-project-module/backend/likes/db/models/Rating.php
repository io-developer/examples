<?php

namespace progorod\modules\likes\db\models;

/**
 * @author Sergey Sedyshev
 */
class Rating
{
    /** @var string */
    public $matType = "";
    
    /** @var int */
    public $matId = 0;
    
    /** @var int */
    public $numLikes = 0;
    
    /** @var int */
    public $numDislikes = 0;
    
    /** @var int */
    public $rating = 0;
    
    /** @var bool */
    public $likedByMe = false;
    
    /** @var bool */
    public $dislikedByMe = false;
    
    /** @var bool */
    public $ratedByMe = false;
}
