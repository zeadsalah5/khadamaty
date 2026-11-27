<?php

namespace Tests\Unit;

use App\Support\ArticleLinks;
use PHPUnit\Framework\TestCase;

class ArticleLinksFaqTest extends TestCase
{
    public function test_parses_bold_question_style(): void
    {
        $md = "## مقدمة\nكلام\n\n## أسئلة بتتسأل كتير\n\n**التركيب بياخد قد إيه؟** ساعتين لتلاتة، اقرا [الدليل](/blog/x).\n\n**سؤال تاني؟** إجابة.\n\n## احجز\nرابط";
        $faqs = ArticleLinks::faqs($md);
        $this->assertCount(2, $faqs);
        $this->assertSame('التركيب بياخد قد إيه؟', $faqs[0]['q']);
        $this->assertSame('ساعتين لتلاتة، اقرا الدليل.', $faqs[0]['a']);
    }

    public function test_parses_h3_style_and_ignores_missing_section(): void
    {
        $md = "## الأسئلة الشائعة\n\n### الكود ده خطر؟\nلأ مش خطر.\n\n### أعمل إيه؟\nاتصل بفني.\n";
        $this->assertCount(2, ArticleLinks::faqs($md));
        $this->assertSame([], ArticleLinks::faqs("## مقدمة\nمفيش أسئلة"));
    }
}
