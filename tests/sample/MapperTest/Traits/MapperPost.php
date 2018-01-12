<?php

namespace MapperTest\Traits;

use  MapperTest\PostComment;
use  MapperTest\Author;
use  Carbon\Carbon;

/**
 * This trait has the only purpose of helping developers and IDEs to identify the properties and
 * methods of a Laravel Model.
 *
 * @method integer       getId()
 * @method $this         setTitle(string $title)
 * @method string        getTitle()
 * @method $this         setContent(string $content)
 * @method string        getContent()
 * @method $this         setPublicatedAt(Carbon $publicatedAt = null)
 * @method Carbon|null   getPublicatedAt()
 * @method $this         setAproved(bool $aproved = null)
 * @method bool|null     isAproved()
 * @method Carbon|null   getCreatedAt()
 * @method Carbon|null   getUpdatedAt()
 * 
 * //Virtual methods created based on foreign keys relationships.
 * @method $this         addPostComment(PostComment $PostComment)
 * @method PostComment[] listPostComments()
 * @method $this         setAuthor(Author $Author)
 * @method Author        getAuthor()
 *
 * @package MapperTest\Traits
 */
trait MapperPost
{
// we do not need to repeat PHP code in here :-)
}
