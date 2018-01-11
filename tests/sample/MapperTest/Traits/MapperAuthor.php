<?php

namespace MapperTest\Traits;

use  MapperTest\Author;
use  Carbon\Carbon;
use  MapperTest\Post;

/**
 * This trait has the only purpose of helping developers and IDEs to identify the properties and
 * methods of a Laravel Model.
 *
 * @method integer     getId()
 * @method $this       setName(string $name)
 * @method string      getName()
 * @method $this       setType(string $type)
 * @method string      getType()
 * @method Carbon|null getCreatedAt()
 * @method Carbon|null getUpdatedAt()
 * 
 * //Virtual methods created based on foreign keys relationships.
 * @method $this       setStudent(Author $Student)
 * @method Author|null getStudent()
 * @method $this       setTeacher(Author $Teacher)
 * @method Author|null getTeacher()
 * @method $this       addMyPost(Post $MyPost)
 * @method Post[]      listMyPosts()
 * @method $this       addReviewedPost(Post $ReviewedPost)
 * @method Post[]      listReviewedPosts()
 *
 * @package MapperTest\Traits
 */
trait MapperAuthor
{
// we do not need to repeat PHP code in here :-)
}
