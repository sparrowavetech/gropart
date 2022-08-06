<?php

namespace Botble\Location\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use Botble\Location\Repositories\Interfaces\PincodeInterface;

class PincodeCacheDecorator extends CacheAbstractDecorator implements PincodeInterface
{
    /**
     * {@inheritDoc}
     */
    public function filters($keyword, $limit = 10, array $with = [], array $select = ['pincodes.*'])
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }
}
