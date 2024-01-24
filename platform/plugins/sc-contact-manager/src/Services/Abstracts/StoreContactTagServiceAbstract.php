<?php

namespace Skillcraft\ContactManager\Services\Abstracts;

use Illuminate\Http\Request;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Models\ContactTag;

abstract class StoreContactTagServiceAbstract
{
    /**
     * @var ContactTag
     */
    protected $tagModel;

    /**
     * StoreContactTagService constructor.
     * @param ContactTag $model
     */
    public function __construct(ContactTag $model)
    {
        $this->tagModel = $model->query();
    }

    /**
     * @param Request $request
     * @param ContactManager $contact
     * @return mixed
     */
    abstract public function execute(Request $request, ContactManager $contact);
}
