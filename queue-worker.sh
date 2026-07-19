#!/usr/bin/env bash
#
# Keeps a queue worker alive on shared hosting.
#
# `queue:work` handles ordinary job failures on its own, but a fatal error —
# a memory limit hit inside a vendor library, for instance — takes the whole
# PHP process down. Run by hand, that means the queue silently stops until
# someone notices and starts it again.
#
# This restarts the worker whenever it exits, so one bad file cannot halt
# every other job behind it.
#
# Usage:
#   bash queue-worker.sh                 # foreground
#   nohup bash queue-worker.sh &         # background, survives logout
#
# Stop with:  pkill -f queue-worker.sh

cd "$(dirname "$0")" || exit 1

LOG="storage/logs/queue-worker.log"
mkdir -p storage/logs

# --max-time recycles the process every hour so leaked memory cannot build up
#   across jobs.
# --tries=1 keeps a failed job from being retried; extraction and pricing are
#   both expensive and non-idempotent.
# --timeout must stay below the longest job's own timeout.
while true; do
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] starting worker" >> "$LOG"

    php artisan queue:work \
        --tries=1 \
        --timeout=7200 \
        --max-time=3600 \
        --sleep=3 >> "$LOG" 2>&1

    code=$?
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] worker exited (code $code); restarting in 5s" >> "$LOG"

    # A short pause, so a worker that dies instantly cannot spin the CPU.
    sleep 5
done
