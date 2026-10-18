<?php

namespace App\Http\Controllers\Content;

use App\Models\Product;
use Illuminate\View\View;

class ShopController extends PageController
{
    protected string $modelClass = Product::class;
    protected string $basePath = '/shop';
    protected string $indexTitle = 'المتجر وقطع الغيار';
    protected string $indexSubtitle = 'قطع غيار ومستلزمات التكييفات والأجهزة.';
    protected string $singleType = 'product';

    public function index(): View
    {
        return $this->renderIndex();
    }

    public function show(Product $product): View
    {
        return $this->renderShow($product);
    }
}
