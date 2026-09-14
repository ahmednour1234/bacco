-- ═══════════════════════════════════════════════════════════════════
--  PREVIEW ONLY — no writes. Run in phpMyAdmin before any cleanup.
--  Tells you exactly what a purge would remove, and what it must not.
-- ═══════════════════════════════════════════════════════════════════

-- ── A. Every account that has quotations — find your test emails here ──
SELECT
  u.id                                AS user_id,
  u.email,
  u.user_type,
  COUNT(q.id)                         AS quotations,
  MIN(q.created_at)                   AS first_quotation,
  MAX(q.created_at)                   AS last_quotation
FROM users u
JOIN quotation_requests q
  ON q.client_id = u.id AND q.deleted_at IS NULL
GROUP BY u.id, u.email, u.user_type
ORDER BY quotations DESC;


-- ── B. Headline numbers: what is real vs. what is noise ───────────────
SELECT
  COUNT(*)                                          AS live_quotations,
  SUM(client_id IS NULL)                            AS guest_noise,
  SUM(client_id IS NOT NULL)                        AS account_backed,
  SUM(EXISTS (SELECT 1 FROM orders o
              WHERE o.quotation_request_id = q.id)) AS with_orders
FROM quotation_requests q
WHERE q.deleted_at IS NULL;


-- ── C. THE SAFETY CHECK — anything here must never be hard-deleted ────
--  A quotation with an order or payment behind it. The FK cascade would
--  wipe the order AND its payments permanently, ignoring soft deletes.
SELECT
  q.id                                AS quotation_id,
  q.quotation_no,
  u.email                             AS owner,
  COUNT(DISTINCT o.id)                AS orders,
  COUNT(DISTINCT p.id)                AS payments,
  COALESCE(SUM(DISTINCT p.amount), 0) AS money
FROM quotation_requests q
LEFT JOIN users    u ON u.id = q.client_id
LEFT JOIN orders   o ON o.quotation_request_id = q.id
LEFT JOIN payments p ON p.order_id = o.id
WHERE q.deleted_at IS NULL
GROUP BY q.id, q.quotation_no, u.email
HAVING orders > 0 OR payments > 0
ORDER BY payments DESC;


-- ── D. Dry-run count: replace the emails with your real test list ─────
--  Change nothing else. This only COUNTS what a purge would remove.
SELECT
  COUNT(*)                     AS would_delete,
  SUM(q.client_id IS NULL)     AS of_which_guests
FROM quotation_requests q
WHERE q.deleted_at IS NULL
  AND (
        q.client_id IS NULL                       -- guest noise
     OR q.client_id IN (
          SELECT id FROM users WHERE LOWER(email) IN (
            -- ↓↓↓ EDIT THIS LIST ↓↓↓
            'ahmednour5999@gmail.com',
            'ahmednour5999@gmail.com00',
            'ahmednour5999@gmail.com11',
            'ahmed@gmail.com',
            'khkhkh@gmail.com',
            'ss@gm.com',
            'kk@gm.com',
            'uu@gm.com',
            'khattabahmed256@gmail.com'
            -- ↑↑↑ EDIT THIS LIST ↑↑↑
          )
        )
      )
  -- Never touch anything with an order behind it.
  AND NOT EXISTS (SELECT 1 FROM orders o WHERE o.quotation_request_id = q.id);
