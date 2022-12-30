<?php

namespace api\modules\v1\controllers\actions\doctor;

use api\modules\v1\resources\Doctor;
use common\models\RnovaDoctor;
use yii\base\Action;

class RandomAction extends Action
{
    public $limit = 3;

    public function run()
    {
        return Doctor::find()->with('professions')->andWhere(['status' => RnovaDoctor::STATUS_ACTIVE])
            ->orderBy(new \yii\db\Expression('rand()'))->limit($this->limit)->all();
    }
}
