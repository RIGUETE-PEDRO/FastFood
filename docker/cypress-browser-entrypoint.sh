#!/bin/sh
set -eu

export DISPLAY=:99

Xvfb "$DISPLAY" -screen 0 1440x900x24 -ac -nolisten tcp >/tmp/xvfb.log 2>&1 &
sleep 2
fluxbox >/tmp/fluxbox.log 2>&1 &
x11vnc -display "$DISPLAY" -forever -shared -nopw -rfbport 5900 -listen 0.0.0.0 >/tmp/x11vnc.log 2>&1 &
/usr/share/novnc/utils/novnc_proxy --vnc localhost:5900 --listen 6080 >/tmp/novnc.log 2>&1 &

cd /app
npm ci
exec npx cypress open --project /app --e2e --browser electron
