<?php
/**
 * Created by PhpStorm.
 * User: rubenss
 * Date: 2018-01-10
 * Time: 5:38 PM
 */

namespace MapperTest;


use Mapper\Workers\MapperModel;
use MapperTest\Traits\MapperAuthor;

class Author extends MapperModel
{
    use MapperAuthor;

    public static function relNameByField($tableName, $fieldName)
    {
        $map['authors']['fk_revisor'] = 'Student';
        $map['authors']['id'] = 'Teacher';
        $map['posts']['fk_reviewer'] = 'ReviewedPost';
        $map['posts']['fk_author'] = 'MyPost';
        return isset($map[$tableName]) ? $map[$tableName][$fieldName] ?? null : null;
    }
}