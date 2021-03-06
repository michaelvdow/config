#!/bin/bash

echo

if [ "$UID" -ne 0 ]; then
	>&2 echo This script must be run as root
	exit 1
fi

if [ -z "$1" ]; then
	>&2 echo no group name
	exit 1
fi

GROUP="$(getent group $1)"
if [ -z "$GROUP" ]; then
	>&2 echo 'no group "'$1'"'
	exit 1
fi

if [ -z "$2" ]; then
	>&2 echo 'no dictionary'
	exit 1
fi

if [ ! -e "$2" ]; then
	>&2 echo 'dictionary "'$2'" no exists'
	exit 1
fi
if [ ! -d "$2" ]; then
	>&2 echo 'not a dictionary "'$2'"'
	exit 1
fi

echo -e "\tset acl\n\n\tgroup: ${1}\n\t  dir: ${2}\n"

cd "$2"
pwd
echo

set -x
setfacl -d -R -m "g:${1}:rwx" "$2"
setfacl    -R -m "g:${1}:rwX" "$2"
