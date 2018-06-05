#!/bin/bash
cd $(dirname `readlink -f $0`)

sudo chown -R zhengkai:zhengkai /home/zhengkai

sudo cp ../file/sudoers_nopassword /etc/sudoers.d/nopassword

mkdir -p ~/.tmp
mkdir -p ~/.backup
mkdir -p ~/.Trash
touch ~/.backup/.wget_cookie
touch ~/.backup/.curl_cookie

sudo apt-get update

sudo apt-get install -y vim
sudo update-alternatives --set editor /usr/bin/vim.basic

sudo sh -c 'echo "Asia/Shanghai" > /etc/timezone'
sudo cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

list=(`cat list-aptget`)
sudo apt-get install -y ${list[@]}

sudo apt-get remove -y fonts-droid

sudo easy_install3 -U pip

sudo -H pip install --upgrade httpie
sudo -H pip install --upgrade qrcode
sudo -H pip install --upgrade tldr
sudo -H pip3 install --upgrade thefuck

sudo adduser zhengkai www-data

sudo chown -R zhengkai:zhengkai /home/zhengkai

if [ "$HOSTNAME" == 'Tesla' ]; then
	./desktop.sh
fi

../npm.sh
