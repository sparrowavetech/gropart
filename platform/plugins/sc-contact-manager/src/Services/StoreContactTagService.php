<?php

namespace Skillcraft\ContactManager\Services;

use Illuminate\Http\Request;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Auth;
use Botble\Base\Events\CreatedContentEvent;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Services\Abstracts\StoreContactTagServiceAbstract;

class StoreContactTagService extends StoreContactTagServiceAbstract
{
    /**
     * @param Request $request
     * @param ContactManager $contact
     * @return mixed|void
     */
    public function execute(Request $request, ContactManager $contact)
    {
        $tags = $contact->tags->pluck('name')->all();

        $tagsInput = collect(json_decode($request->input('tag'), true))->pluck('value')->all();

        if (count($tags) != count($tagsInput) || count(array_diff($tags, $tagsInput)) > 0) {
            $contact->tags()->detach();
            foreach ($tagsInput as $tagName) {
                if (!trim($tagName)) {
                    continue;
                }

                $tag = $this->tagModel->where(['name' => $tagName])->first();

                if ($tag === null && !empty($tagName)) {
                    $tag = $this->tagModel->updateOrCreate([
                        'name'        => $tagName,
                        'author_id'   => Auth::check() ? Auth::id() : 0,
                        'author_type' => User::class,
                    ]);

                    $request->merge(['slug' => $tagName]);

                    event(new CreatedContentEvent(CONTACT_MANAGER_TAG_MODULE_SCREEN_NAME, $request, $tag));
                }

                if (!empty($tag)) {
                    $contact->tags()->attach($tag->id);
                }
            }
        }
    }
}
