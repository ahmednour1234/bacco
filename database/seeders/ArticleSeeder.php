<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'name_en'  => 'Articles',
                'name_ar'  => 'مقالات',
                'title_en' => 'What is Qimta and How Does It Work?',
                'title_ar' => 'ما هي قيمتا وكيف تعمل؟',
                'desc_en'  => '<p>Qimta is a B2B supply chain management platform designed for enterprise logistics. It connects buyers and brands through a centralized procurement portal.</p><p>Our platform enables real-time inventory synchronization, precision parts searching via RAG, and automated BOQ pricing workflows.</p>',
                'desc_ar'  => '<p>قيمتا هي منصة إدارة سلسلة التوريد بين الشركات مصممة للوجستيات المؤسسية. تربط المشترين والعلامات التجارية من خلال بوابة مشتريات مركزية.</p><p>تتيح منصتنا مزامنة المخزون في الوقت الفعلي والبحث الدقيق عن القطع عبر RAG وسير عمل تسعير جداول الكميات الآلي.</p>',
                'image'    => null,
                'active'   => true,
            ],
            [
                'name_en'  => 'Guides',
                'name_ar'  => 'أدلة',
                'title_en' => 'How Qimta turns a BOQ line into a priced answer',
                'title_ar' => 'كيف تحوّل قيمتا بند جدول الكميات إلى إجابة مسعّرة',
                'desc_en'  => '<p>Qimta\'s BOQ pricing engine leverages AI to automatically match line items to catalog products with real-time pricing. Simply upload your BOQ spreadsheet and receive a detailed quote within minutes.</p><ul><li>Automatic SKU matching</li><li>Supplier price comparison</li><li>PDF export ready</li></ul>',
                'desc_ar'  => '<p>يستخدم محرك تسعير جداول الكميات في قيمتا الذكاء الاصطناعي لمطابقة البنود تلقائيًا مع منتجات الكتالوج بأسعار فورية. ما عليك سوى رفع جدول الكميات والحصول على عرض سعر مفصل خلال دقائق.</p><ul><li>مطابقة رقم الصنف تلقائيًا</li><li>مقارنة أسعار الموردين</li><li>جاهز للتصدير بصيغة PDF</li></ul>',
                'image'    => null,
                'active'   => true,
            ],
            [
                'name_en'  => 'Product Updates',
                'name_ar'  => 'تحديثات المنتج',
                'title_en' => 'Keeping Your Qimta Account Secure',
                'title_ar' => 'الحفاظ على أمان حساب قيمتا',
                'desc_en'  => '<p>Enterprise security is at the core of Qimta. We recommend enabling SSO for organizations with more than 50 users and auditing API key access regularly.</p><p>All data is encrypted in transit and at rest using AES-256 and TLS 1.3 protocols.</p>',
                'desc_ar'  => '<p>الأمان المؤسسي في صميم قيمتا. نوصي بتفعيل تسجيل الدخول الموحد (SSO) للمنظمات التي تضم أكثر من 50 مستخدمًا ومراجعة صلاحيات مفاتيح API بانتظام.</p><p>جميع البيانات مشفرة أثناء النقل وفي حالة السكون باستخدام بروتوكولي AES-256 وTLS 1.3.</p>',
                'image'    => null,
                'active'   => true,
            ],
        ];

        foreach ($articles as $data) {
            Article::firstOrCreate(
                ['title_en' => $data['title_en']],
                $data
            );
        }
    }
}
