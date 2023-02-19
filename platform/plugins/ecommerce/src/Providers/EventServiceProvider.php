<?php

namespace Botble\Ecommerce\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Events\ProductViewed;
use Botble\Ecommerce\Events\ShippingStatusChanged;
use Botble\Ecommerce\Listeners\AddLanguageForVariantsListener;
use Botble\Ecommerce\Listeners\GenerateInvoiceListener;
use Botble\Ecommerce\Listeners\RegisterEcommerceWidget;
use Botble\Ecommerce\Listeners\RenderingSiteMapListener;
use Botble\Ecommerce\Listeners\SendMailsAfterCustomerRegistered;
use Botble\Ecommerce\Listeners\SendProductReviewsMailAfterOrderCompleted;
use Botble\Ecommerce\Listeners\SendShippingStatusChangedNotification;
use Botble\Ecommerce\Listeners\SendWebhookWhenOrderPlaced;
use Botble\Ecommerce\Listeners\UpdateProductStockStatus;
use Botble\Ecommerce\Listeners\UpdateProductView;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RenderingSiteMapEvent::class => [
            RenderingSiteMapListener::class,
        ],
        CreatedContentEvent::class => [
            AddLanguageForVariantsListener::class,
        ],
        UpdatedContentEvent::class => [
            AddLanguageForVariantsListener::class,
        ],
        Registered::class => [
            SendMailsAfterCustomerRegistered::class,
        ],
        OrderPlacedEvent::class => [
            SendWebhookWhenOrderPlaced::class,
            GenerateInvoiceListener::class,
        ],
        OrderCreated::class => [
            GenerateInvoiceListener::class,
        ],
        ProductQuantityUpdatedEvent::class => [
            UpdateProductStockStatus::class,
        ],
        OrderCompletedEvent::class => [
            SendProductReviewsMailAfterOrderCompleted::class,
        ],
        ProductViewed::class => [
            UpdateProductView::class,
        ],
        ShippingStatusChanged::class => [
            SendShippingStatusChangedNotification::class,
        ],
        RenderingAdminWidgetEvent::class => [
            RegisterEcommerceWidget::class,
        ],
    ];
}
