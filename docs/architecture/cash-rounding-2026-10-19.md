# COD cash rounding and recovery consistency

Order 18958 first issued a prepaid Razorpay attempt for INR864 (fee0), then a COD advance328 on that same immutable order. Recovery displayed current fee34.56 but reused the old advance. No receipts were present at audit time.

Advanced COD now rounds each shipment's cash collection to whole rupees and assigns the exact residual to online advance; total is unchanged. Fees are finalized from configured payment settings before freezing the intent. Issued intents retain their original values.

Farmart Helper handles the core checkout payment-mode/snapshot mismatch through a fresh checkout token, preserving original evidence. It exposes a generic inconsistency filter; Advanced COD warns and blocks manual confirmation of inconsistent old attempts. Captured/refunding/refunded payments are excluded from automatic restart.

Tests: local cash-rounding, cod-amounts, advance-payment, manual-confirmation, settlement passed with rollback fixtures. Eight runtime files deployed; no core changes. Live SHA256 verification passed. Backup: /home/u834473639/codex-backups/cash-rounding-20261019/before.tgz and manifest.json.

Live browser recovered 18958 into fresh checkout: fee34.56, total898.56, advance341.56, cash557.00. Original18958 retained historical frozen values and displays reconciliation warning. No payment or order confirmation submitted. Screenshot: outputs/cash-rounding/live-recovery.png.
