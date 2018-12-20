#!/bin/sh

#fix link-count, as cron is being a pain, and docker is making hardlink count >0 (very high)
touch /etc/crontab /etc/cron.*/*

service cron start

# Hand off to the CMD
exec "$@"