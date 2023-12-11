<?php

namespace Botble\Table\Columns;

use Botble\Base\Contracts\BaseModel;
use Botble\Base\Supports\Renderable;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Concerns\Copyable;
use Botble\Table\Columns\Concerns\HasEmptyState;
use Botble\Table\Columns\Concerns\HasIcon;
use Botble\Table\Contracts\FormattedColumn as FormattedColumnContract;
use Closure;
use Illuminate\Support\Str;

class FormattedColumn extends Column implements FormattedColumnContract
{
    use Copyable;
    use HasEmptyState;
    use HasIcon;
    use Renderable;

    protected TableAbstract $table;

    protected int $limit;

    protected Closure $getValueUsingCallback;

    protected object $item;

    protected array $appendCallbacks = [];

    protected array $prependCallbacks = [];

    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data, $name)
            ->renderUsing(fn (FormattedColumn $column, $value) => $column->formattedValue($value));
    }

    public function limit(int $length = 5): static
    {
        $this->limit = $length;

        return $this;
    }

    public function applyLimitIfAvailable(string|null $text): string
    {
        if (isset($this->limit) && $this->limit > 0) {
            return Str::limit($text, $this->limit);
        }

        return $text ?: '';
    }

    public function formattedValue($value): string|null
    {
        return $value;
    }

    public function setTable(TableAbstract $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function getTable(): TableAbstract
    {
        return $this->table;
    }

    public function setItem(object $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getItem(): object
    {
        return $this->item;
    }

    public function getValueUsing(Closure $callback): static
    {
        $this->getValueUsingCallback = $callback;

        return $this;
    }

    public function append(Closure $callback): static
    {
        $this->appendCallbacks[] = $callback;

        return $this;
    }

    protected function renderAppends(): string
    {
        $rendered = '';

        foreach ($this->appendCallbacks as $callback) {
            $rendered .= call_user_func($callback, $this);
        }

        return $rendered;
    }

    public function prepend(Closure $callback): static
    {
        $this->prependCallbacks[] = $callback;

        return $this;
    }

    protected function renderPrepends(): string
    {
        $rendered = '';

        foreach ($this->prependCallbacks as $callback) {
            $rendered .= call_user_func($callback, $this);
        }

        return $rendered;
    }

    public function getValue(): mixed
    {
        if (isset($this->getValueUsingCallback)) {
            return call_user_func($this->getValueUsingCallback, $this);
        }

        return $this->getItem()->{$this->name};
    }

    public function renderCell(BaseModel|array $item, TableAbstract $table): string
    {
        $item = $item instanceof BaseModel ? $item : (object) $item;

        $this->setTable($table)->setItem($item);

        $rendered = $this->rendering(fn () => $this->getValue());

        $rendered = $this->renderEmptyStateIfAvailable($rendered);

        return $this->renderPrepends() . $rendered . $this->renderAppends();
    }
}
