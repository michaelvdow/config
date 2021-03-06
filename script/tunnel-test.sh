#!/bin/bash

echo

if [[ -z "$1" ]]; then
	echo input port what you want test
	exit
fi

TUNNEL="$1"

if [[ "$TUNNEL" =~ ^[0-9]+$ ]]; then
	TUNNEL="127.0.0.1:$TUNNEL"
fi

echo test $TUNNEL

function checkip() {
	local IP=`TIME="\ntime: %E" time curl -s --max-time 10 "$1" --socks5-hostname "$2"`
	echo
	echo "  ip: $IP"

	if [[ -n "$IP" ]]; then
		echo
		geoiplookup "$IP"
	fi
}

checkip "https://soulogic.com/ip" "$TUNNEL"
checkip "http://ifconfig.io/ip"   "$TUNNEL"
