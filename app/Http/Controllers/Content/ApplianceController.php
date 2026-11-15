<?php

namespace App\Http\Controllers\Content;

use App\Models\Appliance;
use Illuminate\View\View;

class ApplianceController extends PageController
{
    protected string $modelClass = Appliance::class;
    protected string $basePath = '/appliances';
    protected string $indexTitle = 'الأجهزة';
    protected string $indexSubtitle = 'تكييفات، ثلاجات، غسالات وأجهزة منزلية — صيانة وقطع غيار.';
    protected bool $thinSection = true;
    protected string $singleType = 'appliance';

    public function index(): View
    {
        return $this->renderIndex();
    }

    public function show(Appliance $appliance): View
    {
        return $this->renderShow($appliance);
    }
}
