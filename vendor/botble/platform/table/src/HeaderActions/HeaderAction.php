<?php

namespace Botble\Table\HeaderActions;

use Botble\Base\Supports\Builders\HasAttributes;
use Botble\Base\Supports\Builders\HasColor;
use Botble\Base\Supports\Builders\HasIcon;
use Botble\Base\Supports\Builders\HasLabel;
use Botble\Base\Supports\Builders\HasPermissions;
use Botble\Base\Supports\Builders\HasUrl;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Blade;

class HeaderAction implements Arrayable
{
    use HasLabel;
    use HasColor;
    use HasIcon;
    use HasUrl;
    use HasPermissions;
    use HasAttributes;

    public function __construct(protected string $name)
    {
    }

    public static function make(string $name): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        $content = '';

        if ($this->getIcon()) {
            $content .= sprintf('<x-core::icon name="%s" />', $this->getIcon());
        }

        if ($this->getLabel()) {
            $content .= $this->getLabel();
        }

        $html = sprintf(
            '<span data-action="%s" data-href="%s">%s</span>',
            $this->getName(),
            $this->getUrl(),
            Blade::render($content)
        );

        return [
            'className' => sprintf('action-item %s %s', $this->getColor(), $this->getAttribute('class')),
            'text' => $html,
        ];
    }

    public function route(string $route, array $parameters = [], bool $absolute = true): static
    {
        $this
            ->url(fn (HeaderAction $action) => route($route, $parameters, $absolute))
            ->permission($route);

        return $this;
    }
}
