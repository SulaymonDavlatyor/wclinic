<?php

namespace api\modules\v1\resources;



class Page extends \common\models\Page
{
    public function fields()
    {
        return ['id', 'slug', 'title', 'body', 'status', 'created_at', 'updated_at'];
    }
}
