<?php
namespace app\links\db;

use compact\mvvm\impl\Model;
/**
 *
 * @author eaboxt
 *        
 */
class LinkModel extends Model
{
    const ID = "id";
    const GUID = "guid";
    const TITLE = "title";
    const URL = "url";
    const TAGS = "tags";
    const TIMESTAMP = "timestamp";
    const COUNT = "count";
    const ISPRIVATE = "isprivate";
}
