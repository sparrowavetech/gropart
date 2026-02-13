<?php

namespace Botble\ProductBundles\Http\Controllers;

use Botble\DataSynchronize\Exporter\Exporter;
use Botble\DataSynchronize\Http\Controllers\ExportController;
use Botble\ProductBundles\Exporters\BundleExporter;

class ExportBundleController extends ExportController
{
    protected function getExporter(): Exporter
    {
        return BundleExporter::make();
    }
}
