-- ═══════════════════════════════════════════════════════════════════
--  Find the email behind every account that still has quotations.
--  Read-only. Run in phpMyAdmin, then build the --email list from it.
-- ═══════════════════════════════════════════════════════════════════

SELECT
  u.id                                      AS user_id,
  u.name,
  u.email,
  u.user_type,
  COUNT(q.id)                               AS live_quotations,
  SUM(EXISTS (SELECT 1 FROM orders o
              WHERE o.quotation_request_id = q.id
                AND o.deleted_at IS NULL))  AS with_orders,
  MIN(q.created_at)                         AS first_quotation,
  MAX(q.created_at)                         AS last_quotation
FROM users u
JOIN quotation_requests q
  ON q.client_id = u.id
 AND q.deleted_at IS NULL
GROUP BY u.id, u.name, u.email, u.user_type
ORDER BY live_quotations DESC;

-- Reading the result:
--   * live_quotations  – how many rows that account would remove
--   * with_orders      – anything above 0 means real money may be attached.
--                        The purge command skips those rows automatically,
--                        but check them before adding the email to the list.
