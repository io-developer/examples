<?php

use progorod\templating\Template;
use redesign\pageblocks\common\session\BSession;

/* @var $this Template */
/* @var $t Template */

$matType = $p["matType"];
$matId = $p["matId"];
   
?>


<?= BSession::forTemplate($t)->requireStart() ?>

<?= $t->requireStyle("./css/module.css") ?>
<?= $t->requireScript("./js/module.js") ?>


<div
    class="likes-defer"
    data-mat-type="<?= $t->attr($matType) ?>"
    data-mat-id="<?= $t->attr($matId) ?>"
></div>
