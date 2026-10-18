<?php

namespace App\Http\Controllers\Content;

use App\Models\Vendor;
use Illuminate\View\View;

class VendorController extends PageController
{
    protected string $modelClass = Vendor::class;
    protected string $basePath = '/vendors';
    protected string $indexTitle = 'التجار';
    protected string $indexSubtitle = 'تجار ومراكز صيانة موثوقة.';
    protected string $singleType = 'vendor';

    public function index(): View
    {
        return $this->renderIndex();
    }

    public function show(Vendor $vendor): View
    {
        return $this->renderShow($vendor);
    }
}
