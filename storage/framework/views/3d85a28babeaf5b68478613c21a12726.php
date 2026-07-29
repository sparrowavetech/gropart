<?php
    $cssClass = Arr::get($selectAttributes, 'class') . ' form-select';

    $cssClass = trim(str_replace('form-control', '', $cssClass));

    Arr::set($selectAttributes, 'class', $cssClass);
    $choices = $list ?? $choices;

    if ($optionsAttributes && !is_array($optionsAttributes)) {
        $optionsAttributes = [];
    }

    $selectAttributes['id'] = Arr::get($selectAttributes, 'id', $name . '-select-' . rand(10000, 99999));
?>

<?php echo Form::select($name, $choices, $selected, $selectAttributes, $optionsAttributes, $optgroupsAttributes); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/partials/custom-select.blade.php ENDPATH**/ ?>