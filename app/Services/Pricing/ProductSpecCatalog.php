<?php

namespace App\Services\Pricing;

/**
 * Pricing-specification catalog for the supported product families.
 *
 * For each family this records the default unit, the specs that materially move
 * the market price (BLOCKING — never priced without them), the specs worth asking
 * about only when the project context makes them relevant (CONDITIONAL), and the
 * cross-item checks the project-level pass should run.
 *
 * The engine feeds the matched entry into the AI prompt so the model audits
 * against a fixed rubric instead of improvising a different one per call. Keys
 * are matched against the item description by keyword, so a family only needs
 * the terms a BOQ would realistically use, in English and Arabic.
 */
final class ProductSpecCatalog
{
    /**
     * Resolve the catalog entry for a description, or null when nothing matches.
     * Longest keyword wins, so "network switch" beats a bare "switch".
     */
    public static function match(string $description): ?array
    {
        $haystack = mb_strtolower(trim($description));
        if ($haystack === '') {
            return null;
        }

        $best     = null;
        $bestLen  = 0;

        foreach (self::all() as $key => $entry) {
            foreach ($entry['keywords'] as $kw) {
                $kw = mb_strtolower($kw);
                if ($kw !== '' && mb_strpos($haystack, $kw) !== false && mb_strlen($kw) > $bestLen) {
                    $bestLen = mb_strlen($kw);
                    $best    = ['key' => $key] + $entry;
                }
            }
        }

        return $best;
    }

    /**
     * The full catalog.
     *
     * @return array<string, array{
     *   keywords: list<string>,
     *   unit: string,
     *   blocking: list<string>,
     *   conditional: list<string>,
     *   checks: list<string>
     * }>
     */
    public static function all(): array
    {
        return array_merge(
            self::itAndNetworking(),
            self::securityAndAv(),
            self::electrical(),
            self::hvac(),
            self::plumbingAndFire(),
            self::furnitureAndInterior(),
            self::constructionMaterials(),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IT, computers and networking
    // ─────────────────────────────────────────────────────────────────────────

    private static function itAndNetworking(): array
    {
        return [
            'laptop' => [
                'keywords'    => ['laptop', 'notebook', 'لابتوب', 'حاسب محمول', 'حاسوب محمول'],
                'unit'        => 'PCS',
                'blocking'    => ['intended use', 'processor model and generation', 'RAM capacity', 'storage type and capacity', 'screen size', 'operating system', 'warranty period'],
                'conditional' => ['dedicated or integrated GPU', 'touchscreen', 'Arabic keyboard', 'docking station', 'rugged construction'],
                'checks'      => ['quantity vs number of users', 'GPU/RAM vs required software', 'docking stations and external monitors required?'],
            ],
            'desktop' => [
                'keywords'    => ['desktop computer', 'desktop pc', 'كمبيوتر مكتبي', 'حاسب مكتبي'],
                'unit'        => 'PCS',
                'blocking'    => ['form factor', 'processor model and generation', 'RAM', 'SSD/HDD configuration', 'operating system', 'warranty'],
                'conditional' => ['GPU', 'power supply', 'keyboard and mouse'],
                'checks'      => ['quantity vs monitor quantity', 'configuration vs user workload'],
            ],
            'workstation' => [
                'keywords'    => ['workstation', 'محطة عمل هندسية', 'ورك ستيشن'],
                'unit'        => 'PCS',
                'blocking'    => ['engineering software to be used', 'CPU model and core count', 'RAM (ECC or non-ECC)', 'professional GPU model', 'storage configuration', 'warranty'],
                'conditional' => ['software certification (ISV)', 'power supply rating'],
                'checks'      => ['configuration vs AutoCAD/Revit/BIM/rendering/GIS requirements'],
            ],
            'monitor' => [
                'keywords'    => ['monitor', 'display screen', 'شاشة كمبيوتر', 'شاشة عرض'],
                'unit'        => 'PCS',
                'blocking'    => ['screen size', 'resolution', 'panel type', 'ports', 'warranty'],
                'conditional' => ['refresh rate', 'height adjustment', 'colour accuracy', 'touch capability', 'built-in speakers'],
                'checks'      => ['monitor count vs computer count', 'ports vs computer outputs'],
            ],
            'aio' => [
                'keywords'    => ['all-in-one', 'all in one computer', 'كمبيوتر متكامل'],
                'unit'        => 'PCS',
                'blocking'    => ['screen size', 'processor', 'RAM', 'SSD', 'operating system', 'warranty'],
                'conditional' => ['touch or non-touch', 'webcam and microphone'],
                'checks'      => [],
            ],
            'server' => [
                'keywords'    => ['server', 'سيرفر', 'خادم'],
                'unit'        => 'PCS',
                'blocking'    => ['server workload', 'rack or tower type', 'processor quantity/model/cores', 'ECC RAM', 'disk type/capacity/quantity', 'network interfaces', 'operating system and licences', 'warranty and support'],
                'conditional' => ['RAID controller', 'redundant power supply'],
                'checks'      => ['rack depth and available U space', 'UPS capacity', 'storage/backup/network requirements'],
            ],
            'nas' => [
                'keywords'    => ['nas', 'network attached storage', 'وحدة تخزين شبكية'],
                'unit'        => 'PCS',
                'blocking'    => ['number of drive bays', 'required usable storage', 'RAID level', 'HDD or SSD', 'number of users'],
                'conditional' => ['network speed', 'backup requirements', 'warranty'],
                'checks'      => ['raw storage required to reach usable storage after RAID'],
            ],
            'external_storage' => [
                'keywords'    => ['external hard', 'external ssd', 'قرص صلب خارجي'],
                'unit'        => 'PCS',
                'blocking'    => ['HDD or SSD', 'capacity', 'interface'],
                'conditional' => ['required speed', 'encryption', 'rugged construction', 'warranty'],
                'checks'      => [],
            ],
            'printer' => [
                'keywords'    => ['printer', 'طابعة'],
                'unit'        => 'PCS',
                'blocking'    => ['mono or colour', 'laser/inkjet/thermal', 'A4 or A3', 'print speed', 'network or USB', 'monthly printing volume'],
                'conditional' => ['duplex printing', 'toner yield', 'warranty'],
                'checks'      => ['duty cycle vs monthly volume', 'starter vs full-capacity toner stated'],
            ],
            'scanner' => [
                'keywords'    => ['scanner', 'ماسح ضوئي', 'سكانر'],
                'unit'        => 'PCS',
                'blocking'    => ['A4 or A3', 'flatbed/sheet-fed/both', 'pages per minute', 'resolution', 'daily scanning volume'],
                'conditional' => ['duplex requirement', 'ADF capacity', 'network or USB'],
                'checks'      => [],
            ],
            'ups' => [
                'keywords'    => ['ups', 'uninterruptible', 'يو بي اس', 'مزود طاقة احتياطي'],
                'unit'        => 'PCS',
                'blocking'    => ['online or line-interactive', 'required kVA AND kW', 'single or three phase', 'connected load', 'required runtime', 'internal or external batteries'],
                'conditional' => ['input/output voltage', 'SNMP card', 'warranty'],
                'checks'      => ['total connected load + safety margin', 'reject sizing on kVA alone without kW and runtime'],
            ],
            'switch_managed' => [
                'keywords'    => ['managed switch', 'network switch', 'poe switch', 'سويتش مدار', 'مفتاح شبكة'],
                'unit'        => 'PCS',
                'blocking'    => ['number of ports', 'port speed', 'PoE requirement', 'PoE power budget', 'uplink quantity and type', 'Layer 2 or Layer 3'],
                'conditional' => ['stacking', 'rack or desktop mounting', 'warranty'],
                'checks'      => ['ports vs connected devices', 'PoE budget vs cameras + access points + phones'],
            ],
            'switch_unmanaged' => [
                'keywords'    => ['unmanaged switch', 'سويتش غير مدار'],
                'unit'        => 'PCS',
                'blocking'    => ['number of ports', 'port speed', 'PoE or non-PoE'],
                'conditional' => ['uplink type', 'desktop or rack mounting', 'enclosure material'],
                'checks'      => ['ports vs connected devices'],
            ],
            'router' => [
                'keywords'    => ['router', 'راوتر', 'موجه'],
                'unit'        => 'PCS',
                'blocking'    => ['internet connection type', 'WAN ports (number and type)', 'required throughput', 'number of users'],
                'conditional' => ['VPN requirement', 'LTE/5G backup', 'rack or desktop'],
                'checks'      => [],
            ],
            'firewall' => [
                'keywords'    => ['firewall', 'جدار ناري', 'فايروول'],
                'unit'        => 'PCS',
                'blocking'    => ['number of users', 'internet speed', 'threat-protection throughput', 'interfaces (number and type)', 'security subscription duration'],
                'conditional' => ['VPN users', 'high availability', 'support duration'],
                'checks'      => ['size on threat-protection throughput, never on raw firewall throughput'],
            ],
            'access_point' => [
                'keywords'    => ['access point', 'wireless ap', 'نقطة وصول', 'اكسس بوينت'],
                'unit'        => 'PCS',
                'blocking'    => ['Wi-Fi standard', 'indoor or outdoor', 'expected concurrent users', 'coverage area', 'PoE requirement'],
                'conditional' => ['controller or cloud management', 'licence duration', 'IP rating'],
                'checks'      => ['AP quantity vs project area and user density', 'PoE draw vs switch budget'],
            ],
            'wireless_controller' => [
                'keywords'    => ['wireless controller', 'wlan controller', 'متحكم لاسلكي'],
                'unit'        => 'PCS',
                'blocking'    => ['number of access points', 'compatible AP models', 'on-premise or cloud', 'licence duration'],
                'conditional' => ['high availability', 'required throughput'],
                'checks'      => ['controller capacity vs AP count'],
            ],
            'rack' => [
                'keywords'    => ['network rack', 'server rack', 'كابينة شبكة', 'راك'],
                'unit'        => 'PCS',
                'blocking'    => ['rack height in U', 'width and depth', 'floor or wall mounted', 'static load capacity'],
                'conditional' => ['door type', 'fans', 'PDU', 'cable-management accessories'],
                'checks'      => ['required U from all rack equipment', 'server and UPS depth vs rack depth'],
            ],
            'patch_panel' => [
                'keywords'    => ['patch panel', 'باتش بانل', 'لوحة توصيل'],
                'unit'        => 'PCS',
                'blocking'    => ['category (CAT6/CAT6A/fiber)', 'number of ports', 'shielded or unshielded', 'loaded or unloaded'],
                'conditional' => ['rack size in U'],
                'checks'      => ['total ports vs network outlets'],
            ],
            'data_cable' => [
                'keywords'    => ['cat6', 'cat6a', 'utp cable', 'data cable', 'كابل شبكة'],
                'unit'        => 'M',
                'blocking'    => ['cable category', 'UTP/FTP/S-FTP', 'solid copper or CCA', 'LSZH or PVC', 'indoor or outdoor', 'roll length'],
                'conditional' => ['certification', 'colour'],
                'checks'      => ['quantity vs number of points and routing + wastage', 'flag unusually low cable quantity'],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Security, CCTV, access control and AV
    // ─────────────────────────────────────────────────────────────────────────

    private static function securityAndAv(): array
    {
        return [
            'ip_camera' => [
                'keywords'    => ['ip camera', 'cctv camera', 'surveillance camera', 'security camera', 'كاميرا مراقبة', 'كاميرا'],
                'unit'        => 'PCS',
                'blocking'    => ['camera form (dome/bullet/box/PTZ/fisheye)', 'resolution', 'lens (fixed or motorised) and focal length', 'indoor or outdoor', 'IP rating', 'PoE requirement'],
                'conditional' => ['IR distance', 'IK rating', 'WDR', 'analytics', 'ONVIF compatibility', 'warranty'],
                'checks'      => ['camera count vs NVR channels', 'PoE draw vs switch budget'],
            ],
            'nvr' => [
                'keywords'    => ['nvr', 'network video recorder', 'مسجل شبكي'],
                'unit'        => 'PCS',
                'blocking'    => ['number of channels', 'maximum supported resolution', 'incoming recording bandwidth', 'number of HDD bays'],
                'conditional' => ['RAID', 'integrated PoE', 'analytics compatibility', 'warranty'],
                'checks'      => ['channels must exceed connected cameras with spare capacity'],
            ],
            'surveillance_hdd' => [
                'keywords'    => ['surveillance hdd', 'hard disk for nvr', 'قرص مراقبة'],
                'unit'        => 'PCS',
                'blocking'    => ['required retention period', 'number of cameras', 'camera resolution', 'frame rate and bitrate', 'recording mode', 'HDD capacity'],
                'conditional' => ['RAID requirement'],
                'checks'      => ['storage calculated from retention — never accept quantity without the calculation'],
            ],
            'cctv_display' => [
                'keywords'    => ['cctv monitor', 'cctv display', 'شاشة مراقبة'],
                'unit'        => 'PCS',
                'blocking'    => ['screen size', 'resolution', '24/7 operation requirement', 'input ports'],
                'conditional' => ['mounting', 'bezel width for video wall'],
                'checks'      => [],
            ],
            'access_reader' => [
                'keywords'    => ['access control reader', 'card reader', 'قارئ بطاقات'],
                'unit'        => 'PCS',
                'blocking'    => ['credential type (card/PIN/fingerprint/face)', 'card technology', 'indoor or outdoor', 'communication protocol'],
                'conditional' => ['IP rating', 'user capacity'],
                'checks'      => ['reader count vs controller capacity'],
            ],
            'access_controller' => [
                'keywords'    => ['access control panel', 'door controller', 'وحدة تحكم أبواب'],
                'unit'        => 'PCS',
                'blocking'    => ['number of doors', 'number of readers', 'communication type', 'software and licence'],
                'conditional' => ['offline storage', 'input/output requirements'],
                'checks'      => ['controller door capacity vs project doors'],
            ],
            'door_lock' => [
                'keywords'    => ['magnetic lock', 'electric strike', 'قفل كهربائي', 'قفل مغناطيسي'],
                'unit'        => 'PCS',
                'blocking'    => ['lock type', 'door material and type', 'holding force', 'fail-safe or fail-secure', 'voltage'],
                'conditional' => ['mounting brackets'],
                'checks'      => ['compatibility with door type and fire-escape requirements'],
            ],
            'exit_button' => [
                'keywords'    => ['exit button', 'زر خروج'],
                'unit'        => 'PCS',
                'blocking'    => ['button type (touch/push/no-touch)', 'indoor or outdoor'],
                'conditional' => ['material', 'back-box', 'IP rating'],
                'checks'      => [],
            ],
            'time_attendance' => [
                'keywords'    => ['time attendance', 'جهاز حضور وانصراف'],
                'unit'        => 'PCS',
                'blocking'    => ['biometric type', 'user capacity', 'log capacity', 'connectivity', 'software licence'],
                'conditional' => ['cloud or on-premise', 'integration requirement'],
                'checks'      => ['device capacity vs employee count'],
            ],
            'intercom' => [
                'keywords'    => ['intercom', 'انتركم'],
                'unit'        => 'SET',
                'blocking'    => ['IP or analogue', 'audio or video', 'number of indoor stations', 'number of entrances'],
                'conditional' => ['outdoor rating', 'mobile app', 'access-control integration'],
                'checks'      => ['all components of the set itemised'],
            ],
            'pa_speaker' => [
                'keywords'    => ['pa speaker', 'ceiling speaker', 'horn speaker', 'سماعة'],
                'unit'        => 'PCS',
                'blocking'    => ['speaker type', 'power rating', '100V line or low impedance', 'indoor or outdoor'],
                'conditional' => ['SPL', 'IP rating'],
                'checks'      => ['total speaker load vs amplifier power'],
            ],
            'amplifier' => [
                'keywords'    => ['amplifier', 'مضخم صوت', 'امبليفاير'],
                'unit'        => 'PCS',
                'blocking'    => ['output power', 'number of zones', '100V line or low impedance', 'inputs (number and type)'],
                'conditional' => ['DSP requirement'],
                'checks'      => ['amplifier power vs total speaker load + safety margin'],
            ],
            'projector' => [
                'keywords'    => ['projector', 'بروجكتر', 'جهاز عرض'],
                'unit'        => 'PCS',
                'blocking'    => ['brightness in lumens', 'resolution', 'throw distance', 'screen size', 'laser or lamp'],
                'conditional' => ['input ports', 'ceiling mount', 'warranty'],
                'checks'      => ['brightness vs room ambient light and screen size'],
            ],
            'interactive_display' => [
                'keywords'    => ['interactive display', 'smart board', 'شاشة تفاعلية'],
                'unit'        => 'PCS',
                'blocking'    => ['screen size', 'resolution', 'number of touch points', 'built-in operating system'],
                'conditional' => ['camera and microphone', 'speakers', 'OPS computer', 'warranty'],
                'checks'      => [],
            ],
            'conference_system' => [
                'keywords'    => ['conference system', 'video conference', 'نظام مؤتمرات'],
                'unit'        => 'SET',
                'blocking'    => ['room size', 'number of participants', 'microphone type', 'camera requirements', 'platform compatibility (Teams/Zoom)'],
                'conditional' => ['speaker and DSP requirements', 'control panel'],
                'checks'      => ['set contains every required component'],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Electrical and power
    // ─────────────────────────────────────────────────────────────────────────

    private static function electrical(): array
    {
        return [
            'led_panel' => [
                'keywords'    => ['led panel', 'panel light', 'لوحة إنارة', 'بانل ليد'],
                'unit'        => 'PCS',
                'blocking'    => ['dimensions', 'wattage', 'lumen output', 'colour temperature', 'mounting type'],
                'conditional' => ['CRI', 'UGR', 'driver type', 'warranty'],
                'checks'      => ['quantity vs room area and required lux level'],
            ],
            'downlight' => [
                'keywords'    => ['downlight', 'سبوت لايت', 'داون لايت'],
                'unit'        => 'PCS',
                'blocking'    => ['cut-out size', 'wattage', 'lumens', 'colour temperature'],
                'conditional' => ['beam angle', 'IP rating', 'dimmable'],
                'checks'      => ['quantity vs area and lux level'],
            ],
            'floodlight' => [
                'keywords'    => ['floodlight', 'كشاف', 'فلود لايت'],
                'unit'        => 'PCS',
                'blocking'    => ['wattage', 'lumens', 'colour temperature', 'IP rating'],
                'conditional' => ['beam angle', 'IK rating', 'surge protection', 'mounting type'],
                'checks'      => [],
            ],
            'emergency_light' => [
                'keywords'    => ['emergency light', 'إنارة طوارئ'],
                'unit'        => 'PCS',
                'blocking'    => ['maintained or non-maintained', 'lumen output', 'battery backup duration', 'certification'],
                'conditional' => ['self-test', 'indoor or outdoor'],
                'checks'      => ['coverage vs escape-route layout'],
            ],
            'exit_sign' => [
                'keywords'    => ['exit sign', 'لوحة مخرج'],
                'unit'        => 'PCS',
                'blocking'    => ['single or double sided', 'language (Arabic/English/bilingual)', 'maintained or non-maintained', 'battery backup duration'],
                'conditional' => ['arrow direction'],
                'checks'      => [],
            ],
            'distribution_board' => [
                'keywords'    => ['distribution board', 'db panel', 'لوحة توزيع'],
                'unit'        => 'PCS',
                'blocking'    => ['single or three phase', 'incomer rating', 'number of ways', 'fault level', 'busbar rating', 'IP rating'],
                'conditional' => ['indoor or outdoor', 'metering'],
                'checks'      => ['ways vs project circuits', 'incomer and busbar vs connected load'],
            ],
            'mccb' => [
                'keywords'    => ['mccb', 'moulded case circuit breaker'],
                'unit'        => 'PCS',
                'blocking'    => ['number of poles', 'rated current', 'breaking capacity', 'trip type'],
                'conditional' => ['fixed or adjustable trip', 'manufacturer series compatibility'],
                'checks'      => ['rating vs cable size and load'],
            ],
            'mcb' => [
                'keywords'    => ['mcb', 'miniature circuit breaker', 'قاطع كهربائي'],
                'unit'        => 'PCS',
                'blocking'    => ['number of poles', 'rated current', 'trip curve', 'breaking capacity'],
                'conditional' => ['voltage'],
                'checks'      => ['rating vs cable size and load'],
            ],
            'rccb' => [
                'keywords'    => ['rccb', 'rcbo', 'قاطع تسرب أرضي'],
                'unit'        => 'PCS',
                'blocking'    => ['RCCB or RCBO', 'number of poles', 'rated current', 'residual current sensitivity', 'type (AC/A/F/B)'],
                'conditional' => ['breaking capacity'],
                'checks'      => [],
            ],
            'contactor' => [
                'keywords'    => ['contactor', 'كونتاكتور'],
                'unit'        => 'PCS',
                'blocking'    => ['number of poles', 'coil voltage', 'AC-3 current rating', 'load type'],
                'conditional' => ['auxiliary contacts'],
                'checks'      => [],
            ],
            'power_cable' => [
                'keywords'    => ['power cable', 'xlpe', 'nyy', 'كابل كهرباء'],
                'unit'        => 'M',
                'blocking'    => ['number of cores', 'conductor size', 'copper or aluminium', 'XLPE or PVC', 'armoured or unarmoured', 'voltage rating', 'required length'],
                'conditional' => ['fire rating'],
                'checks'      => ['cable size vs load, voltage drop and breaker rating'],
            ],
            'cable_tray' => [
                'keywords'    => ['cable tray', 'حامل كابلات', 'تراي'],
                'unit'        => 'M',
                'blocking'    => ['width', 'side height', 'material thickness', 'finish', 'type (perforated/ladder/solid)'],
                'conditional' => ['cover', 'support spacing', 'fittings'],
                'checks'      => ['tray fill vs cable quantities'],
            ],
            'conduit' => [
                'keywords'    => ['conduit', 'مواسير كهرباء', 'كوندويت'],
                'unit'        => 'M',
                'blocking'    => ['material (PVC/EMT/GI/flexible)', 'diameter', 'duty (heavy or medium)', 'required length'],
                'conditional' => ['indoor or outdoor', 'fittings'],
                'checks'      => [],
            ],
            'socket' => [
                'keywords'    => ['socket outlet', 'wall socket', 'مفتاح كهرباء', 'بريزة'],
                'unit'        => 'PCS',
                'blocking'    => ['socket standard', 'current rating', 'number of gangs', 'switched or unswitched'],
                'conditional' => ['USB', 'weatherproof', 'finish and colour'],
                'checks'      => [],
            ],
            'isolator' => [
                'keywords'    => ['isolator', 'عازل كهربائي'],
                'unit'        => 'PCS',
                'blocking'    => ['number of poles', 'current rating', 'indoor or outdoor', 'IP rating'],
                'conditional' => ['load type', 'lockable'],
                'checks'      => [],
            ],
            'ats' => [
                'keywords'    => ['automatic transfer switch', 'ats panel', 'مفتاح تحويل أوتوماتيكي'],
                'unit'        => 'PCS',
                'blocking'    => ['current rating', 'number of poles', 'voltage and phase', 'number of sources', 'controller type'],
                'conditional' => ['bypass', 'enclosure IP rating'],
                'checks'      => ['ATS rating vs generator and incomer rating'],
            ],
            'generator' => [
                'keywords'    => ['generator', 'genset', 'مولد كهربائي', 'جينيريتور'],
                'unit'        => 'SET',
                'blocking'    => ['prime or standby rating', 'capacity in kVA AND kW', 'voltage and phase', 'fuel type', 'canopy type', 'fuel-tank capacity and autonomy'],
                'conditional' => ['ATS requirement', 'emission and noise limits', 'warranty'],
                'checks'      => ['capacity vs project connected and demand loads'],
            ],
            'transformer' => [
                'keywords'    => ['transformer', 'محول كهربائي'],
                'unit'        => 'PCS',
                'blocking'    => ['capacity', 'primary and secondary voltage', 'number of phases', 'dry or oil immersed', 'vector group'],
                'conditional' => ['impedance', 'indoor or outdoor', 'enclosure rating', 'efficiency'],
                'checks'      => ['capacity vs total connected load'],
            ],
            'capacitor_bank' => [
                'keywords'    => ['capacitor bank', 'power factor correction', 'مكثفات'],
                'unit'        => 'SET',
                'blocking'    => ['required kVAR', 'voltage', 'number of steps', 'detuned reactors required?'],
                'conditional' => ['harmonic level', 'controller', 'enclosure'],
                'checks'      => ['kVAR vs load power factor target'],
            ],
            'earthing' => [
                'keywords'    => ['earthing', 'grounding', 'تأريض'],
                'unit'        => 'SET',
                'blocking'    => ['electrode type and size', 'conductor material and size', 'required earth resistance', 'number of pits'],
                'conditional' => ['soil conditions', 'inspection chamber', 'accessories'],
                'checks'      => ['design vs electrical fault and protection requirements'],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HVAC
    // ─────────────────────────────────────────────────────────────────────────

    private static function hvac(): array
    {
        return [
            'split_ac' => [
                'keywords'    => ['split air condition', 'split ac', 'مكيف سبليت'],
                'unit'        => 'SET',
                'blocking'    => ['capacity in BTU or TR', 'inverter or fixed speed', 'cooling only or heat pump', 'refrigerant', 'pipe length'],
                'conditional' => ['energy-efficiency rating', 'indoor/outdoor unit type', 'warranty'],
                'checks'      => ['capacity vs area, occupancy, equipment load and climate'],
            ],
            'cassette_ac' => [
                'keywords'    => ['cassette air condition', 'cassette ac', 'مكيف كاسيت'],
                'unit'        => 'SET',
                'blocking'    => ['capacity', 'cassette type (2-way or 4-way)', 'pipe length'],
                'conditional' => ['inverter', 'drain pump', 'fresh-air connection', 'controller'],
                'checks'      => ['capacity vs area and occupancy'],
            ],
            'package_ac' => [
                'keywords'    => ['package unit', 'packaged air condition', 'وحدة تكييف مجمعة'],
                'unit'        => 'PCS',
                'blocking'    => ['cooling capacity', 'supply airflow', 'external static pressure', 'voltage and phase'],
                'conditional' => ['refrigerant', 'number of stages', 'controls'],
                'checks'      => ['capacity vs zone load'],
            ],
            'vrf_indoor' => [
                'keywords'    => ['vrf indoor', 'vrv indoor', 'وحدة داخلية'],
                'unit'        => 'PCS',
                'blocking'    => ['indoor-unit type', 'cooling capacity', 'airflow', 'pipe connection sizes'],
                'conditional' => ['controller', 'drain pump'],
                'checks'      => ['total indoor capacity vs outdoor combination ratio'],
            ],
            'vrf_outdoor' => [
                'keywords'    => ['vrf outdoor', 'vrv outdoor', 'وحدة خارجية'],
                'unit'        => 'SET',
                'blocking'    => ['required total capacity', 'connected indoor-unit capacity', 'combination ratio', 'heat pump or heat recovery', 'design ambient temperature', 'maximum piping length'],
                'conditional' => ['electrical supply'],
                'checks'      => ['all indoor units vs outdoor combination limits'],
            ],
            'ahu' => [
                'keywords'    => ['air handling unit', 'ahu', 'وحدة مناولة هواء'],
                'unit'        => 'PCS',
                'blocking'    => ['airflow', 'external static pressure', 'cooling and heating capacity', 'coil type', 'filter class', 'casing construction'],
                'conditional' => ['motor and VFD', 'dimensions', 'indoor or outdoor'],
                'checks'      => ['airflow vs zone requirement'],
            ],
            'fcu' => [
                'keywords'    => ['fan coil unit', 'fcu', 'وحدة ملف مروحي'],
                'unit'        => 'PCS',
                'blocking'    => ['airflow', 'static pressure', 'two-pipe or four-pipe', 'number of coil rows', 'concealed or exposed'],
                'conditional' => ['drain-pan material', 'valve and thermostat'],
                'checks'      => ['airflow vs room load'],
            ],
            'exhaust_fan' => [
                'keywords'    => ['exhaust fan', 'مروحة شفط'],
                'unit'        => 'PCS',
                'blocking'    => ['required airflow', 'static pressure', 'mounting type'],
                'conditional' => ['noise limit', 'motor type', 'IP rating', 'backdraft damper'],
                'checks'      => ['airflow vs required air changes per hour'],
            ],
            'ductwork' => [
                'keywords'    => ['duct work', 'ductwork', 'hvac duct', 'دكت تكييف'],
                'unit'        => 'M2',
                'blocking'    => ['duct material', 'sheet thickness or gauge', 'pressure class', 'insulation type and thickness', 'total duct area'],
                'conditional' => ['internal lining', 'fittings'],
                'checks'      => ['reject pricing by linear metre without duct dimensions'],
            ],
            'diffuser' => [
                'keywords'    => ['diffuser', 'grille', 'مخرج هواء'],
                'unit'        => 'PCS',
                'blocking'    => ['type (diffuser or grille)', 'neck size', 'face size', 'required airflow'],
                'conditional' => ['finish and colour', 'volume-control damper'],
                'checks'      => ['airflow vs duct design'],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Plumbing and fire fighting
    // ─────────────────────────────────────────────────────────────────────────

    private static function plumbingAndFire(): array
    {
        return [
            'water_pump' => [
                'keywords'    => ['water pump', 'مضخة مياه', 'طلمبة'],
                'unit'        => 'PCS',
                'blocking'    => ['required flow', 'required head', 'fluid type and temperature', 'pump type', 'motor power', 'voltage and phase'],
                'conditional' => ['body and impeller material', 'duty/standby arrangement', 'control panel'],
                'checks'      => ['flow and head vs building height and demand'],
            ],
            'booster_pump' => [
                'keywords'    => ['booster pump', 'مضخة ضغط'],
                'unit'        => 'SET',
                'blocking'    => ['total flow', 'required head', 'number of pumps', 'duty and standby arrangement'],
                'conditional' => ['VFD', 'pressure-vessel size', 'control panel', 'manifold material'],
                'checks'      => ['flow vs peak demand'],
            ],
            'submersible_pump' => [
                'keywords'    => ['submersible pump', 'مضخة غاطسة'],
                'unit'        => 'PCS',
                'blocking'    => ['required flow', 'required head', 'application (clean/drainage/sewage)', 'discharge diameter'],
                'conditional' => ['maximum solids size', 'installation depth', 'cable length', 'pump material'],
                'checks'      => [],
            ],
            'water_tank' => [
                'keywords'    => ['water tank', 'خزان مياه'],
                'unit'        => 'PCS',
                'blocking'    => ['capacity', 'material (GRP/PE/steel/concrete)', 'dimensions', 'sectional or one-piece'],
                'conditional' => ['shape', 'indoor or outdoor', 'insulation', 'fittings'],
                'checks'      => ['capacity vs occupancy and expected consumption'],
            ],
            'ppr_pipe' => [
                'keywords'    => ['ppr pipe', 'ماسورة ppr'],
                'unit'        => 'M',
                'blocking'    => ['diameter', 'PN rating', 'application (hot or cold water)', 'pipe length'],
                'conditional' => ['SDR', 'fittings', 'certification'],
                'checks'      => [],
            ],
            'upvc_pipe' => [
                'keywords'    => ['upvc pipe', 'pvc pipe', 'ماسورة بي في سي'],
                'unit'        => 'M',
                'blocking'    => ['diameter', 'pressure class or schedule', 'application (pressure or drainage)', 'pipe length'],
                'conditional' => ['joint type', 'fittings'],
                'checks'      => [],
            ],
            'hdpe_pipe' => [
                'keywords'    => ['hdpe pipe', 'ماسورة بولي إيثيلين'],
                'unit'        => 'M',
                'blocking'    => ['outside diameter', 'SDR or PN', 'material grade', 'application', 'length (coil or straight)'],
                'conditional' => ['jointing method', 'fittings'],
                'checks'      => [],
            ],
            'gate_valve' => [
                'keywords'    => ['gate valve', 'محبس بوابة'],
                'unit'        => 'PCS',
                'blocking'    => ['valve size', 'pressure rating', 'body material', 'end connection'],
                'conditional' => ['rising or non-rising stem', 'approvals'],
                'checks'      => [],
            ],
            'ball_valve' => [
                'keywords'    => ['ball valve', 'محبس كروي'],
                'unit'        => 'PCS',
                'blocking'    => ['valve size', 'pressure rating', 'body and ball material', 'end connection'],
                'conditional' => ['full or reduced bore', 'manual or actuated'],
                'checks'      => [],
            ],
            'sanitary_fixture' => [
                'keywords'    => ['wash basin', 'water closet', 'sanitary ware', 'أدوات صحية', 'مغسلة'],
                'unit'        => 'PCS',
                'blocking'    => ['fixture type', 'brand or approved equivalent', 'material', 'dimensions', 'mounting (floor or wall-hung)'],
                'conditional' => ['colour', 'trap and flushing system', 'accessories', 'water-saving requirement'],
                'checks'      => [],
            ],
            'water_heater' => [
                'keywords'    => ['water heater', 'سخان مياه'],
                'unit'        => 'PCS',
                'blocking'    => ['type (storage/instantaneous/solar/heat pump)', 'capacity', 'electrical power', 'pressure rating'],
                'conditional' => ['orientation', 'thermostat and safety devices', 'warranty'],
                'checks'      => ['capacity vs occupancy'],
            ],
            'fire_extinguisher' => [
                'keywords'    => ['fire extinguisher', 'طفاية حريق'],
                'unit'        => 'PCS',
                'blocking'    => ['extinguishing agent', 'capacity', 'fire rating', 'portable or trolley mounted', 'Civil Defense approval'],
                'conditional' => ['cylinder certification', 'bracket or cabinet'],
                'checks'      => ['type and quantity vs project hazard class and area'],
            ],
            'fire_hose_reel' => [
                'keywords'    => ['fire hose reel', 'بكرة خرطوم حريق'],
                'unit'        => 'SET',
                'blocking'    => ['hose length', 'hose diameter', 'cabinet material and size', 'valve and nozzle', 'certification'],
                'conditional' => ['surface or recessed mounting'],
                'checks'      => ['coverage vs floor layout'],
            ],
            'fire_pump' => [
                'keywords'    => ['fire pump', 'مضخة حريق'],
                'unit'        => 'SET',
                'blocking'    => ['required flow and pressure', 'pump set composition (electric/diesel/jockey)', 'applicable NFPA standard', 'UL/FM approval'],
                'conditional' => ['controllers', 'fuel tank', 'test header and accessories'],
                'checks'      => ['flow and pressure vs sprinkler and hydrant demand'],
            ],
            'sprinkler' => [
                'keywords'    => ['sprinkler', 'رشاش حريق'],
                'unit'        => 'PCS',
                'blocking'    => ['orientation (upright/pendent/sidewall/concealed)', 'temperature rating', 'K-factor', 'response type', 'UL/FM approval'],
                'conditional' => ['finish'],
                'checks'      => ['type vs occupancy and hazard classification'],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Furniture and interior
    // ─────────────────────────────────────────────────────────────────────────

    private static function furnitureAndInterior(): array
    {
        return [
            'office_desk' => [
                'keywords'    => ['office desk', 'مكتب خشبي', 'طاولة مكتب'],
                'unit'        => 'PCS',
                'blocking'    => ['dimensions', 'material', 'finish', 'standard or custom manufactured'],
                'conditional' => ['drawers or pedestal', 'cable management', 'warranty'],
                'checks'      => ['quantity vs number of users'],
            ],
            'office_chair' => [
                'keywords'    => ['office chair', 'كرسي مكتب'],
                'unit'        => 'PCS',
                'blocking'    => ['chair type', 'mechanism type', 'upholstery material', 'base material'],
                'conditional' => ['lumbar support', 'adjustable armrests', 'maximum supported weight', 'warranty'],
                'checks'      => ['quantity vs number of users and desks'],
            ],
            'meeting_table' => [
                'keywords'    => ['meeting table', 'conference table', 'طاولة اجتماعات'],
                'unit'        => 'PCS',
                'blocking'    => ['dimensions', 'number of seats', 'material', 'finish'],
                'conditional' => ['cable box', 'modular or fixed'],
                'checks'      => ['table size vs meeting-room capacity'],
            ],
            'filing_cabinet' => [
                'keywords'    => ['filing cabinet', 'دولاب ملفات'],
                'unit'        => 'PCS',
                'blocking'    => ['dimensions', 'material', 'number of drawers or shelves'],
                'conditional' => ['lock', 'fire rating', 'finish and colour'],
                'checks'      => [],
            ],
            'reception_counter' => [
                'keywords'    => ['reception counter', 'كاونتر استقبال'],
                'unit'        => 'PCS',
                'blocking'    => ['dimensions and layout', 'shape', 'material', 'finish', 'approved shop drawing'],
                'conditional' => ['lighting', 'cable management'],
                'checks'      => ['custom item — dimensions must never be assumed'],
            ],
            'workstation_furniture' => [
                'keywords'    => ['modular workstation', 'ورك ستيشن مكتبي', 'وحدات عمل'],
                'unit'        => 'SET',
                'blocking'    => ['number of users per cluster', 'workstation configuration', 'desk dimensions', 'partition height and material'],
                'conditional' => ['pedestals', 'power and data modules', 'cable management'],
                'checks'      => ['total seats vs required employees'],
            ],
            'raised_floor' => [
                'keywords'    => ['raised floor', 'access floor', 'أرضية مرتفعة'],
                'unit'        => 'M2',
                'blocking'    => ['panel size', 'finished floor height', 'load rating', 'panel core', 'surface finish', 'total area'],
                'conditional' => ['pedestals and stringers', 'wastage'],
                'checks'      => ['area vs room dimensions + wastage'],
            ],
            'carpet_tiles' => [
                'keywords'    => ['carpet tile', 'سجاد مربعات'],
                'unit'        => 'M2',
                'blocking'    => ['tile dimensions', 'fiber material', 'pile weight', 'backing', 'fire rating', 'total area'],
                'conditional' => ['colour and pattern', 'wastage percentage'],
                'checks'      => ['area vs room dimensions + wastage'],
            ],
            'gypsum_partition' => [
                'keywords'    => ['gypsum partition', 'drywall partition', 'قواطع جبس'],
                'unit'        => 'M2',
                'blocking'    => ['total thickness', 'stud size and spacing', 'number of gypsum layers', 'partition height', 'total area'],
                'conditional' => ['insulation', 'fire rating', 'acoustic rating'],
                'checks'      => ['area vs floor plan'],
            ],
            'false_ceiling' => [
                'keywords'    => ['false ceiling', 'suspended ceiling', 'سقف مستعار'],
                'unit'        => 'M2',
                'blocking'    => ['ceiling type', 'tile or board dimensions', 'grid system', 'suspension height', 'total area'],
                'conditional' => ['moisture and fire resistance', 'access panels'],
                'checks'      => ['area vs floor plan'],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Construction and general materials
    // ─────────────────────────────────────────────────────────────────────────

    private static function constructionMaterials(): array
    {
        return [
            'cement' => [
                'keywords'    => ['cement', 'أسمنت'],
                'unit'        => 'BAG',
                'blocking'    => ['cement type', 'strength grade', 'bag weight', 'applicable standard', 'quantity'],
                'conditional' => ['delivery location'],
                'checks'      => ['never price bags without a confirmed bag weight'],
            ],
            'ready_mix' => [
                'keywords'    => ['ready mix', 'ready-mix concrete', 'خرسانة جاهزة'],
                'unit'        => 'M3',
                'blocking'    => ['concrete strength', 'slump', 'cement type', 'maximum aggregate size', 'total volume'],
                'conditional' => ['additives', 'concrete pump requirement', 'delivery location and timing'],
                'checks'      => ['volume vs structural drawings'],
            ],
            'rebar' => [
                'keywords'    => ['reinforcement steel', 'rebar', 'حديد تسليح'],
                'unit'        => 'TON',
                'blocking'    => ['steel grade', 'bar diameters', 'standard bar length', 'plain or deformed', 'total tonnage'],
                'conditional' => ['coated or uncoated', 'cut-and-bend schedule'],
                'checks'      => ['tonnage vs bar bending schedule'],
            ],
            'concrete_block' => [
                'keywords'    => ['concrete block', 'block work', 'بلوك خرساني', 'طابوق'],
                'unit'        => 'PCS',
                'blocking'    => ['block type', 'dimensions', 'density', 'compressive strength', 'quantity'],
                'conditional' => ['fire/acoustic requirement', 'pallet quantity'],
                'checks'      => ['quantity vs wall area'],
            ],
            'tiles' => [
                'keywords'    => ['floor tile', 'wall tile', 'ceramic tile', 'porcelain', 'بلاط', 'سيراميك'],
                'unit'        => 'M2',
                'blocking'    => ['application (floor or wall)', 'material', 'tile dimensions', 'thickness', 'surface finish', 'total area'],
                'conditional' => ['slip rating', 'colour and pattern', 'wastage percentage'],
                'checks'      => ['area vs floor plan + wastage'],
            ],
            'waterproofing' => [
                'keywords'    => ['waterproofing', 'membrane', 'عزل مائي'],
                'unit'        => 'M2',
                'blocking'    => ['system type', 'thickness', 'application area', 'substrate', 'exposed or protected', 'total area'],
                'conditional' => ['accessories', 'overlap and wastage'],
                'checks'      => ['area vs drawings + overlap'],
            ],
            'paint' => [
                'keywords'    => ['paint', 'دهان', 'بويه'],
                'unit'        => 'LTR',
                'blocking'    => ['interior or exterior', 'surface type', 'paint type', 'finish', 'number of coats', 'coverage rate'],
                'conditional' => ['primer requirement', 'colour', 'low-VOC requirement'],
                'checks'      => ['litres calculated from area x coats / coverage — never price m2 without a system definition'],
            ],
            'door' => [
                'keywords'    => ['door leaf', 'wooden door', 'fire door', 'باب'],
                'unit'        => 'PCS',
                'blocking'    => ['material', 'width and height', 'door leaf and frame scope', 'hardware set', 'finish'],
                'conditional' => ['fire rating', 'acoustic rating', 'handing (left or right)'],
                'checks'      => ['confirm whether frame and hardware are included'],
            ],
            'aluminium_window' => [
                'keywords'    => ['aluminium window', 'aluminum window', 'نافذة ألمنيوم', 'شباك'],
                'unit'        => 'M2',
                'blocking'    => ['opening dimensions', 'aluminium system/profile', 'glass type and thickness', 'glazing (single or double)', 'opening type'],
                'conditional' => ['colour and coating', 'hardware', 'thermal and acoustic requirements'],
                'checks'      => ['area vs elevation drawings'],
            ],
            'power_drill' => [
                'keywords'    => ['power drill', 'rotary hammer', 'شنيور', 'مثقاب'],
                'unit'        => 'PCS',
                'blocking'    => ['corded or cordless', 'tool type', 'motor power or battery voltage', 'chuck type and size'],
                'conditional' => ['battery capacity and quantity', 'maximum drilling capacity', 'accessories', 'duty class', 'warranty'],
                'checks'      => [],
            ],
        ];
    }
}
