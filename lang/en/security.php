<?php

return [

    // ── HERO ─────────────────────────────────────────────────────────────────
    'hero' => [
        'label'       => 'Enterprise Security',
        'h1'          => 'Security built for construction procurement data.',
        'sub'         => 'Qimta protects BOQs, project data, account data, and catalog workflows with enterprise-grade controls. We ensure your proprietary pricing and material data remains confidential.',
        'btn_talk'    => 'Talk to Security Team',
        'btn_paper'   => 'Download Whitepaper',
        'card_title'  => 'Data Encryption Protocol',
        'card_id'     => 'SEC-VER-04',
        'row1_label'  => 'In-Transit',
        'row1_value'  => 'TLS 1.3 / AES-256',
        'row2_label'  => 'At-Rest',
        'row2_value'  => 'FIPS 140-2 Compliant',
        'row3_label'  => 'Key Mgmt',
        'row3_value'  => 'AWS KMS / HSM',
        'card_quote'  => '"All Bill of Quantities (BOQ) uploads are automatically hashed and isolated at the project tenant level."',
    ],

    // ── PILLARS ───────────────────────────────────────────────────────────────
    'pillars' => [
        'label' => 'Core Security Pillars',
        'sub'   => 'Every layer of the Qimta infrastructure is hardened to prevent unauthorized access and ensure total data sovereignty.',
        'p1_title' => 'Project-level data isolation',
        'p1_desc'  => 'Logical separation of data between accounts ensures your project details never leak across organization boundaries.',
        'p2_title' => 'Account protection',
        'p2_desc'  => 'Multi-factor authentication (MFA) and Single Sign-On (SSO) integration for enterprise identity management.',
        'p3_title' => 'Secure BOQ handling',
        'p3_desc'  => 'Encrypted processing pipelines for Bill of Quantities, ensuring sensitive line-item pricing remains confidential.',
        'p4_title' => 'Access control',
        'p4_desc'  => 'Granular Role-Based Access Control (RBAC) allows you to define who can view, edit, or approve procurement data.',
        'p5_title' => 'Encryption',
        'p5_desc'  => 'End-to-end encryption for all sensitive files. Data is encrypted at rest using AES-256 and in transit via TLS 1.3.',
        'p6_title' => 'Monitoring',
        'p6_desc'  => '24/7 security operations center (SOC) monitoring with real-time threat detection and incident response protocols.',
    ],

    // ── COMPLIANCE ────────────────────────────────────────────────────────────
    'compliance' => [
        'title' => 'Regulatory Compliance',
        'sub'   => 'We adhere to the highest international data protection standards to support global operations.',
        'c1_title' => 'PDPL-compliant',
        'c1_desc'  => "Fully aligned with Saudi Arabia's Personal Data Protection Law (PDPL).",
        'c2_title' => 'GDPR-aligned',
        'c2_desc'  => 'Strict adherence to European Union data privacy and portability requirements.',
        'c3_title' => 'DPA Available',
        'c3_desc'  => 'Data Processing Agreements (DPA) available for enterprise legal requirements.',
    ],

    // ── LIFECYCLE ─────────────────────────────────────────────────────────────
    'lifecycle' => [
        'title'  => 'Secure Data Processing Lifecycle',
        'sub'    => 'How Qimta handles your BOQ data from upload to retrieval.',
        's1_label' => 'Step 01',
        's1_title' => 'BOQ upload',
        's1_desc'  => 'Encrypted TLS 1.3 Channel',
        's2_label' => 'Step 02',
        's2_title' => 'secure processing',
        's2_desc'  => 'Isolated Sandbox Environment',
        's3_label' => 'Step 03',
        's3_title' => 'RAG retrieval',
        's3_desc'  => 'Private Vector Embeddings',
        's4_label' => 'Step 04',
        's4_title' => 'priced response',
        's4_desc'  => 'Verified Catalog Matching',
        'sc_label' => 'Complete',
        'sc_title' => 'account history',
        'sc_desc'  => 'Immutable Audit Trail',
    ],

    // ── FAQ ───────────────────────────────────────────────────────────────────
    'faq' => [
        'title' => 'Security FAQ',
        'sub'   => 'Specific technical answers for your compliance teams.',
        'q1'    => 'Is my BOQ data secure?',
        'a1'    => 'Yes. Every BOQ uploaded is encrypted and processed within a single-tenant logical environment. Qimta uses industrial-strength hashing to ensure that your specific pricing remains private.',
        'q2'    => 'Are projects isolated between accounts?',
        'a2'    => 'Yes. Each account operates in a fully isolated data context. There is no cross-tenant data sharing at any layer of the infrastructure.',
        'q3'    => 'Can enterprise clients request a DPA?',
        'a3'    => 'Yes. We offer Data Processing Agreements (DPAs) for enterprise clients that require formal contractual data protection guarantees.',
        'q4'    => 'How does Qimta protect account access?',
        'a4'    => 'We support MFA, SSO integrations, and session-based access tokens. All authentication events are logged in the immutable audit trail.',
        'q5'    => 'How is BOQ data handled?',
        'a5'    => 'BOQ files are encrypted in transit via TLS 1.3 and at rest via AES-256. Each file is processed in an isolated sandbox and is never shared with other tenants.',
    ],

    // ── CTA ───────────────────────────────────────────────────────────────────
    'cta' => [
        'label'  => 'Talk to our team about security.',
        'sub'    => 'Need to complete a security questionnaire or review our compliance reports? Our security specialists are ready to help.',
        'btn'    => 'Request Security Pack',
    ],

];
