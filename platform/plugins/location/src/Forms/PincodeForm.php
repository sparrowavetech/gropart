<?php

namespace Botble\Location\Forms;

use Assets;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\Location\Http\Requests\PincodeRequest;
// use Botble\Location\Models\City;
use Botble\Location\Repositories\Interfaces\CountryInterface;
use Botble\Location\Repositories\Interfaces\StateInterface;
use Botble\Location\Repositories\Interfaces\CityInterface;
// new
use Botble\Location\Models\Pincode;

class PincodeForm extends FormAbstract
{

    /**
     * @var CountryInterface
     */
    protected $countryRepository;

    /**
     * @var StateInterface
     */
    protected $stateRepository;

    protected $cityRepository;

    /**
     * @param CountryInterface $countryRepository
     * @param StateInterface $stateRepository
     */
    public function __construct(CountryInterface $countryRepository, StateInterface $stateRepository, CityInterface $cityRepository)
    {
        parent::__construct();

        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm()
    {
        Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');

        // $countries = $this->countryRepository->pluck('countries.name', 'countries.id');
        $cities = $this->cityRepository->pluck('cities.name', 'cities.id');

        // $states = [];
        // if ($this->getModel()) {
        //     $states = $this->stateRepository->pluck('states.name', 'states.id',
        //         [['country_id', '=', $this->model->country_id]]);
        // }

        $this
            ->setupModel(new Pincode)
            ->setValidatorClass(PincodeRequest::class)
            ->withCustomFields()
            ->add('pincode', 'text', [
                'label'      => "Pincode",
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => "Pincode",
                    'data-counter' => 6,
                ],
            ])
            ->add('city_id', 'customSelect', [
                'label'      => "City",
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'id'        => 'country_id',
                    'class'     => 'form-control select-search-full',
                    'data-type' => 'country',
                ],
                'choices'    => [0 => "Select City"] + $cities,
            ])
            // ->add('state_id', 'customSelect', [
            //     'label'      => trans('plugins/location::city.state'),
            //     'label_attr' => ['class' => 'control-label'],
            //     'attr'       => [
            //         'id'        => 'state_id',
            //         'data-url'  => route('ajax.states-by-country'),
            //         'class'     => 'form-control select-search-full',
            //         'data-type' => 'state',
            //     ],
            //     'choices'    => ($this->getModel()->state_id ?
            //             [
            //                 $this->model->state->id => $this->model->state->name,
            //             ]
            //             :
            //             [0 => trans('plugins/location::city.select_state')]) + $states,
            // ])
            // ->add('order', 'number', [
            //     'label'         => trans('core/base::forms.order'),
            //     'label_attr'    => ['class' => 'control-label'],
            //     'attr'          => [
            //         'placeholder' => trans('core/base::forms.order_by_placeholder'),
            //     ],
            //     'default_value' => 0,
            // ])
            // ->add('is_default', 'onOff', [
            //     'label'         => trans('core/base::forms.is_default'),
            //     'label_attr'    => ['class' => 'control-label'],
            //     'default_value' => false,
            // ])
            ->add('status', 'customSelect', [
                'label'      => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
