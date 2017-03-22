<?php

namespace progorod\modules\likes;

use progorod\BaseFactory;

/**
 * @author Sergey Sedyshev
 */
class LikesModuleFactory extends BaseFactory
{
    /**
     * @return LikesModule
     */
    public function create()
    {
        return new LikesModule();
    }
}
