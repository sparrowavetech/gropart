<?php

namespace Botble\Theme\Supports;

use Botble\Base\Facades\BaseHelper;
use Botble\Media\Facades\RvMedia;

class SocialLink
{
    public function __construct(
        protected string|null $name,
        protected string|null $url,
        protected string|null $icon,
        protected string|null $image,
    ) {
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function getIcon(): string|null
    {
        return $this->icon;
    }

    public function getIconHtml(): string
    {
        if (BaseHelper::hasIcon($this->icon)) {
            return BaseHelper::renderIcon('ti ti-' . $this->icon);
        }

        return sprintf('<i class="%s"></i>', $this->icon);
    }

    public function getUrl(): string|null
    {
        return $this->url;
    }

    public function getImage(): string|null
    {
        return $this->image;
    }

    public function getImageHtml(): string|null
    {
        if (! $this->image) {
            return null;
        }

        return RvMedia::image($this->image, $this->name);
    }
}
