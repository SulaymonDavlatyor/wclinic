<?php

namespace api\modules\v1\resources;


class ArticleTag extends \common\models\ArticleTag
{
    public function fields()
    {
        return ['id', 'name', 'frequency', 'slug', 'created_at', 'updated_at', 'articles'];
    }

    public function extraFields()
    {
        return ['articles'];
    }
}
