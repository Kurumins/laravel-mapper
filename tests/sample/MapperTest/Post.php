<?php
/**
 * Created by PhpStorm.
 * User: rubenss
 * Date: 2018-01-10
 * Time: 5:38 PM
 */

namespace MapperTest;


use Mapper\Workers\MapperModel;
use MapperTest\Traits\MapperPost;

class Post extends MapperModel
{
    use MapperPost;

}