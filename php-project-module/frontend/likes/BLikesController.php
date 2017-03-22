<?php

namespace redesign\pageblocks\common\likes;

use Exception;
use progorod\helpers\ArrayHelper;
use progorod\helpers\Path;
use progorod\modules\likes\db\models\Rating;
use progorod\routing\BaseController;
use progorod\routing\Response;
use progorod\routing\responses\JsonResponse;

/**
 * @author Sergey Sedyshev
 */
class BLikesController extends BaseController
{
    const ACTION_LIKE = "like";
    const ACTION_DISLIKE = "dislike";
    const ACTION_UNRATE = "unrate";
    const ACTION_RATING = "rating";
    
    
    /**
     * @return string
     */
    public static function ajaxUrl()
    {
        return Path::toRoot(__DIR__."/ajax.php");
    }
    

    /**
     * @return Response
     */
    public function handle()
    {
        $r = $this->request();
        $action = $r->param("action");
        
        if ($action == self::ACTION_LIKE || $action == self::ACTION_DISLIKE) {
            return $this->rateAction($action);
        }
        if ($action == self::ACTION_UNRATE) {
            return $this->unrateAction();
        }
        if ($action == self::ACTION_RATING) {
            return $this->ratingAction();
        }
        
        throw new Exception("Недопустимое действие");
    }
    
    /**
     * @param string $action
     * @return Response
     * @throws Exception
     */
    public function rateAction( $action )
    {
        $this->validateAjaxRefererHost();
        $this->validateSessionResume();
        
        $r = $this->request();
        
        $user = $this->session()->user();
        $userId = $user ? $user->id : 0;
        $sessId = $this->session()->id();
        
        $matType = $r->paramPost("matType");
        $matId = (int)$r->paramPost("matId");
        
        $mod = $this->cms()->modules()->likes();
        
        if ($action == self::ACTION_LIKE) {
            $mod->actions()->like($userId, $sessId, $matType, $matId);
        } elseif ($action == self::ACTION_DISLIKE) {
            $mod->actions()->dislike($userId, $sessId, $matType, $matId);
        }
        
        $rating = $mod->createRatingSelector()
            ->setCurrentUserId($userId)
            ->setCurrentSessId($sessId)
            ->whereMatType($matType)
            ->whereMatId($matId)
            ->limit(0, 1)
            ->fetchOne();
        
        $rating = $this->_ratingOrCreate($rating, $matType, $matId);
        
        return new JsonResponse([
            "action" => $action
            , "ratingHtml" => $this->cms()->templater()->render(
                __DIR__."/templates/rating.html.php"
                , [
                    "rating" => $rating
                ]
            )
        ]);
    }
    
    /**
     * @return Response
     * @throws Exception
     */
    public function unrateAction()
    {
        $this->validateAjaxRefererHost();
        $this->validateSessionResume();
        
        $r = $this->request();
        
        $user = $this->session()->user();
        $userId = $user ? $user->id : 0;
        $sessId = $this->session()->id();
        
        $matType = $r->paramPost("matType");
        $matId = (int)$r->paramPost("matId");
        
        $mod = $this->cms()->modules()->likes();
        
        $mod->actions()
            ->unrate($userId, $sessId, $matType, $matId);
        
        $rating = $mod->createRatingSelector()
            ->setCurrentUserId($userId)
            ->setCurrentSessId($sessId)
            ->whereMatType($matType)
            ->whereMatId($matId)
            ->limit(0, 1)
            ->fetchOne();
        
        $rating = $this->_ratingOrCreate($rating, $matType, $matId);
        
        return new JsonResponse([
            "action" => self::ACTION_UNRATE
            , "ratingHtml" => $this->cms()->templater()->render(
                __DIR__."/templates/rating.html.php"
                , [
                    "rating" => $rating
                ]
            )
        ]);
    }
    
    /**
     * @return Response
     * @throws Exception
     */
    public function ratingAction()
    {
        $this->validateAjaxRefererHost();
        
        $userId = $this->session()->userId();
        $sessId = $this->session()->id();
        
        $r = $this->request();
        $matDict = json_decode($r->param("matDict"), true);
        
        $mod = $this->cms()->modules()->likes();
        
        $dict = $mod->cache()->callableGet(
            __METHOD__
            , 1
            , [ $this, "renderedRatingDict" ]
            , [ $matDict, $userId, $sessId ]
        );
        
        return new JsonResponse([
            "action" => self::ACTION_RATING
            , "dict" => $dict
        ]);
    }
    
    /**
     * @param array $matDict
     * @param int $userId
     * @param string $sessId
     * @return array
     */
    public function renderedRatingDict( $matDict, $userId, $sessId )
    {
        $outDict = [];
        foreach ($matDict as $matType => $matIdDict) {
            $matIds = ArrayHelper::valuesToInt(array_keys($matIdDict));
            $ratingDict = $this->_getRatingDict($matType, $matIds, $userId, $sessId);
            
            $outDict[$matType] = [];
            foreach ($matIds as $matId) {
                $rating = $this->_ratingOrCreate($ratingDict[$matId], $matType, $matId);
                $outDict[$matType][$matId] = [
                    "ratingHtml" => $this->templater()->render(
                        __DIR__."/templates/rating.html.php"
                        , [ "rating" => $rating ]
                    )
                ];
            }
        }
        return $outDict;
    }
    
    /**
     * @param Rating $rating
     * @param string $matType
     * @param int $matId
     * @return Rating
     */
    private function _ratingOrCreate( $rating, $matType, $matId )
    {
        if (!$rating) {
            $rating = new Rating();
            $rating->matType = $matType;
            $rating->matId = $matId;
        }
        return $rating;
    }
    
    /**
     * @param string $matType
     * @param int[] $matIds
     * @param int $userId
     * @param string $sessId
     * @return Rating[]
     */
    private function _getRatingDict( $matType, $matIds, $userId, $sessId )
    {
        if (!$matIds) {
            return [];
        }
        
        $ratings = $this->cms()->modules()->likes()->createRatingSelector()
            ->setCurrentUserId($userId)
            ->setCurrentSessId($sessId)
            ->whereMatType($matType)
            ->whereMatIdIn($matIds)
            ->fetch();

        $ratingDict = [];
        foreach ($ratings as $rating) {
            $ratingDict[$rating->matId] = $rating;
        }
        
        return $ratingDict;
    }
}
