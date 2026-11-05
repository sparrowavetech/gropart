# Authorized GST batch repair — 17 October 2026

Production backup: `/home/u834473639/codex-backups/gst-batch-20261017-180250`.

Transaction repaired only the authorized 40 orders (18853–18879, 18881–18893), 41 line tax breakdowns/components, and invoices 738/740 with their items/components.

- Order 18885 and invoice 740: net 833.90 + included tax 150.10 + shipping 106 = 1090.00. Razorpay API independently confirmed INR 1090 captured. Invoice quantity 2, net unit price 416.95, line total 984.00.
- Invoice 738: net line 194.92 + tax 35.08 = 230.00; with shipping 74, invoice total remains 304.00.
- Incomplete order 18872: total 616.00 including shipping 106.
- Incomplete order 18874: total 466.00 including shipping 187.

All changes used direct scoped database updates, with no model events/customer notifications. Before/after snapshots and old cached invoice PDFs were backed up. Commit followed checks of all line/component sums and order/invoice totals. Full comparison verified payment rows, order/invoice status, other unrelated fields, stock and histories unchanged. Post-commit snapshot matched verified changes. Stale PDFs for invoices 738/740 were invalidated after backup. No source files were deployed or changed during this batch repair.
