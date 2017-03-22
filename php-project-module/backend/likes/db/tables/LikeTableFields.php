<?php

namespace progorod\modules\likes\db\tables;

/**
 * @author Sergey Sedyshev
 */
class LikeTableFields
{
    public $userId = "user_id";
    public $anonymousSessionId = "anon_sess_id";
    public $matType = "mat_type";
    public $matId = "mat_id";
    public $date = "date";
    public $isLike = "is_like";
}
