<?php

namespace App\Support\Store;

use RuntimeException;

/**
 * خطأ متعلّق بالسلة/الطلب يتعرض للعميل برسالة عربية مفهومة
 * (سلة فاضية، نفاد المخزون، منطقة خارج التغطية...).
 */
class CartException extends RuntimeException
{
}
