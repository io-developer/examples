<?php

use progorod\modules\likes\db\models\Rating;
use progorod\templating\Template;

/* @var $this Template */
/* @var $t Template */

/* @var $rating Rating */
$rating = $p["rating"];

$blockClass = "";
if ($rating->likedByMe) {
    $blockClass .= " likes_liked";
} elseif ($rating->dislikedByMe) {
    $blockClass .= " likes_disliked";
}

$totalClass = "";
if ($rating->rating > 0) {
    $blockClass .= " likes__total_positive";
} elseif ($rating->rating < 0) {
    $blockClass .= " likes__total_negative";
}

?>


<div
    class="likes <?= $t->attr($blockClass) ?>"
    data-mat-type="<?= $t->attr($rating->matType) ?>"
    data-mat-id="<?= $t->attr($rating->matId) ?>"
    data-liked-by-me="<?= $t->attr($rating->likedByMe ? 1 : 0) ?>"
    data-disliked-by-me="<?= $t->attr($rating->dislikedByMe ? 1 : 0) ?>"
>
    <div class="likes__total-wrapper font-size_default">
        <span class="likes__total <?= $t->attr($blockClass) ?>">
            <?= $t->text($rating->rating) ?>
        </span>
    </div>
    <div class="likes__like font-size_default"></div>
    <div class="likes__dislike font-size_default"></div>
</div>
