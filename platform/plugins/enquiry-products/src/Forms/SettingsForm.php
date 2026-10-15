<?php
namespace SparroWave\EnquiryProducts\Forms;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
class SettingsForm extends \Botble\Setting\Forms\SettingForm
{
    public function setup(): void
    {
        parent::setup();
        $this->setUrl(route('enquiry-products.settings.update'))->setSectionTitle(__('plugins/enquiry-products::messages.enquiry_settings'))
            ->setSectionDescription(__('plugins/enquiry-products::messages.reference_prefix_enquiry_id_suffix_reference_settings_apply_to_ne'))
            ->setValidatorClass(\SparroWave\EnquiryProducts\Http\Requests\SettingsRequest::class);
        foreach (['ep_catalog_title' => ['plugins/enquiry-products::messages.enquiry_page_title', "Equipment's Enquiry"], 'ep_catalog_subtitle' => ['plugins/enquiry-products::messages.enquiry_page_subtitle_optional', ''], 'ep_reference_prefix' => ['plugins/enquiry-products::messages.reference_prefix', '#GPEQ-'], 'ep_reference_suffix' => ['plugins/enquiry-products::messages.reference_suffix', ''], 'ep_catalog_slug' => ['plugins/enquiry-products::messages.enquiry_page_url_slug', 'equipments-enquiry-products']] as $key => [$label, $default]) {
            $this->add($key, TextField::class, TextFieldOption::make()->label(__($label))->value(setting($key, $default))->toArray());
        }
    }
}
