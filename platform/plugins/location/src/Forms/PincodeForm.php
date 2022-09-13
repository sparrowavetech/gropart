<?php
namespace Botble\Location\Forms;
use Assets;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\Location\Http\Requests\PincodeRequest;
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

        $cities = $this->cityRepository->pluck('cities.name', 'cities.id');

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