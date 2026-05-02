<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ArticleSeeder extends Seeder
{
    private function downloadImage(int $seed, string $filename): ?string
    {
        try {
            $response = Http::timeout(15)->get("https://picsum.photos/seed/{$seed}/1200/600");
            if ($response->successful()) {
                $path = "articles/{$filename}";
                Storage::disk('public')->put($path, $response->body());
                return $path;
            }
        } catch (\Throwable) {}
        return null;
    }

    public function run(): void
    {
        Article::truncate();

        $images = [
            $this->downloadImage(42,  'article-01.jpg'),
            $this->downloadImage(87,  'article-02.jpg'),
            $this->downloadImage(133, 'article-03.jpg'),
            $this->downloadImage(200, 'article-04.jpg'),
            $this->downloadImage(315, 'article-05.jpg'),
            $this->downloadImage(456, 'article-06.jpg'),
            $this->downloadImage(512, 'article-07.jpg'),
            $this->downloadImage(678, 'article-08.jpg'),
            $this->downloadImage(790, 'article-09.jpg'),
            $this->downloadImage(999, 'article-10.jpg'),
        ];

        $articles = [
            // 1
            [
                'name_en'  => 'Guides',
                'name_ar'  => 'أدلة',
                'title_en' => 'How to Order on Qimta After Pricing Your BOQ',
                'title_ar' => 'كيفية الطلب عبر قيمتا بعد تسعير جدول الكميات',
                'image'    => $images[0],
                'active'   => true,
                'desc_en'  => '<h2>Introduction</h2><p>Transitioning from a priced Bill of Quantities (BOQ) to a finalized order is a critical junction in procurement. Qimta\'s unified interface streamlines this move, ensuring data integrity and price protection across your entire project lifecycle.</p><h2>Step 1: Register and Secure Your Workspace</h2><p>Before proceeding to checkout, ensure your enterprise account is fully verified. This step locks in your regional tax compliance settings and procurement permissions.</p><h2>Step 2: Open and Review Priced Items</h2><p>Access your "Saved Estimates" and open the specific BOQ. Check for price expiration warnings — market fluctuations may require a quick refresh of the real-time material indices.</p><h2>Step 3: Compare Supplier Options</h2><p>Qimta presents a matrix of available merchants for each SKU. Toggle between <strong>Lowest Cost</strong>, <strong>Fastest Delivery</strong>, or <strong>Highest Compliance Rating</strong> to align with your project priorities.</p><h2>Step 4: Confirm Logistics and Site Access</h2><p>Input precise GPS coordinates for delivery drops. Specify offloading equipment available on-site (e.g., cranes, forklifts) to avoid logistical surcharges.</p><h2>Step 5: Merchant of Record Verification</h2><p>Finalize the transaction through our secure escrow. Qimta acts as the Merchant of Record, consolidating multiple supplier invoices into a single, audit-ready payment receipt.</p>',
                'desc_ar'  => '<h2>مقدمة</h2><p>الانتقال من جدول الكميات المسعَّر إلى أمر شراء نهائي هو نقطة تحوّل حاسمة في عملية الشراء. توحّد منصة قيمتا هذا الانتقال لضمان سلامة البيانات وحماية الأسعار.</p><h2>الخطوة 1: التسجيل وتأمين مساحة العمل</h2><p>قبل الشروع في الدفع، تأكد من اكتمال التحقق من حساب المؤسسة لقفل إعدادات الامتثال الضريبي وصلاحيات الشراء.</p><h2>الخطوة 2: فتح البنود المسعَّرة ومراجعتها</h2><p>ادخل إلى "التقديرات المحفوظة" وتحقق من تحذيرات انتهاء صلاحية الأسعار.</p><h2>الخطوة 3: مقارنة خيارات الموردين</h2><p>تعرض قيمتا مصفوفة من التجار لكل وحدة. بدّل بين <strong>الأقل تكلفة</strong> أو <strong>الأسرع توصيلاً</strong>.</p>',
            ],
            // 2
            [
                'name_en'  => 'Market Analysis',
                'name_ar'  => 'تحليل السوق',
                'title_en' => 'Understanding Construction Material Price Indexing',
                'title_ar' => 'فهم مؤشر أسعار مواد البناء',
                'image'    => $images[1],
                'active'   => true,
                'desc_en'  => '<h2>What Is a Construction Material Price Index?</h2><p>A Construction Material Price Index (CMPI) is a statistical measure tracking price changes of key building materials over time. It serves as a reference for contractors, developers, and procurement managers to benchmark costs and forecast budgets.</p><h2>Key Commodities Tracked</h2><ul><li><strong>Structural Steel:</strong> Hot-rolled sections, rebars, and hollow sections</li><li><strong>Cement &amp; Ready-Mix Concrete:</strong> OPC and special-grade mixes</li><li><strong>Copper Wiring:</strong> High-purity electrical cables</li><li><strong>HDPE Pipes:</strong> Pressure-rated plumbing and irrigation lines</li></ul><h2>How Qimta Uses Live Index Data</h2><p>Qimta integrates with regional commodity exchanges and supplier APIs to update its internal price engine every 4 hours. When a BOQ item price deviates more than 3% from the index baseline, the system flags it for manual review.</p><h2>Impact on Project Budgets</h2><p>Projects spanning more than 6 months face the highest exposure to index volatility. Qimta\'s <em>Price Lock</em> feature allows procurement teams to secure rates for up to 90 days.</p>',
                'desc_ar'  => '<h2>ما هو مؤشر أسعار مواد البناء؟</h2><p>مؤشر أسعار مواد البناء هو مقياس إحصائي يتتبع تغيرات أسعار المواد الإنشائية الرئيسية عبر الزمن.</p><h2>السلع الرئيسية المُتتبَّعة</h2><ul><li><strong>الفولاذ الإنشائي:</strong> المقاطع وحديد التسليح</li><li><strong>الأسمنت والخرسانة الجاهزة</strong></li><li><strong>أسلاك النحاس</strong></li><li><strong>أنابيب HDPE</strong></li></ul><h2>كيف تستخدم قيمتا بيانات المؤشر</h2><p>تتكامل قيمتا مع بورصات السلع وواجهات برمجة الموردين لتحديث محرك الأسعار كل 4 ساعات.</p>',
            ],
            // 3
            [
                'name_en'  => 'Logistics',
                'name_ar'  => 'اللوجستيات',
                'title_en' => 'Optimizing Last-Mile Delivery for Steel Reinforcements',
                'title_ar' => 'تحسين التوصيل للميل الأخير لحديد التسليح',
                'image'    => $images[2],
                'active'   => true,
                'desc_en'  => '<h2>The Last-Mile Challenge</h2><p>Last-mile delivery of steel reinforcements is consistently cited as one of the most cost-intensive and time-sensitive operations in construction supply chains. Urban congestion and site-specific unloading requirements compound the challenge.</p><h2>Pre-Delivery Site Assessment</h2><ul><li>Crane or forklift availability on-site</li><li>Maximum permissible vehicle length for site entry</li><li>Time-restricted delivery windows (municipality permits)</li><li>Laydown area dimensions and weight-bearing capacity</li></ul><h2>Route Optimization Engine</h2><p>Qimta\'s AI routing engine analyses real-time traffic data, bridge weight limits, and low-clearance zones to compute the optimal delivery route. This reduces delivery time by <strong>23%</strong> and fuel consumption by <strong>17%</strong>.</p><h2>Real-Time Tracking</h2><p>Once dispatched, the project manager receives live GPS tracking via the Qimta mobile app. Delivery confirmation is captured via digital signature and appended to the project\'s audit log.</p>',
                'desc_ar'  => '<h2>تحدي الميل الأخير</h2><p>يُصنَّف توصيل حديد التسليح في الميل الأخير ضمن أكثر العمليات تكلفةً في سلاسل التوريد الإنشائية.</p><h2>تقييم الموقع قبل التسليم</h2><ul><li>توافر الرافعة أو الرافعة الشوكية</li><li>الحد الأقصى لطول المركبة</li><li>نوافذ التسليم المقيدة</li></ul><h2>محرك تحسين المسارات</h2><p>يحلّل محرك قيمتا بيانات حركة المرور لاحتساب المسار الأمثل، مما يقلل وقت التسليم بنسبة <strong>23%</strong>.</p>',
            ],
            // 4
            [
                'name_en'  => 'Compliance',
                'name_ar'  => 'الامتثال',
                'title_en' => 'Vendor Verification Standards for Saudi Giga-Projects',
                'title_ar' => 'معايير التحقق من الموردين للمشاريع العملاقة السعودية',
                'image'    => $images[3],
                'active'   => true,
                'desc_en'  => '<h2>Overview</h2><p>Saudi Arabia\'s Giga-Projects — NEOM, The Line, Diriyah Gate, and others — operate under a rigorous vendor pre-qualification framework mandated by the Project Management Office (PMO). This article explains how Qimta simplifies compliance for procurement teams.</p><h2>Required Certifications</h2><ul><li>ISO 9001:2015 Quality Management System</li><li>Saudi Standards, Metrology and Quality Organization (SASO) product certification</li><li>Zakat, Tax and Customs Authority (ZATCA) e-invoicing compliance</li><li>Ministry of Commerce commercial registration</li></ul><h2>Qimta\'s Automated Verification Engine</h2><p>Qimta connects to the SASO portal and ZATCA APIs in real time to validate supplier certifications before they are permitted to bid on any project-linked purchase order. Expired certificates are automatically flagged and the supplier is notified 30 days before expiry.</p><h2>Audit Trail Management</h2><p>Every verification action is logged with a timestamp, user ID, and document version number. The resulting audit trail meets the requirements of Saudi Vision 2030\'s National Transformation Program for public sector procurement transparency.</p>',
                'desc_ar'  => '<h2>نظرة عامة</h2><p>تعمل المشاريع العملاقة السعودية — نيوم والخط وبوابة الدرعية وغيرها — وفق إطار صارم للتأهيل المسبق للموردين. تشرح هذه المقالة كيف تبسّط قيمتا الامتثال لفرق المشتريات.</p><h2>الشهادات المطلوبة</h2><ul><li>نظام إدارة الجودة ISO 9001:2015</li><li>شهادة منتج هيئة المواصفات والمقاييس والجودة السعودية</li><li>امتثال الفوترة الإلكترونية لهيئة الزكاة والضريبة والجمارك</li></ul><h2>محرك التحقق الآلي من قيمتا</h2><p>تتصل قيمتا ببوابة الهيئة وواجهات برمجة هيئة الزكاة في الوقت الفعلي للتحقق من شهادات الموردين قبل السماح لهم بتقديم العروض.</p>',
            ],
            // 5
            [
                'name_en'  => 'Technology',
                'name_ar'  => 'التكنولوجيا',
                'title_en' => 'How AI is Transforming BOQ Pricing in 2026',
                'title_ar' => 'كيف يُحوِّل الذكاء الاصطناعي تسعير جداول الكميات في 2026',
                'image'    => $images[4],
                'active'   => true,
                'desc_en'  => '<h2>The Pricing Problem in Construction</h2><p>Accurate BOQ pricing has historically required weeks of manual effort — cross-referencing supplier catalogues, requesting quotations, and reconciling currency fluctuations. AI changes this equation fundamentally.</p><h2>How Qimta\'s AI Engine Works</h2><p>The Qimta pricing engine uses a Retrieval-Augmented Generation (RAG) model trained on three years of regional transaction data. Given a BOQ line item, it retrieves the top five supplier offers matching the exact specification, then ranks them by total landed cost including delivery, VAT, and compliance fees.</p><h2>Accuracy Benchmarks</h2><p>In independent testing across 47 completed projects, Qimta\'s AI pricing achieved a <strong>94.7% accuracy rate</strong> against final project cost — outperforming the industry average of 78.2% for manual estimation.</p><h2>Continuous Learning</h2><p>Every completed transaction feeds back into the model, improving price predictions for similar items in subsequent BOQs. Projects executed through Qimta benefit from an ever-improving pricing baseline.</p><h2>Human-in-the-Loop Controls</h2><p>Procurement managers retain full override capability. Any AI-suggested price can be manually adjusted, and the reason for adjustment is captured for future model training.</p>',
                'desc_ar'  => '<h2>مشكلة التسعير في البناء</h2><p>تطلّب تسعير جداول الكميات تاريخياً أسابيع من الجهد اليدوي. يُغيِّر الذكاء الاصطناعي هذه المعادلة جذرياً.</p><h2>كيف يعمل محرك الذكاء الاصطناعي في قيمتا</h2><p>يستخدم محرك تسعير قيمتا نموذج RAG المدرَّب على بيانات المعاملات الإقليمية لمدة ثلاث سنوات.</p><h2>معايير الدقة</h2><p>حقق تسعير الذكاء الاصطناعي في قيمتا <strong>دقة 94.7%</strong> في الاختبارات المستقلة مقارنةً بمتوسط صناعي يبلغ 78.2%.</p>',
            ],
            // 6
            [
                'name_en'  => 'Sustainability',
                'name_ar'  => 'الاستدامة',
                'title_en' => 'Green Procurement: Sourcing Sustainable Building Materials',
                'title_ar' => 'المشتريات الخضراء: الحصول على مواد بناء مستدامة',
                'image'    => $images[5],
                'active'   => true,
                'desc_en'  => '<h2>Why Green Procurement Matters</h2><p>The construction industry accounts for approximately 39% of global carbon emissions. Procurement decisions — particularly material sourcing — are one of the highest-leverage points for reducing embodied carbon in built assets.</p><h2>Key Green Material Categories</h2><ul><li><strong>Low-carbon concrete:</strong> Supplementary cementitious materials (SCMs) like fly ash and GGBS reduce cement content by up to 60%</li><li><strong>Recycled steel:</strong> Electric arc furnace (EAF) steel carries roughly 75% lower embodied carbon than blast furnace equivalents</li><li><strong>Certified timber:</strong> FSC-certified structural timber from sustainably managed forests</li><li><strong>Low-VOC finishes:</strong> Paints and coatings meeting LEED v4.1 indoor air quality standards</li></ul><h2>Qimta\'s Sustainability Filters</h2><p>The Qimta catalogue includes an Environmental Product Declaration (EPD) filter. Procurement teams can restrict searches to materials with verified third-party EPDs, ensuring embodied carbon data is available for project-level lifecycle assessments.</p>',
                'desc_ar'  => '<h2>أهمية المشتريات الخضراء</h2><p>تُسهم صناعة البناء بنحو 39% من انبعاثات الكربون العالمية. قرارات المشتريات هي أحد أعلى النقاط تأثيراً لتقليل الكربون المتضمَّن في الأصول المبنية.</p><h2>فئات المواد الخضراء الرئيسية</h2><ul><li><strong>الخرسانة المنخفضة الكربون</strong></li><li><strong>الفولاذ المُعاد تدويره</strong></li><li><strong>الأخشاب المعتمدة</strong></li></ul><h2>مرشحات الاستدامة في قيمتا</h2><p>يتضمن كتالوج قيمتا مرشح إعلانات المنتجات البيئية للتحقق من بيانات الكربون.</p>',
            ],
            // 7
            [
                'name_en'  => 'Finance',
                'name_ar'  => 'المالية',
                'title_en' => 'Payment Protection and Escrow in Construction Procurement',
                'title_ar' => 'حماية المدفوعات والضمان في مشتريات البناء',
                'image'    => $images[6],
                'active'   => true,
                'desc_en'  => '<h2>The Payment Risk Problem</h2><p>Late and disputed payments are endemic to the construction industry. A 2025 Saudi PMO report found that 68% of subcontractors experienced payment delays exceeding 45 days on government-adjacent projects.</p><h2>How Escrow Protects All Parties</h2><p>Qimta\'s integrated escrow service holds buyer funds in a regulated trust account upon purchase order issuance. Funds are released to the supplier only upon confirmed delivery and sign-off from the site supervisor — eliminating payment default risk for both parties.</p><h2>Supported Payment Methods</h2><ul><li>SARIE instant bank transfers (Saudi Central Bank)</li><li>Corporate VISA/Mastercard virtual cards</li><li>30/60/90-day invoice financing via Qimta\'s banking partners</li></ul><h2>Dispute Resolution</h2><p>In the event of a delivery dispute, both parties submit evidence through the Qimta platform. An independent adjudicator reviews the case within 5 business days and issues a binding payment decision.</p>',
                'desc_ar'  => '<h2>مشكلة مخاطر الدفع</h2><p>التأخر في الدفع والنزاعات المتعلقة به متوطّنة في قطاع البناء. وجد تقرير هيئة المشاريع السعودية لعام 2025 أن 68% من المقاولين من الباطن عانوا من تأخيرات تجاوزت 45 يوماً.</p><h2>كيف تحمي خدمة الضمان جميع الأطراف</h2><p>تحتفظ خدمة الضمان المتكاملة في قيمتا بأموال المشتري في حساب ائتماني مُنظَّم حتى التأكيد على الاستلام.</p>',
            ],
            // 8
            [
                'name_en'  => 'Technology',
                'name_ar'  => 'التكنولوجيا',
                'title_en' => 'Real-Time Inventory Sync: Eliminating Stock-Out Delays',
                'title_ar' => 'مزامنة المخزون في الوقت الفعلي: القضاء على تأخيرات نفاد المخزون',
                'image'    => $images[7],
                'active'   => true,
                'desc_en'  => '<h2>The Cost of Stock-Outs on Construction Sites</h2><p>A single stock-out event can halt an entire work front. With daily site labour costs averaging SAR 85,000 for mid-size projects, even a 24-hour material delay translates to substantial unrecoverable cost.</p><h2>Qimta\'s Real-Time Inventory Architecture</h2><p>Qimta maintains live inventory data from over 340 approved suppliers across Saudi Arabia. Stock levels are updated via webhook every 15 minutes. When a material\'s available quantity drops below the project\'s buffer threshold, the platform automatically triggers a replenishment purchase order.</p><h2>Buffer Stock Calculation</h2><p>The buffer threshold is calculated using the Wilson formula, adjusted for regional lead time variability. Procurement managers can override the calculated buffer for critical materials flagged as high-risk.</p><h2>Substitution Engine</h2><p>When a preferred SKU is out of stock, Qimta\'s substitution engine suggests technically equivalent alternatives based on specification matching, price proximity (within ±5%), and supplier compliance score.</p>',
                'desc_ar'  => '<h2>تكلفة نفاد المخزون في مواقع البناء</h2><p>يمكن لحدث نفاد مخزون واحد أن يوقف جبهة عمل كاملة. مع متوسط تكاليف العمالة اليومية البالغة 85,000 ريال للمشاريع متوسطة الحجم.</p><h2>بنية المخزون الفوري في قيمتا</h2><p>تحتفظ قيمتا ببيانات المخزون الحية من أكثر من 340 موردًا معتمدًا في السعودية، تُحدَّث كل 15 دقيقة.</p>',
            ],
            // 9
            [
                'name_en'  => 'Guides',
                'name_ar'  => 'أدلة',
                'title_en' => 'Setting Up Your First Project on Qimta: A Step-by-Step Guide',
                'title_ar' => 'إعداد أول مشروع لك على قيمتا: دليل خطوة بخطوة',
                'image'    => $images[8],
                'active'   => true,
                'desc_en'  => '<h2>Before You Begin</h2><p>To create a project on Qimta, you will need an active enterprise account, at least one verified site location, and your project\'s master schedule in PDF or MS Project format.</p><h2>Step 1: Create the Project Record</h2><p>Navigate to Projects → New Project. Enter the project name, client name, site GPS coordinates, and expected start and completion dates. Select the applicable project type: Residential, Commercial, Infrastructure, or Industrial.</p><h2>Step 2: Upload the Master Schedule</h2><p>Import your MS Project (.mpp) or Primavera P6 (.xer) file. Qimta parses the schedule and creates procurement milestones aligned with each construction phase.</p><h2>Step 3: Assign Procurement Roles</h2><p>Add team members and assign roles: Project Manager, Quantity Surveyor, Finance Approver, and Site Supervisor. Each role has pre-configured approval permissions within the procurement workflow.</p><h2>Step 4: Link Your BOQ</h2><p>Upload or create your Bill of Quantities directly within the project. Qimta auto-prices items using the live index engine and presents three cost scenarios: Conservative, Market Rate, and Aggressive.</p>',
                'desc_ar'  => '<h2>قبل البدء</h2><p>لإنشاء مشروع على قيمتا، ستحتاج إلى حساب مؤسسي نشط وموقع مُتحقَّق منه وجدول المشروع الرئيسي.</p><h2>الخطوة 1: إنشاء سجل المشروع</h2><p>انتقل إلى المشاريع ← مشروع جديد. أدخل اسم المشروع والعميل والإحداثيات الجغرافية وتواريخ البدء والانتهاء المتوقعة.</p><h2>الخطوة 2: رفع الجدول الرئيسي</h2><p>استورد ملف MS Project أو Primavera P6. تقوم قيمتا بتحليل الجدول وإنشاء معالم المشتريات.</p>',
            ],
            // 10
            [
                'name_en'  => 'Market Analysis',
                'name_ar'  => 'تحليل السوق',
                'title_en' => 'Saudi Rebar Prices Q1 2026: Trends and Forecast',
                'title_ar' => 'أسعار حديد التسليح السعودي الربع الأول 2026: الاتجاهات والتوقعات',
                'image'    => $images[9],
                'active'   => true,
                'desc_en'  => '<h2>Q1 2026 Market Summary</h2><p>Saudi rebar (ASTM A615 Grade 60) averaged SAR 2,840 per tonne in Q1 2026 — a 6.2% increase over Q4 2025, driven by tightening domestic supply amid record Giga-Project demand and reduced import volumes from Turkey and China.</p><h2>Key Price Drivers</h2><ul><li><strong>Domestic demand surge:</strong> NEOM Phase 2 civil works mobilized 14 new concrete-intensive work fronts in January 2026</li><li><strong>Energy cost pass-through:</strong> Saudi Electricity Company tariff revisions increased EAF operating costs by approximately SAR 45/tonne</li><li><strong>Import compression:</strong> Red Sea shipping disruptions added USD 28/tonne to CIF Saudi port costs for imported rebar</li></ul><h2>Regional Price Comparison (March 2026)</h2><table><thead><tr><th>Country</th><th>Price (USD/tonne)</th><th>Change QoQ</th></tr></thead><tbody><tr><td>Saudi Arabia</td><td>757</td><td>+6.2%</td></tr><tr><td>UAE</td><td>729</td><td>+4.1%</td></tr><tr><td>Turkey (export)</td><td>641</td><td>-1.8%</td></tr><tr><td>China (export)</td><td>598</td><td>-3.2%</td></tr></tbody></table><h2>Q2 2026 Forecast</h2><p>Qimta\'s market intelligence team projects a 2–4% price moderation in Q2 2026 as additional domestic capacity from Hadeed\'s Jubail expansion comes online in May. Procurement teams are advised to consider forward contracts for volumes exceeding 500 tonnes.</p>',
                'desc_ar'  => '<h2>ملخص السوق في الربع الأول 2026</h2><p>بلغ متوسط سعر حديد التسليح السعودي 2,840 ريال للطن في الربع الأول من 2026، بزيادة 6.2% عن الربع الرابع من 2025.</p><h2>محركات الأسعار الرئيسية</h2><ul><li><strong>ارتفاع الطلب المحلي:</strong> حرّك المرحلة الثانية من نيوم 14 جبهة عمل جديدة</li><li><strong>ارتفاع تكاليف الطاقة</strong></li><li><strong>انكماش الاستيراد</strong></li></ul><h2>توقعات الربع الثاني 2026</h2><p>يتوقع فريق استخبارات السوق في قيمتا تراجعاً في الأسعار بنسبة 2-4% مع دخول طاقة إنتاجية جديدة من توسع حديد الجبيل.</p>',
            ],
        ];

        foreach ($articles as $data) {
            Article::create($data);
        }

        $this->command->info('ArticleSeeder: 10 articles seeded successfully.');
    }
}