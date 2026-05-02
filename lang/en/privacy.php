<?php

return [
    'title'        => 'Privacy Policy',
    'title_ar'     => 'سياسة الخصوصية',
    'last_updated' => 'LAST UPDATED: APRIL 2026',

    'nav' => [
        'collect'   => 'Information we collect',
        'boq'       => 'BOQ and project data',
        'account'   => 'Account data',
        'analytics' => 'Usage analytics',
        'use'       => 'How we use data',
        'protect'   => 'How we protect data',
        'sharing'   => 'Data sharing',
        'retention' => 'Data retention',
        'rights'    => 'User rights',
        'pdpl'      => 'PDPL and GDPR alignment',
        'contact'   => 'Contact for privacy requests',
    ],

    'collect' => [
        'h2'  => 'Information we collect',
        'p1'  => 'Qimta Legal operates as a high-integrity technical platform for enterprise legal and architectural data. We collect information that is necessary to provide our professional services, ensure platform security, and maintain the accuracy of legal documentation.',
        'p2'  => 'This includes information provided directly by you, information automatically collected during platform use, and data derived from technical project inputs.',
    ],

    'boq' => [
        'h2'    => 'BOQ and project data',
        'intro' => 'As a technical platform, the primary data we process involves Bill of Quantities (BOQ) and structural project specifications. This data is classified as high-integrity business intelligence.',
        'li1'   => 'Raw architectural data and material specifications.',
        'li2'   => 'Pricing structures and historical bidding information.',
        'li3'   => 'Legal annotations related to specific project line items.',
        'li4'   => 'Version history and collaborative technical edits.',
        'note'  => 'All project data is encrypted at rest and in transit, isolated by enterprise tenant boundaries.',
    ],

    'account' => [
        'h2'    => 'Account data',
        'intro' => 'To access Qimta Legal, users must provide verified enterprise credentials. We collect and store:',
        'li1'   => 'Full legal name and professional title.',
        'li2'   => 'Corporate email address and organizational affiliation.',
        'li3'   => 'Two-factor authentication metadata for security auditing.',
    ],

    'analytics' => [
        'h2' => 'Usage analytics',
        'p'  => 'We monitor technical performance and interaction patterns to maintain the platform\'s "Sharp" performance standard. This includes IP addresses, browser technical strings, and interaction logs within the legal workspace. This data is used solely for system optimization and security forensics.',
    ],

    'use' => [
        'h2'         => 'How we use data',
        'intro'      => 'Qimta Legal uses your data for the following strictly technical purposes:',
        'op_title'   => 'Operational',
        'op_body'    => 'Provisioning of BOQ analysis tools and legal document generation.',
        'assur_title'=> 'Assurance',
        'assur_body' => 'Maintaining audit trails for legal compliance and professional accountability.',
    ],

    'protect' => [
        'h2'    => 'How we protect data',
        'intro' => 'Our security architecture is designed for zero-trust environments. Protection measures include:',
        'li1'   => 'AES-256 encryption for all stored project datasets.',
        'li2'   => 'Strict Logical Access Control (LAC) based on the principle of least privilege.',
        'li3'   => 'Real-time threat monitoring and automated incident response protocols.',
        'li4'   => 'Regular third-party security audits and penetration testing.',
    ],

    'sharing' => [
        'h2'    => 'Data sharing',
        'intro' => 'Qimta does not sell, trade, or monetize project or account data. Data sharing is strictly limited to:',
        'li1'   => 'Technical sub-processors required for cloud infrastructure (AWS/Azure/Google Cloud).',
        'li2'   => 'Legal authorities where required by mandatory KSA or international law.',
        'li3'   => 'Authorized collaborators within your specific project environment.',
    ],

    'retention' => [
        'h2' => 'Data retention',
        'p'  => 'We retain legal and project data for the duration of the professional engagement and for a statutory period thereafter (typically 10 years for structural legal records) to satisfy regulatory requirements in the Kingdom of Saudi Arabia and applicable international jurisdictions.',
    ],

    'rights' => [
        'h2'    => 'User rights',
        'intro' => 'Under Qimta\'s global governance framework, users maintain the right to:',
        'li1'   => 'Request a machine-readable export of their technical account data.',
        'li2'   => 'Rectify inaccurate project metadata.',
        'li3'   => 'Request data deletion (Right to be Forgotten), subject to prevailing legal retention mandates.',
        'li4'   => 'Withdraw consent for optional usage analytics.',
    ],

    'pdpl' => [
        'h2'   => 'PDPL and GDPR alignment',
        'p'    => 'Qimta Legal is engineered for strict compliance with the Saudi Arabian :pdpl and the European :gdpr. Our data processing agreements reflect the highest standards of international data sovereignty.',
        'pdpl' => 'Personal Data Protection Law (PDPL)',
        'gdpr' => 'General Data Protection Regulation (GDPR)',
    ],

    'contact' => [
        'h2'       => 'Contact for privacy requests',
        'office'   => 'Technical Data Office',
        'body'     => 'For all privacy-related inquiries, data access requests, or compliance questions, please contact our Data Protection Officer.',
        'email'    => 'privacy@qimta.legal',
        'location' => 'Riyadh Technical District, KSA',
    ],
];
