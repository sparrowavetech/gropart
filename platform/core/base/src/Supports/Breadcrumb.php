<?php

namespace Botble\Base\Supports;

use Botble\Base\Facades\BaseHelper;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class Breadcrumb implements Htmlable
{
    use Renderable;

    protected array $items = [];

    protected string $currentGroup = 'admin';

    protected string $view = 'core/base::breadcrumb';

    public function for(string $group): static
    {
        $this->currentGroup = $group;

        return $this;
    }

    public function default(): static
    {
        return $this->for('admin');
    }

    public function add(string $label, string $url = ''): static
    {
        $label = BaseHelper::clean($label);

        $this->items[$this->currentGroup][$label] = compact('label', 'url');

        return $this;
    }

    public function prepend(string $label, string $url = ''): static
    {
        $label = BaseHelper::clean($label);

        $breadcrumb = $this->items[$this->currentGroup] ??= [];

        $this->items[$this->currentGroup] = [...[$label => compact('label', 'url')], ...$breadcrumb];

        return $this;
    }

    public function getItems(): Collection
    {
        if (empty($this->items[$this->currentGroup])) {
            return collect();
        }

        return collect($this->items[$this->currentGroup])->values();
    }

    public function render(): string
    {
        return $this->rendering(
            fn () => view($this->view, [
                'items' => $this->getItems(),
            ])->render()
        );
    }

    public function toHtml(): string
    {
        return $this->render();
    }
}
