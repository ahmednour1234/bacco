<?php

namespace Database\Seeders;

use App\Models\SeoMeta;
use Illuminate\Database\Seeder;

/**
 * Seeds SEO metadata for every public page, keyed by the canonical (EN) route
 * name. Bilingual title/description carry live :products / :brands / :categories
 * placeholders that the SeoMeta model substitutes at render time.
 *
 * Values mirror the strings previously hardcoded in the landing blades so the
 * migration is loss-less; admins can now edit them from /admin/seo.
 */
class SeoMetaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            SeoMeta::updateOrCreate(
                ['route_name' => $page['route_name']],
                $page + ['active' => true, 'og_type' => 'website']
            );
        }

        $this->command->info('Seeded ' . count($this->pages()) . ' SEO page records.');
    }

    /**
     * @return list<array<string, string>>
     */
    private function pages(): array
    {
        return [
            [
                'route_name'   => 'landing.boq-pricing',
                'label'        => 'Landing — BOQ Pricing',
                'title_en'     => 'BOQ Pricing — Construction Bill of Quantities | Qimta | Saudi Arabia & GCC',
                'title_ar'     => 'تسعير BOQ — جداول الكميات الإنشائية | كيمتا | السعودية والخليج',
                'meta_desc_en' => 'Price your entire Bill of Quantities in 60 seconds. Qimta matches every line item against :products verified construction products — free for contractors and procurement teams in Saudi Arabia and GCC.',
                'meta_desc_ar' => 'سعّر جدول الكميات بالكامل خلال 60 ثانية. كيمتا تُطابق كل بند مع :products منتج إنشائي معتمد — مجاناً للمقاولين وفرق المشتريات في السعودية والخليج.',
                'keywords_en'  => 'BOQ pricing, bill of quantities, construction pricing, Saudi Arabia, GCC',
                'keywords_ar'  => 'تسعير جداول الكميات, جدول الكميات, تسعير البناء, السعودية, الخليج',
            ],
            [
                'route_name'   => 'landing.construction-pricing',
                'label'        => 'Landing — Construction Pricing',
                'title_en'     => 'Construction Materials Pricing in Saudi Arabia & GCC — Qimta | :products Products',
                'title_ar'     => 'تسعير مواد البناء في السعودية والخليج — كيمتا | :products منتج',
                'meta_desc_en' => 'Get instant construction materials pricing for any project in Saudi Arabia and GCC. Qimta indexes :products products from :brands brands with automatic BOQ pricing.',
                'meta_desc_ar' => 'احصل على أسعار مواد البناء فوراً لأي مشروع في السعودية والخليج. كيمتا تفهرس :products منتجاً من :brands علامة تجارية مع تسعير تلقائي لجداول الكميات.',
                'keywords_en'  => 'construction materials pricing, building materials, Saudi Arabia, GCC, Qimta',
                'keywords_ar'  => 'تسعير مواد البناء, مواد البناء, السعودية, الخليج, كيمتا',
            ],
            [
                'route_name'   => 'landing.procurement-platform',
                'label'        => 'Landing — Procurement Platform',
                'title_en'     => 'B2B Construction Procurement Platform — Qimta | Saudi Arabia & GCC',
                'title_ar'     => 'منصة مشتريات الإنشاء B2B — كيمتا | السعودية والخليج',
                'meta_desc_en' => 'Qimta: B2B construction procurement platform for Saudi Arabia and GCC. Connects contractors and procurement teams to :products construction products from :brands verified brands — with instant, verified pricing.',
                'meta_desc_ar' => 'كيمتا: منصة مشتريات الإنشاء B2B للسعودية والخليج. تربط المقاولين وفرق المشتريات بـ :products منتج إنشائي من :brands علامة تجارية معتمدة — بتسعير فوري وموثّق.',
                'keywords_en'  => 'B2B procurement, construction procurement, procurement platform, Saudi Arabia, GCC',
                'keywords_ar'  => 'مشتريات B2B, مشتريات الإنشاء, منصة مشتريات, السعودية, الخليج',
            ],
            [
                'route_name'   => 'about',
                'label'        => 'About',
                'title_en'     => 'About Qimta — Construction BOQ Pricing Platform | Saudi Arabia',
                'title_ar'     => 'عن كيمتا — منصة تسعير جداول الكميات الإنشائية | السعودية',
                'meta_desc_en' => 'Learn about Qimta, the construction BOQ pricing platform serving Saudi Arabia and the GCC with instant, verified manufacturer prices across :products products.',
                'meta_desc_ar' => 'تعرّف على كيمتا، منصة تسعير جداول الكميات الإنشائية في السعودية والخليج بأسعار فورية وموثّقة عبر :products منتج.',
                'keywords_en'  => 'about Qimta, construction pricing platform, company, Saudi Arabia',
                'keywords_ar'  => 'عن كيمتا, منصة تسعير البناء, الشركة, السعودية',
            ],
            [
                'route_name'   => 'for-brands',
                'label'        => 'For Brands',
                'title_en'     => 'For Brands & Manufacturers — List Your Products on Qimta',
                'title_ar'     => 'للعلامات التجارية والمصنّعين — اعرض منتجاتك على كيمتا',
                'meta_desc_en' => 'Reach contractors and procurement teams across Saudi Arabia and the GCC. List your construction products on Qimta and get discovered in BOQ pricing.',
                'meta_desc_ar' => 'اوصل للمقاولين وفرق المشتريات في السعودية والخليج. اعرض منتجاتك الإنشائية على كيمتا وكن ضمن نتائج تسعير جداول الكميات.',
                'keywords_en'  => 'for brands, manufacturers, list products, construction suppliers, GCC',
                'keywords_ar'  => 'للعلامات التجارية, المصنّعون, عرض المنتجات, موردو البناء, الخليج',
            ],
            [
                'route_name'   => 'contact',
                'label'        => 'Contact',
                'title_en'     => 'Contact Qimta — Get in Touch',
                'title_ar'     => 'تواصل مع كيمتا',
                'meta_desc_en' => 'Contact the Qimta team for support, partnerships, or product enquiries across Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'تواصل مع فريق كيمتا للدعم أو الشراكات أو الاستفسارات في السعودية والخليج.',
                'keywords_en'  => 'contact Qimta, support, partnerships',
                'keywords_ar'  => 'تواصل مع كيمتا, الدعم, الشراكات',
            ],
            [
                'route_name'   => 'privacy',
                'label'        => 'Privacy Policy',
                'title_en'     => 'Privacy Policy — Qimta',
                'title_ar'     => 'سياسة الخصوصية — كيمتا',
                'meta_desc_en' => 'How Qimta collects, uses, and protects your data.',
                'meta_desc_ar' => 'كيف تجمع كيمتا بياناتك وتستخدمها وتحميها.',
            ],
            [
                'route_name'   => 'terms',
                'label'        => 'Terms of Service',
                'title_en'     => 'Terms of Service — Qimta',
                'title_ar'     => 'شروط الخدمة — كيمتا',
                'meta_desc_en' => 'The terms governing your use of the Qimta platform.',
                'meta_desc_ar' => 'الشروط التي تحكم استخدامك لمنصة كيمتا.',
            ],
            [
                'route_name'   => 'security',
                'label'        => 'Security',
                'title_en'     => 'Security — Qimta',
                'title_ar'     => 'الأمان — كيمتا',
                'meta_desc_en' => 'How Qimta keeps your account and data secure.',
                'meta_desc_ar' => 'كيف تحافظ كيمتا على أمان حسابك وبياناتك.',
            ],
            [
                'route_name'   => 'support',
                'label'        => 'Support',
                'title_en'     => 'Support — Qimta',
                'title_ar'     => 'الدعم — كيمتا',
                'meta_desc_en' => 'Get help using Qimta — guides, FAQs, and contact options.',
                'meta_desc_ar' => 'احصل على مساعدة في استخدام كيمتا — أدلة وأسئلة شائعة وطرق تواصل.',
            ],
            // ── Previously unmanaged pages ─────────────────────────────────
            // These render through the same layout but had no SeoMeta row, so
            // they fell back to the layout's generic defaults — including the
            // home page and the whole catalogue.
            [
                'route_name'   => 'home',
                'label'        => 'Home',
                'title_en'     => 'Construction Material Prices in Saudi Arabia | Qimta',
                'title_ar'     => 'أسعار مواد البناء في السعودية — تسعير فوري | كيمتا',
                'meta_desc_en' => 'Price a full BOQ in under 60 seconds. Qimta matches every line against :products verified construction products from :brands brands across Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'سعّر جدول الكميات كاملاً في أقل من 60 ثانية. كيمتا تطابق كل بند مع :products منتج إنشائي موثّق من :brands علامة تجارية في السعودية والخليج.',
                'keywords_en'  => 'construction material prices Saudi Arabia, BOQ pricing, building materials cost, contractor procurement',
                'keywords_ar'  => 'أسعار مواد البناء السعودية, تسعير جداول الكميات, تكلفة مواد البناء, مشتريات المقاولين',
            ],
            [
                'route_name'   => 'catalog.index',
                'label'        => 'Catalogue — Index',
                'title_en'     => 'Construction Materials Catalogue | :products Products | Qimta',
                'title_ar'     => 'كتالوج مواد البناء — :products منتج | كيمتا',
                'meta_desc_en' => 'Browse :products construction products from :brands brands across :categories categories, with verified pricing for projects in Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'تصفّح :products منتج إنشائي من :brands علامة تجارية عبر :categories تصنيف، بأسعار موثّقة لمشاريع السعودية والخليج.',
                'keywords_en'  => 'construction materials catalogue, building products Saudi Arabia, material suppliers, product directory',
                'keywords_ar'  => 'كتالوج مواد البناء, منتجات البناء السعودية, موردو المواد, دليل المنتجات',
            ],
            [
                'route_name'   => 'catalog.division',
                'label'        => 'Catalogue — Division',
                'title_en'     => 'Construction Division Products & Prices | Qimta',
                'title_ar'     => 'منتجات وأسعار أقسام البناء | كيمتا',
                'meta_desc_en' => 'Products, specifications and verified prices for this construction division across Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'منتجات ومواصفات وأسعار موثّقة لهذا القسم الإنشائي في السعودية والخليج.',
                'keywords_en'  => 'construction division, building materials by category, specifications, prices',
                'keywords_ar'  => 'أقسام البناء, مواد البناء حسب التصنيف, المواصفات, الأسعار',
            ],
            [
                'route_name'   => 'catalog.category',
                'label'        => 'Catalogue — Category',
                'title_en'     => 'Building Material Category & Prices | Qimta',
                'title_ar'     => 'تصنيف مواد البناء والأسعار | كيمتا',
                'meta_desc_en' => 'Compare products, brands and verified prices in this building material category for projects in Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'قارن المنتجات والعلامات التجارية والأسعار الموثّقة في هذا التصنيف لمشاريع السعودية والخليج.',
                'keywords_en'  => 'building material category, compare products, brand prices',
                'keywords_ar'  => 'تصنيف مواد البناء, مقارنة المنتجات, أسعار العلامات',
            ],
            [
                'route_name'   => 'catalog.item',
                'label'        => 'Catalogue — Product',
                'title_en'     => 'Product Specifications & Price | Qimta',
                'title_ar'     => 'مواصفات المنتج والسعر | كيمتا',
                'meta_desc_en' => 'Full specifications, brand and verified pricing for this construction product, with instant BOQ pricing for Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'المواصفات الكاملة والعلامة التجارية والسعر الموثّق لهذا المنتج الإنشائي، مع تسعير فوري لجداول الكميات في السعودية والخليج.',
                'keywords_en'  => 'product specifications, construction product price, material datasheet',
                'keywords_ar'  => 'مواصفات المنتج, سعر المنتج الإنشائي, بيانات المادة',
            ],
            [
                'route_name'   => 'news',
                'label'        => 'News — Index',
                'title_en'     => 'Construction Market News & Price Trends | Qimta',
                'title_ar'     => 'أخبار سوق البناء واتجاهات الأسعار | كيمتا',
                'meta_desc_en' => 'Construction material price trends, market analysis and procurement news for Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'اتجاهات أسعار مواد البناء وتحليلات السوق وأخبار المشتريات في السعودية والخليج.',
                'keywords_en'  => 'construction news Saudi Arabia, material price trends, market analysis, procurement news',
                'keywords_ar'  => 'أخبار البناء السعودية, اتجاهات أسعار المواد, تحليل السوق, أخبار المشتريات',
            ],
            [
                'route_name'   => 'news.show',
                'label'        => 'News — Article',
                'og_type'      => 'article',
                'title_en'     => 'Construction Market Insight | Qimta',
                'title_ar'     => 'رؤية في سوق البناء | كيمتا',
                'meta_desc_en' => 'Analysis and insight on construction material pricing and procurement in Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'تحليلات ورؤى حول تسعير مواد البناء والمشتريات في السعودية والخليج.',
                'keywords_en'  => 'construction market insight, price analysis, procurement',
                'keywords_ar'  => 'رؤى سوق البناء, تحليل الأسعار, المشتريات',
            ],
            [
                'route_name'   => 'guest.boq.create',
                'label'        => 'Try — Upload BOQ',
                'title_en'     => 'Upload Your BOQ — Free Instant Pricing | Qimta',
                'title_ar'     => 'ارفع جدول الكميات — تسعير فوري مجاني | كيمتا',
                'meta_desc_en' => 'Upload a BOQ and get verified supplier pricing in under 60 seconds. No account needed to start — free for contractors in Saudi Arabia and the GCC.',
                'meta_desc_ar' => 'ارفع جدول الكميات واحصل على أسعار موردين موثّقة في أقل من 60 ثانية. ابدأ بدون حساب — مجاناً للمقاولين في السعودية والخليج.',
                'keywords_en'  => 'upload BOQ, free BOQ pricing, bill of quantities pricing, instant construction quote',
                'keywords_ar'  => 'رفع جدول الكميات, تسعير جداول الكميات مجاناً, تسعير فوري, عرض سعر إنشائي',
            ],
            [
                'route_name'   => 'cookie',
                'label'        => 'Cookie Policy',
                'title_en'     => 'Cookie Policy — Qimta',
                'title_ar'     => 'سياسة ملفات تعريف الارتباط — كيمتا',
                'meta_desc_en' => 'How Qimta uses cookies and similar technologies.',
                'meta_desc_ar' => 'كيف تستخدم كيمتا ملفات تعريف الارتباط والتقنيات المشابهة.',
            ],
        ];
    }
}
