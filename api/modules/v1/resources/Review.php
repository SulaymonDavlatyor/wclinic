<?php

namespace api\modules\v1\resources;


class Review extends \common\models\Review
{
    public function fields()
    {
        return ['photo_base_url', 'photo_path'];
    }
}
