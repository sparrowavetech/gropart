<?php

namespace Skillcraft\ContactManager\Services\Abstracts;

use Botble\Base\Models\BaseQueryBuilder;
use Illuminate\Http\Request;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Models\ContactTag;

abstract class StoreContactTagServiceAbstract
{
    protected ContactTag|BaseQueryBuilder $tagModel;

    public function __construct(ContactTag $model)
    {
        $this->tagModel = $model->query();
    }

    abstract public function execute(Request $request, ContactManager $contact): void;
}
