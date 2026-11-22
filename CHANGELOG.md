# Changes in this source-review edition

- Added missing fresh-install migration prerequisites and reconciled the file/movement schema with the retained controllers.
- Corrected namespace/import errors and the dashboard destination.
- Added a guarded synthetic DemoSeeder and local workflow tests.
- Added descending movement ID as the tie-breaker for same-second movement records, preserving the latest-holder workflow.
- Replaced the uncertain interface shell with separately written CSS, navigation, authentication views and shell behavior after visual review.
- Removed inert template sample scripts and unused public assets. Bundled required browser libraries locally from pinned upstream packages with license notices.
- Added neutral empty-branding fallbacks, kept generated output and optional branding out of version control, and removed obsolete deployment-path comments.

No production deployment or framework modernization is claimed. The original working copy remains separate.
