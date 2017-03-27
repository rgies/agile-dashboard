#!/bin/sh

ROOTPATH=`pwd`/`dirname $0`/..

sudo rm -rf $ROOTPATH/app/cache/*
sudo rm -rf $ROOTPATH/app/logs/*

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
if [ "$HTTPDUSER" == "" ]; then
    echo '!!! Webserver process not found, please check if your webserver is running and try again.'
    exit
fi

echo "Set file permitions to $HTTPDUSER and owner to `whoami` !"
sudo chown -R "`whoami`" $ROOTPATH
sudo chmod 0777 $ROOTPATH/app/console
sudo chmod -R +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" $ROOTPATH/app $ROOTPATH/src $ROOTPATH/web $ROOTPATH/vendor
sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" $ROOTPATH/app/cache $ROOTPATH/app/logs
