<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سجل إعداد مفرد. القيمة بتتخزّن JSON ويقراها/يكتبها App\Support\Settings.
 * الـ observer بيمسح cache الإعدادات عند أي حفظ/حذف.
 */
class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];
}
