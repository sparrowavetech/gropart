<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\LoyaltyPoints\Forms\LoyaltyLevelForm;
use Botble\LoyaltyPoints\Http\Requests\LoyaltyLevelRequest;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Tables\LoyaltyLevelTable;

class LoyaltyLevelController extends BaseLoyaltyController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/loyalty-points::loyalty-points.levels.menu_name'), route('loyalty-points.levels.index'));
    }

    public function index(LoyaltyLevelTable $table)
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.levels.menu_name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.levels.create'));

        return LoyaltyLevelForm::create()->renderForm();
    }

    public function store(LoyaltyLevelRequest $request, BaseHttpResponse $response)
    {
        LoyaltyLevel::query()->create($request->input());

        return $response
            ->setPreviousUrl(route('loyalty-points.levels.index'))
            ->setNextUrl(route('loyalty-points.levels.create'))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(LoyaltyLevel $level)
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.levels.edit', ['name' => $level->name]));

        return LoyaltyLevelForm::createFromModel($level)->renderForm();
    }

    public function update(LoyaltyLevel $level, LoyaltyLevelRequest $request, BaseHttpResponse $response)
    {
        $level->fill($request->input());
        $level->save();

        return $response
            ->setPreviousUrl(route('loyalty-points.levels.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(LoyaltyLevel $level, DeleteResourceAction $action)
    {
        return $action->handle($level, 'loyalty-points.levels.index');
    }
}
