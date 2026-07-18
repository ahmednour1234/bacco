<?php

namespace App\Services\Pricing;

/**
 * Project-level cross-item checks.
 *
 * The engine qualifies each line on its own; this pass looks at the BOQ as a
 * whole and catches the mismatches that are only visible in aggregate — more
 * cameras than NVR channels, PoE devices exceeding the switch budget, monitors
 * that outnumber computers, rack equipment deeper than the rack.
 *
 * It never silently changes a quantity. Every finding is attached to the
 * relevant line as a compatibility warning and, when material, escalates that
 * line to COMPATIBILITY_REVIEW_REQUIRED so a human decides.
 */
final class ProjectIntelligence
{
    /**
     * @param  array<int, array<string, mixed>>  $records qualified records from ProductSpecEngine
     * @param  array<string, mixed>              $project shared project context
     * @return array<int, array<string, mixed>>
     */
    public static function analyse(array $records, array $project = []): array
    {
        $byFamily = self::indexByFamily($records);

        $records = self::checkCameraToNvr($records, $byFamily);
        $records = self::checkPoeBudget($records, $byFamily);
        $records = self::checkMonitorToComputer($records, $byFamily);
        $records = self::checkSwitchPorts($records, $byFamily);
        $records = self::checkPatchPanelPorts($records, $byFamily);
        $records = self::checkDataCableLength($records, $byFamily);
        $records = self::checkRackCapacity($records, $byFamily);
        $records = self::checkDeskToChair($records, $byFamily);

        return $records;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Individual checks
    // ─────────────────────────────────────────────────────────────────────────

    /** Cameras must not exceed NVR channels; spare capacity is expected. */
    private static function checkCameraToNvr(array $records, array $byFamily): array
    {
        $cameras = self::totalQuantity($records, $byFamily['ip_camera'] ?? []);
        $nvrRows = $byFamily['nvr'] ?? [];

        if ($cameras <= 0 || $nvrRows === []) {
            return $records;
        }

        // Channel count is usually stated in the description ("32 channel NVR").
        $channels = 0;
        foreach ($nvrRows as $i) {
            $channels += self::extractNumber($records[$i]['original_item_name'], ['channel', 'ch', 'قناة'])
                * max(1, (float) $records[$i]['quantity']);
        }

        if ($channels <= 0) {
            return self::warn($records, $nvrRows, 'NVR_CHANNELS_UNKNOWN', 'warning',
                "Channel count could not be read from the NVR description, so it cannot be checked against the {$cameras} camera(s) in this BOQ.");
        }

        if ($channels < $cameras) {
            return self::warn($records, array_merge($nvrRows, $byFamily['ip_camera'] ?? []),
                'NVR_CHANNELS_INSUFFICIENT', 'error',
                "The BOQ has {$cameras} camera(s) but only {$channels} NVR channel(s). Recording capacity is insufficient.");
        }

        return $records;
    }

    /** Total PoE draw must fit the switch PoE budget with headroom. */
    private static function checkPoeBudget(array $records, array $byFamily): array
    {
        $switchRows = $byFamily['switch_managed'] ?? [];
        if ($switchRows === []) {
            return $records;
        }

        // Typical worst-case draw per device class, in watts.
        $draw = 0.0;
        $draw += self::totalQuantity($records, $byFamily['ip_camera'] ?? []) * 12.0;
        $draw += self::totalQuantity($records, $byFamily['access_point'] ?? []) * 25.0;

        if ($draw <= 0) {
            return $records;
        }

        $budget = 0.0;
        foreach ($switchRows as $i) {
            $budget += self::extractNumber($records[$i]['original_item_name'], ['w', 'watt', 'poe'])
                * max(1, (float) $records[$i]['quantity']);
        }

        if ($budget <= 0) {
            return self::warn($records, $switchRows, 'POE_BUDGET_UNKNOWN', 'warning',
                'The switch PoE power budget is not stated, so it cannot be checked against roughly '
                . round($draw) . ' W of PoE devices in this BOQ.');
        }

        // 80% loading is the practical ceiling for a PoE budget.
        if ($budget * 0.8 < $draw) {
            return self::warn($records, $switchRows, 'POE_BUDGET_INSUFFICIENT', 'error',
                'PoE devices draw roughly ' . round($draw) . ' W, which exceeds the usable budget of '
                . round($budget * 0.8) . ' W (80% of ' . round($budget) . ' W).');
        }

        return $records;
    }

    /** Monitors are normally one-per-computer unless the description says otherwise. */
    private static function checkMonitorToComputer(array $records, array $byFamily): array
    {
        $monitors  = self::totalQuantity($records, $byFamily['monitor'] ?? []);
        $computers = self::totalQuantity($records, $byFamily['desktop'] ?? [])
            + self::totalQuantity($records, $byFamily['workstation'] ?? []);

        if ($monitors <= 0 || $computers <= 0) {
            return $records;
        }

        if ($monitors < $computers) {
            return self::warn($records, $byFamily['monitor'] ?? [], 'MONITOR_COUNT_LOW', 'warning',
                "There are {$computers} computer(s) but only {$monitors} monitor(s). Confirm the intended ratio.");
        }

        if ($monitors > $computers * 2) {
            return self::warn($records, $byFamily['monitor'] ?? [], 'MONITOR_COUNT_HIGH', 'info',
                "There are {$monitors} monitor(s) for {$computers} computer(s). Confirm the multi-screen requirement.");
        }

        return $records;
    }

    /** Switch ports must cover the devices that plug into them. */
    private static function checkSwitchPorts(array $records, array $byFamily): array
    {
        $switchRows = array_merge($byFamily['switch_managed'] ?? [], $byFamily['switch_unmanaged'] ?? []);
        if ($switchRows === []) {
            return $records;
        }

        $devices = self::totalQuantity($records, $byFamily['ip_camera'] ?? [])
            + self::totalQuantity($records, $byFamily['access_point'] ?? [])
            + self::totalQuantity($records, $byFamily['desktop'] ?? [])
            + self::totalQuantity($records, $byFamily['printer'] ?? []);

        if ($devices <= 0) {
            return $records;
        }

        $ports = 0.0;
        foreach ($switchRows as $i) {
            $ports += self::extractNumber($records[$i]['original_item_name'], ['port', 'منفذ'])
                * max(1, (float) $records[$i]['quantity']);
        }

        if ($ports > 0 && $ports < $devices) {
            return self::warn($records, $switchRows, 'SWITCH_PORTS_INSUFFICIENT', 'error',
                "Roughly {$devices} network device(s) require ports, but the BOQ provides only {$ports}.");
        }

        return $records;
    }

    /** Patch-panel ports should track the number of network outlets. */
    private static function checkPatchPanelPorts(array $records, array $byFamily): array
    {
        $panelRows = $byFamily['patch_panel'] ?? [];
        $devices   = self::totalQuantity($records, $byFamily['ip_camera'] ?? [])
            + self::totalQuantity($records, $byFamily['access_point'] ?? [])
            + self::totalQuantity($records, $byFamily['desktop'] ?? []);

        if ($panelRows === [] || $devices <= 0) {
            return $records;
        }

        $ports = 0.0;
        foreach ($panelRows as $i) {
            $ports += self::extractNumber($records[$i]['original_item_name'], ['port', 'منفذ'])
                * max(1, (float) $records[$i]['quantity']);
        }

        if ($ports > 0 && $ports < $devices) {
            return self::warn($records, $panelRows, 'PATCH_PANEL_PORTS_LOW', 'warning',
                "Patch-panel ports ({$ports}) are fewer than the roughly {$devices} outlet(s) implied by this BOQ.");
        }

        return $records;
    }

    /** Flag a data-cable quantity too small for the number of points. */
    private static function checkDataCableLength(array $records, array $byFamily): array
    {
        $cableRows = $byFamily['data_cable'] ?? [];
        $points    = self::totalQuantity($records, $byFamily['ip_camera'] ?? [])
            + self::totalQuantity($records, $byFamily['access_point'] ?? [])
            + self::totalQuantity($records, $byFamily['desktop'] ?? []);

        if ($cableRows === [] || $points <= 0) {
            return $records;
        }

        $metres = 0.0;
        foreach ($cableRows as $i) {
            $metres += (float) $records[$i]['quantity'] * ($records[$i]['normalized_unit'] === 'ROLL' ? 305 : 1);
        }

        // 30 m average per drop is a conservative floor for a small office.
        $expected = $points * 30;

        if ($metres > 0 && $metres < $expected * 0.5) {
            return self::warn($records, $cableRows, 'DATA_CABLE_QUANTITY_LOW', 'warning',
                "About {$points} data point(s) typically need roughly {$expected} m of cable, but only "
                . round($metres) . ' m is listed. Confirm the routing allowance and wastage.');
        }

        return $records;
    }

    /** Rack U capacity must fit the equipment destined for it. */
    private static function checkRackCapacity(array $records, array $byFamily): array
    {
        $rackRows = $byFamily['rack'] ?? [];
        if ($rackRows === []) {
            return $records;
        }

        // Conservative U footprints for rack-mounted families.
        $requiredU = self::totalQuantity($records, $byFamily['server'] ?? []) * 2
            + self::totalQuantity($records, $byFamily['switch_managed'] ?? []) * 1
            + self::totalQuantity($records, $byFamily['patch_panel'] ?? []) * 1
            + self::totalQuantity($records, $byFamily['ups'] ?? []) * 2
            + self::totalQuantity($records, $byFamily['nvr'] ?? []) * 2;

        if ($requiredU <= 0) {
            return $records;
        }

        $availableU = 0.0;
        foreach ($rackRows as $i) {
            $availableU += self::extractNumber($records[$i]['original_item_name'], ['u'])
                * max(1, (float) $records[$i]['quantity']);
        }

        if ($availableU > 0 && $availableU < $requiredU) {
            return self::warn($records, $rackRows, 'RACK_CAPACITY_INSUFFICIENT', 'error',
                "Rack equipment needs about {$requiredU}U but only {$availableU}U is available.");
        }

        return $records;
    }

    /** Desks and chairs are normally one-to-one. */
    private static function checkDeskToChair(array $records, array $byFamily): array
    {
        $desks  = self::totalQuantity($records, $byFamily['office_desk'] ?? []);
        $chairs = self::totalQuantity($records, $byFamily['office_chair'] ?? []);

        if ($desks <= 0 || $chairs <= 0 || $desks === $chairs) {
            return $records;
        }

        return self::warn($records, array_merge($byFamily['office_desk'] ?? [], $byFamily['office_chair'] ?? []),
            'DESK_CHAIR_MISMATCH', 'info',
            "The BOQ lists {$desks} desk(s) and {$chairs} chair(s). Confirm the intended ratio.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array<string, list<int>> family key => record indices */
    private static function indexByFamily(array $records): array
    {
        $index = [];
        foreach ($records as $i => $r) {
            $key = $r['catalog_key'] ?? null;
            if ($key !== null) {
                $index[$key][] = $i;
            }
        }
        return $index;
    }

    /** @param list<int> $indices */
    private static function totalQuantity(array $records, array $indices): float
    {
        $total = 0.0;
        foreach ($indices as $i) {
            $total += (float) ($records[$i]['quantity'] ?? 0);
        }
        return $total;
    }

    /**
     * Pull the number that precedes any of $labels in a description —
     * "24 Port PoE Switch" → 24, "32 Channel NVR" → 32, "42U Rack" → 42.
     *
     * @param list<string> $labels
     */
    private static function extractNumber(string $description, array $labels): float
    {
        $d = mb_strtolower($description);

        foreach ($labels as $label) {
            $label = preg_quote(mb_strtolower($label), '/');
            if (preg_match('/(\d+(?:\.\d+)?)\s*' . $label . '\b/u', $d, $m)) {
                return (float) $m[1];
            }
        }

        return 0.0;
    }

    /**
     * Attach a compatibility warning to each named row, and escalate the row's
     * status when the finding is material. Never mutates quantities.
     *
     * @param list<int> $indices
     */
    private static function warn(array $records, array $indices, string $code, string $severity, string $message): array
    {
        foreach (array_unique($indices) as $i) {
            if (! array_key_exists($i, $records)) {
                continue;
            }

            $records[$i]['compatibility_warnings'][] = [
                'code'     => $code,
                'severity' => $severity,
                'message'  => $message,
            ];

            // Only a genuine conflict blocks; advisory notes leave the status alone.
            if ($severity === 'error' && in_array($records[$i]['pricing_status'], ['READY_TO_PRICE', 'READY_WITH_ASSUMPTIONS'], true)) {
                $records[$i]['pricing_status'] = 'COMPATIBILITY_REVIEW_REQUIRED';
            }
        }

        return $records;
    }
}
