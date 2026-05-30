<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'sort_order' => 1,
                'tag'        => 'market',
                'name_en'    => 'Market Report',
                'name_ar'    => 'تقرير السوق',
                'title_en'   => 'Steel pricing volatility in Q3',
                'title_ar'   => 'تذبذب أسعار الفولاذ في الربع الثالث',
                'desc_en'    => "Analysis of supply chain shifts and how Qimta's engine is tracking real-time fluctuations.",
                'desc_ar'    => 'تحليل تحولات سلسلة التوريد وكيف يتتبع محرك كيمتا التقلبات في الوقت الفعلي.',
                'image'      => null,
                'active'     => true,
            ],
            [
                'sort_order' => 2,
                'tag'        => 'tech',
                'name_en'    => 'Tech Update',
                'name_ar'    => 'تحديث تقني',
                'title_en'   => 'RAG Engine V2.0 Launch',
                'title_ar'   => 'إطلاق محرك RAG الإصدار 2.0',
                'desc_en'    => 'Introducing sub-60 second matching for complex mechanical sub-assemblies.',
                'desc_ar'    => 'تقديم مطابقة دون 60 ثانية للتجمعات الميكانيكية المعقدة.',
                'image'      => null,
                'active'     => true,
            ],
            [
                'sort_order' => 3,
                'tag'        => 'case',
                'name_en'    => 'Case Study',
                'name_ar'    => 'دراسة حالة',
                'title_en'   => 'Efficiency at Scale',
                'title_ar'   => 'الكفاءة على نطاق واسع',
                'desc_en'    => 'How a Tier-1 contractor reduced procurement overhead by 40% using Qimta.',
                'desc_ar'    => 'كيف خفّض مقاول من الدرجة الأولى تكاليف المشتريات بنسبة 40% باستخدام كيمتا.',
                'image'      => null,
                'active'     => true,
            ],
        ];

        foreach ($items as $item) {
            Article::firstOrCreate(
                ['title_en' => $item['title_en']],
                $item
            );
        }
    }
}
