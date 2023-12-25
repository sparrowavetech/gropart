<?php

namespace Botble\Faq\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\MetaBox;
use Botble\Faq\FaqSupport;

class SaveFaqListener
{
    public function handle(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        $request = $event->request;
        $model = $event->data;

        if ($request->has('content') && $request->has('faq_schema_config')) {
            (new FaqSupport())->saveConfigs($model, $request->input('faq_schema_config'));
        }

        if ($request->filled('selected_existing_faqs')) {
            MetaBox::saveMetaBoxData($model, 'faq_ids', $request->input('selected_existing_faqs'));
        }
    }
}
