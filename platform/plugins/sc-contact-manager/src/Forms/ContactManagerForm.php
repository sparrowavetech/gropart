<?php

namespace Skillcraft\ContactManager\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\Fields\TagField;
use Skillcraft\ContactManager\Enums\ContactDataTypeEnum;
use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Http\Requests\ContactManagerRequest;
use Skillcraft\ContactManager\Models\ContactGroup;
use Skillcraft\ContactManager\Services\ContactManagerService;

class ContactManagerForm extends FormAbstract
{
    public function buildForm(): void
    {
        $tags = null;

        if ($this->getModel()) {
            $tags = $this->getModel()->tags()->pluck('name')->all();
            $tags = implode(',', $tags);
        }

        $this
            ->setupModel(new ContactManager)
            ->setValidatorClass(ContactManagerRequest::class)
            ->withCustomFields()
            ->addCustomField('tags', TagField::class)
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('first_name', 'text', [
                'label' => trans('plugins/sc-contact-manager::contact-manager.forms.first_name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder'  => trans('plugins/sc-contact-manager::contact-manager.forms.first_name'),
                    'data-counter' => 120,
                ],
                'wrapper'    => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
            ])
            ->add('last_name', 'text', [
                'label' => trans('plugins/sc-contact-manager::contact-manager.forms.last_name'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder'  => trans('plugins/sc-contact-manager::contact-manager.forms.last_name'),
                    'data-counter' => 120,
                ],
                'wrapper'    => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-6',
                ],
            ])
            ->add('business_name', 'text', [
                'label' => trans('plugins/sc-contact-manager::contact-manager.forms.business_name'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder'  => trans('plugins/sc-contact-manager::contact-manager.forms.business_name'),
                    'data-counter' => 255,
                ],
                'wrapper'    => [
                    'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ' col-md-12',
                ],
            ])
            ->add('emails', 'repeater', [
                'label'      => __('plugins/sc-contact-manager::contact-manager.forms.emails'),
                'label_attr' => ['class' => 'control-label'],
                'fields' => [
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.email'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'email',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type' => 'customSelect',
                        'label' => trans('plugins/sc-contact-manager::contact-manager.forms.contact_type'),
                        'label_attr' => [
                            'class' => 'control-label'
                        ],
                        'attributes' => [
                            'name' => 'type',
                            'list' => ContactDataTypeEnum::labels(),
                            'value' => null,
                            'options' => [
                                'class' => 'form-control select-full',
                            ],
                        ],
                    ],
                ],
                'value' => ContactManagerService::getEmailRepeaterData($this->getModel()),
            ])
            ->add('phones', 'repeater', [
                'label'      => __('plugins/sc-contact-manager::contact-manager.forms.phones'),
                'label_attr' => ['class' => 'control-label'],
                'fields' => [
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.phone'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'phone',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type' => 'customSelect',
                        'label' => trans('plugins/sc-contact-manager::contact-manager.forms.contact_type'),
                        'label_attr' => [
                            'class' => 'control-label'
                        ],
                        'attributes' => [
                            'name' => 'type',
                            'list' => ContactDataTypeEnum::labels(),
                            'value' => null,
                            'options' => [
                                'class' => 'form-control select-full',
                            ],
                        ],
                    ],
                ],
                'value' => ContactManagerService::getPhoneRepeaterData($this->getModel()),
            ])
            ->add('addresses', 'repeater', [
                'label'      => __('plugins/sc-contact-manager::contact-manager.forms.addresses'),
                'label_attr' => ['class' => 'control-label'],
                'fields' => [
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.address'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'address',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.address2'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'address2',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.city'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'city',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.state'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'state',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type'       => 'text',
                        'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.postalcode'),
                        'label_attr' => ['class' => 'control-label'],
                        'attributes' => [
                            'name'    => 'postalcode',
                            'value'   => null,
                            'options' => [
                                'class'        => 'form-control'
                            ],
                            
                        ],
                    ],
                    [
                        'type' => 'customSelect',
                        'label' => trans('plugins/sc-contact-manager::contact-manager.forms.contact_type'),
                        'label_attr' => [
                            'class' => 'control-label'
                        ],
                        'attributes' => [
                            'name' => 'type',
                            'list' => ContactDataTypeEnum::labels(),
                            'value' => null,
                            'options' => [
                                'class' => 'form-control select-full',
                            ],
                        ],
                    ],
                ],
                'value' => ContactManagerService::getAddressRepeaterData($this->getModel()),
            ])
            ->add('rowOpen2', 'html', [
                'html' => '</div>',
            ])
            ->add('source', 'text', [
                'label' => trans('plugins/sc-contact-manager::contact-manager.forms.source'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'placeholder'  => trans('plugins/sc-contact-manager::contact-manager.forms.source'),
                    'data-counter' => 255,
                ],
            ])
            ->add('type', 'customSelect', [
                'label' => trans('plugins/sc-contact-manager::contact-manager.forms.type'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => ContactTypeEnum::labels(),
            ])
            ->add('group_id', 'customSelect', [
                'label'      => trans('plugins/sc-contact-manager::contact-manager.forms.group'),
                'label_attr' => ['class' => 'control-label'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'choices'    => ['' => 'None'] + (new ContactGroup())->query()->pluck('name', 'id')->toArray(),
            ])
            ->add('tag', 'tags', [
                'label'      => trans('plugins/blog::posts.form.tags'),
                'label_attr' => ['class' => 'control-label'],
                'value'      => $tags,
                'attr'       => [
                    'placeholder' => trans('plugins/blog::base.write_some_tags'),
                    'data-url'    => route('contact-tag.all'),
                ],
            ])
            ->setBreakFieldPoint('source');
    }
}
