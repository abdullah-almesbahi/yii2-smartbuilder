<?php


?>
<div class="panel panel-default grid-view" id="<?= \yii\helpers\Html::encode($_id) ?>">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $title ?></h3>
        <div class="tools pull-right"> <?= $header_append ?> </div>
    </div>
    <div class="panel-body">
        <?= $content ?>
    </div>
    <div class="panel-footer">
        <?= $footer ?>
    </div>
</div>