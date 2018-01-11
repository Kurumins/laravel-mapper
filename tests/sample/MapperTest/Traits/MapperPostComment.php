<?php

namespace MapperTest\Traits;

use  MapperTest\Post;
use  Carbon\Carbon;

/**
 * This trait has the only purpose of helping developers and IDEs to identify the properties and
 * methods of a Laravel Model.
 *
 * @method integer     getId()
 * @method $this       setName(string $name = null)
 * @method string|null getName()
 * @method $this       setComment(string $comment = null)
 * @method string|null getComment()
 * @method Carbon|null getCreatedAt()
 * @method Carbon|null getUpdatedAt()
 * 
 * //Virtual methods created based on foreign keys relationships.
 * @method $this       setPost(Post $Post)
 * @method Post        getPost()
 *
 * @package MapperTest\Traits
 */
trait MapperPostComment
{
// we do not need to repeat PHP code in here :-)
}
