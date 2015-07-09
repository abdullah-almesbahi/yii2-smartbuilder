<?php

namespace SmartBuilder\widgets\image;

use backend\models\Image;
use yii\base\Action;

class RemoveAction extends Action
{
    public $uploadDir = '@webroot/upload';

    public function run($id, $filename)
    {
        $allImages = Image::findAll(['filename' => $filename]);

        if (count($allImages) === 1) {
            if (Image::deleteAll(['id' => $id])) {
                if (unlink(\Yii::getAlias('@webroot'.$this->uploadDir . '/' . $filename))) {
                    return unlink(\Yii::getAlias('@webroot'.$this->uploadDir . '/small-' . $filename));
                }
            }
        } elseif ($allImages > 1) {
            return Image::deleteAll(['id' => $id]);
        }

        return false;
    }
}
