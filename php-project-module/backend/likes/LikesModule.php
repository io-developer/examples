<?php

namespace progorod\modules\likes;

use progorod\modules\BaseModule;
use progorod\modules\likes\db\models\RatingSelector;
use progorod\modules\likes\model\ActionController;

/**
 * @author Sergey Sedyshev
 */
class LikesModule extends BaseModule
{
    public function __construct()
    {
    }
    
    
    /** @var ActionController */
    private $_actions;
    
    
    /**
     * @return ActionController
     */
    public function actions()
    {
        if (!$this->_actions) {
            $this->_actions = new ActionController($this);
        }
        return $this->_actions;
    }
    
    /**
     * @return RatingSelector
     */
    public function createRatingSelector()
    {
        return new RatingSelector($this->db());
    }
}
