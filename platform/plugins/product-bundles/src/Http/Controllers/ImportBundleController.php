<?php

namespace Botble\ProductBundles\Http\Controllers;

use Botble\DataSynchronize\Http\Controllers\ImportController;
use Botble\DataSynchronize\Importer\Importer;
use Botble\ProductBundles\Importers\BundleImporter;

class ImportBundleController extends ImportController
{
    protected function getImporter(): Importer
    {
        return BundleImporter::make();
    }
}
