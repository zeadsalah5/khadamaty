<?php

namespace App\Console\Commands;

use App\Models\Visit;
use App\Support\VisitClassifier;
use Illuminate\Console\Command;

/**
 * يعيد تصنيف الزيارات القديمة اللي كانت طلبات فحص ثغرات (wp-admin, .env, ...)
 * واتسجّلت «إنسان» بالغلط — بيعلّمها بوت. مابيمسحش أي زيارة.
 */
class ReclassifyProbeVisits extends Command
{
    protected $signature = 'khadamaty:reclassify-probe-visits';

    protected $description = 'تعليم زيارات فحص الثغرات القديمة كبوت';

    public function handle(): int
    {
        $fixed = 0;

        Visit::query()->where('is_bot', false)->select(['id', 'path'])
            ->chunkById(1000, function ($visits) use (&$fixed) {
                $ids = $visits->filter(fn (Visit $v) => VisitClassifier::isProbePath($v->path))->pluck('id');
                if ($ids->isNotEmpty()) {
                    $fixed += Visit::whereIn('id', $ids)->update([
                        'is_bot'   => true,
                        'bot_name' => VisitClassifier::PROBE_BOT_NAME,
                        'browser'  => null,
                        'device'   => 'bot',
                    ]);
                }
            });

        $this->info("اتعلّم {$fixed} زيارة كبوت (فحص ثغرات).");

        return self::SUCCESS;
    }
}
