# Backup & Restore Runbook

This runbook explains how we create, verify, and restore backups for the e-commerce platform. It covers the operational cadence, storage locations, and the sequence to follow when restoring the stack.

## Backup Cadence
- **Nightly (02:00 UTC)** – Incremental database backup via `php artisan system:backup:database --incremental`.
- **Nightly (02:15 UTC)** – Media delta sync via `php artisan system:backup:media --mode=delta`.
- **Weekly (Sundays 03:00 UTC)** – Full snapshot (`--full`) for both database and media.
- **Monthly (1st day 04:00 UTC)** – Archive export pushed to cold storage for long-term retention.

## Retention Policy
- Retain nightly incrementals for **14 days**.
- Retain weekly full snapshots for **12 weeks**.
- Retain monthly archives for **13 months**.
- Backups older than their retention window are pruned with `php artisan system:backup:prune` running every Monday at 04:30 UTC.

## Storage Locations
- **Primary S3 Bucket:** `s3://prus-eu-backups/<environment>` (versioned, encrypted at rest).
- **Secondary Region:** `s3://prus-eu-backups-dr/<environment>` (cross-region replication).
- **Local Staging:** `/srv/backups/<environment>` for the last 48 hours of snapshots.

## Required Environment Variables
| Variable | Purpose |
| --- | --- |
| `BACKUP_STORAGE_DISK` | Laravel disk key for the S3 bucket (defaults to `s3-backups`). |
| `BACKUP_PRUNE_RETENTION_DAYS` | Overrides the default 14-day incremental retention when set. |
| `BACKUP_COLD_ARCHIVE_DISK` | Disk key for monthly archive storage (e.g., `s3-glacier`). |
| `BACKUP_ENCRYPTION_KEY` | 32-byte key used for encrypting database dumps prior to upload. |
| `BACKUP_ALERT_SLACK_WEBHOOK` | Optional webhook that receives failure notifications from the Artisan commands. |
| `DB_BACKUP_CONNECTION` | Connection name to dump (defaults to `mysql`). |
| `MEDIA_BACKUP_DISK` | Disk from which media assets are streamed (defaults to `public`). |

Ensure secrets are loaded in production through the parameter store so rotation does not require redeployments.

## Backup Execution
1. Confirm the environment variables above are present.
2. Run `php artisan system:backup:database --incremental` for adhoc DB snapshots.
3. Run `php artisan system:backup:media --mode=delta` to sync media uploads.
4. Use `php artisan system:backup:verify` to validate checksum manifests and upload integrity.
5. Review the command output for warnings—each command emits JSON logs to `storage/logs/backup.log`.

## Verification Cadence
- **Daily:** `system:backup:verify` runs at 05:00 UTC against the last incremental backup.
- **Weekly:** Automated restore test in staging every Tuesday using `system:restore:database --latest-successful` and `system:restore:media --latest-successful`.
- **Monthly:** Manual drill where the on-call engineer performs a full restore rehearsal and signs off in the operations tracker.

## Restore Procedure
1. **Database First**
   - Identify the snapshot (usually the most recent successful full backup + incrementals).
   - Run `php artisan system:restore:database --backup=<name>`.
   - Monitor the log stream; restoration emits progress updates at 10% increments.
2. **Media Assets**
   - Execute `php artisan system:restore:media --backup=<name>`.
   - Confirm S3 credentials include `s3:ListBucket`, `s3:GetObject`, and `s3:PutObject` for the media bucket.
3. **Cache Bust & Reindex**
   - Flush caches: `php artisan cache:clear`, `php artisan config:clear`, `php artisan route:clear`, `php artisan view:clear`.
   - Rebuild caches: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
   - Trigger search/index rebuilds if applicable: `php artisan scout:sync-indexes`.
4. **Sanity Verification**
   - Run smoke tests (`php artisan test --filter=Smoke`).
   - Validate storefront availability and admin logins.

## Known Pitfalls
- **Expired Credentials:** S3 keys and the `BACKUP_ENCRYPTION_KEY` expire every 90 days—coordinate rotations before expiry to avoid failed jobs.
- **Large Media Archives:** Restores can throttle on >50 GB archives; use the `--chunk-size=2048` option on `system:restore:media` to force multipart downloads.
- **Database Downtime:** Restoring overwrites the primary database; schedule a maintenance window and enable the maintenance banner (`php artisan down --render="maintenance"`).
- **Queue Backlog:** Pause Horizon workers (`php artisan horizon:pause`) before restoring to prevent consumers from writing stale data.

## Escalation Steps
1. **Command Failure Detected**
   - Re-run with `-vvv` for verbose logging.
   - Capture the JSON log from `storage/logs/backup.log` and attach it to the incident ticket.
2. **Notify Stakeholders**
   - Page the DevOps on-call via Slack `#ops-p0`.
   - Email `infra@goleaf.dev` if the failure persists beyond 30 minutes.
3. **Vendor & Cloud Support**
   - For S3 availability incidents, open an AWS support ticket (Enterprise support plan, severity `urgent`).
   - For encryption issues, escalate to the security team (`security@goleaf.dev`) with the KMS key ID.
4. **Document & Follow Up**
   - Log the incident in the operations tracker (Confluence page `OPS-Backups`).
   - Schedule a post-mortem within 48 hours when the outage exceeds 15 minutes.

## Change Log
- Introduced dedicated commands: `system:backup:database`, `system:backup:media`, `system:backup:verify`, `system:backup:prune`, `system:restore:database`, and `system:restore:media`.
- Updated environment dependencies to include backup disks and encryption secrets.
