# Validation and limits

Checked on 22 November 2026 with PHP 8.3.13, SQLite and the existing Composer lock.

## Checked

- Fresh-database migration and guarded invented-data seeding.
- Full test suite: 7 tests, 75 assertions. Coverage includes login and screen rendering, lifecycle/report/attachment paths, the approved same-second movement tie-breaker, and empty-branding fallbacks.
- Real browser: login, dashboard, File Creation and Configuration, with no page JavaScript errors or failed asset responses in the checked flow.
- An invented file was submitted through the real form after category, subcategory, branch, department and employee selection.
- Password reveal, mobile menu open/close and a 390px dashboard without horizontal overflow.
- Desktop screenshots were visually inspected; a synthetic generated PDF in the earlier same-workflow validation was also inspected.
- Browser libraries were replaced from pinned upstream packages, their license texts were inspected, and required notices were retained. Unused template assets and the uncertain old shell were removed.
- The original working application and repository were not edited.

## Not certified

- Production security, MySQL, real email, physical printers, RFID/scanner hardware, every master CRUD/import/export path and unrelated warehouse-era modules.
- One retained physical-verification route references an absent controller, so global route enumeration is not clean.
- The legacy dependency stack remains unsupported/affected by advisories. See SECURITY.md.
- Historical migrations here support a new disposable database only. They are not an upgrade path for an old database.

Screenshots and demo records are invented. No original records, company branding, runtime logs, repository history or usable preset account password are distributed.

## Final package checks

- A clean archive extraction followed the documented environment setup, key generation, dependency installation, fresh migration and guarded demo seeding. The full test suite passed again.
- A 20-page real-browser crawl returned no page JavaScript errors or failed HTTP responses. This is screen-load evidence, not full CRUD coverage for every page.
- Dark dashboard and File Creation panels, labels, Select2 controls and the Configuration action were checked visually. Low-contrast legacy surface styles were corrected in the replacement stylesheet. Light desktop and mobile views were rechecked afterward.
- Every distributed file other than SHA256SUMS.txt is covered by that checksum manifest. The ZIP was integrity-tested.

The final archive was extracted once more and its documented Composer install, key generation, package discovery, migration, guarded seeding and in-memory tests all completed. Composer still reports PSR-4 warnings for retained legacy duplicate/backup files.
