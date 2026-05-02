<?php

return [

    // ── HERO ─────────────────────────────────────────────────────────────────
    'hero' => [
        'label'    => 'Support Center',
        'h1'       => 'Frequently Asked Questions',
        'h1_ar'    => 'الأسئلة الشائعة',
        'sub'      => 'Get instant technical insights on our enterprise RAG pricing engine, BOQ processing, and the largest construction catalog in the region.',
        'search'   => 'Search questions...',
    ],

    // ── TABS ─────────────────────────────────────────────────────────────────
    'tabs' => [
        'pricing'   => 'Pricing',
        'catalog'   => 'Catalog',
        'boq'       => 'BOQ Upload',
        'rag'       => 'RAG Engine',
        'ordering'  => 'Ordering',
        'brands'    => 'Brands',
        'security'  => 'Security',
        'account'   => 'Account',
    ],

    // ── PRICING ──────────────────────────────────────────────────────────────
    'pricing' => [
        'title' => 'Pricing & Costs',
        'q1' => 'What does Qimta cost?',
        'a1' => 'Qimta is free for buyers. There are no subscription fees, no platform access fees, and no per-BOQ pricing. We monetize through brand partnerships, ensuring the best tools remain accessible to project owners and contractors.',
        'q2' => 'Are there any hidden fees for large BOQ submissions?',
        'a2' => 'No. You can submit BOQs of any size without incurring additional fees. Our pricing model is designed to scale with your project needs at no extra cost to buyers.',
        'q3' => 'How do brands pay for access to Qimta?',
        'a3' => 'Brands subscribe to verified listing plans that give them visibility to qualified buyers. This is completely separate from the buyer experience and does not affect the catalog data you see.',
    ],

    // ── CATALOG ──────────────────────────────────────────────────────────────
    'catalog' => [
        'title' => 'Indexed Catalog',
        'q1' => 'How many products are in the catalog?',
        'a1' => 'Qimta currently indexes over 418,000 products across structural, architectural, mechanical, electrical, and finishing categories. The catalog is updated continuously as brands publish new technical data.',
        'q2' => 'Why are prices hidden until registration?',
        'a2' => 'To maintain the integrity of commercial agreements between brands and verified buyers, individual SKU pricing and technical data sheets are behind our secure registration wall. We currently index over 418,000 architectural and technical specifications.',
        'q3' => 'How often is the catalog updated?',
        'a3' => 'The catalog is updated in real time as brands publish or revise product data. Major catalog sweeps happen quarterly, ensuring discontinued products are removed and new SKUs are indexed within 48 hours of submission.',
    ],

    // ── BOQ ──────────────────────────────────────────────────────────────────
    'boq' => [
        'title' => 'BOQ Upload & Processing',
        'q1' => 'Which file formats do you support?',
        'a1' => 'Qimta supports Excel (.xlsx, .xls), CSV, and PDF BOQ files. Our AI engine can parse structured tables, multi-sheet workbooks, and scanned PDF documents using OCR extraction.',
        'q2' => 'Can Qimta handle mixed units and custom line items?',
        'a2' => 'Yes. Our RAG engine normalises units automatically (m, m2, m3, kg, pcs, sets, etc.) and handles custom or non-standard descriptions by matching them against the nearest catalog equivalent with a confidence score.',
        'q3' => 'How long does BOQ processing take?',
        'a3' => 'Standard BOQs with up to 200 line items are processed in under 60 seconds. Larger BOQs with complex specifications may take up to 3 minutes. You will receive a notification when results are ready.',
    ],

    // ── RAG ──────────────────────────────────────────────────────────────────
    'rag' => [
        'title'       => 'RAG Pricing Engine',
        'card_title'  => 'Retrieval-Augmented Generation (RAG) Security',
        'card_body'   => 'Our engine processes complex BOQs in under 60 seconds by retrieving real data points from our verified database. Unlike standard LLMs, Qimta does not "hallucinate" or invent prices; it matches your line items against physical catalog data with 99.9% accuracy, requiring no human review for standard items.',
        'q1' => 'Does Qimta use AI to generate prices?',
        'a1' => 'No. Qimta uses Retrieval-Augmented Generation (RAG) — meaning prices come directly from our verified catalog database, not from AI inference. The AI component handles matching and normalisation, not price generation.',
        'q2' => 'What happens when no exact match is found?',
        'a2' => 'When an exact match is not found, the engine returns the nearest semantic match with a confidence score. Items below the confidence threshold are flagged for manual review so your team can validate before committing.',
    ],

    // ── ORDERING ─────────────────────────────────────────────────────────────
    'ordering' => [
        'title' => 'Ordering & Procurement',
        'q1' => 'Can I place orders directly through Qimta?',
        'a1' => 'Yes. Once your BOQ is priced and approved, you can submit a procurement request directly to verified suppliers through the platform. Orders are tracked end-to-end within your project dashboard.',
        'q2' => 'How are delivery timelines communicated?',
        'a2' => 'Suppliers provide lead time estimates when they respond to a procurement request. You can track logistics updates, including dispatch and delivery confirmation, in the Logistics section of your project.',
    ],

    // ── BRANDS ───────────────────────────────────────────────────────────────
    'brands' => [
        'title' => 'Brand Listings & Data',
        'q1' => 'How can my brand get listed on Qimta?',
        'a1' => 'Brands apply through our partner onboarding portal. After verification, your product catalog is indexed and made visible to buyers submitting BOQs that match your product categories.',
        'q2' => 'Can brands update their pricing in real time?',
        'a2' => 'Yes. Brands with active subscriptions can update pricing, technical specifications, and product availability through the Supplier Dashboard. Changes are reflected in the catalog within 24 hours.',
    ],

    // ── SECURITY ─────────────────────────────────────────────────────────────
    'security' => [
        'title'        => 'Data Security & Compliance',
        'card1_title'  => 'PDPL & GDPR',
        'card1_body'   => 'Full compliance with Saudi Personal Data Protection Law and GDPR standards.',
        'card2_title'  => 'Data Isolation',
        'card2_body'   => 'Each enterprise client has a dedicated logical partition ensuring no cross-contamination of project data.',
        'q1' => 'Is my BOQ data shared with other users or brands?',
        'a1' => 'Never. Your BOQ data is processed in a fully isolated environment. Brands cannot see your specific line items, quantities, or project details. Only aggregated, anonymised demand signals are used for catalog improvement.',
        'q2' => 'Where is data stored?',
        'a2' => 'All data is stored on AWS infrastructure in the Middle East (Bahrain) region, ensuring compliance with Saudi data residency requirements. Backups are encrypted and geo-replicated.',
    ],

    // ── ACCOUNT ──────────────────────────────────────────────────────────────
    'account' => [
        'title' => 'Account & Access',
        'q1' => 'How do I create a team account?',
        'a1' => 'After registering, navigate to Settings → Team Management. You can invite team members by email and assign roles (Viewer, Editor, Approver) to control access to project and BOQ data.',
        'q2' => 'Can I export my project history?',
        'a2' => 'Yes. All BOQ history, pricing snapshots, and procurement records can be exported as Excel or PDF from the Reports section of your dashboard.',
    ],

    // ── CTA ───────────────────────────────────────────────────────────────────
    'cta' => [
        'title'   => 'Still have questions?',
        'sub'     => 'Our technical sales team is ready to assist with enterprise integrations and custom catalog requests.',
        'btn1'    => 'Price a BOQ — Free',
        'btn2'    => 'Contact Qimta',
    ],

];
