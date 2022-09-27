#!/bin/bash

# Ganti baris ini saja
JUMLAH_ONESENDER=10

## Di bawah ini tidak perlu diganti

FILEVERSION=/opt/onesender/version
if [ ! -f "$FILEVERSION" ]; then
echo "LOCAL_VERSION=2.0.0" > $FILEVERSION
fi
source "$FILEVERSION"
VERSION=`curl -ksL https://onesender.net/apps/onesender-latest-version/`

echo "Local Version  : $LOCAL_VERSION"
echo "Latest Version : $VERSION"

if [ "$LOCAL_VERSION" != "$VERSION" ]; then
	echo "do update"
	sudo systemctl stop onesender@1
	/opt/onesender/onesender-x86_64 -c /opt/onesender/config_1.yaml --upgrade
	/opt/onesender/onesender-x86_64 -c /opt/onesender/config_1.yaml --update
	sudo systemctl start onesender@1
	sleep 1

	if [ "$JUMLAH_ONESENDER" -gt "1" ]; then
		echo "do banyak update"

		START=1
		for (( c=$START; c<=$JUMLAH_ONESENDER; c++ ))
		do
			echo "config_${c}.yaml"
			sudo systemctl stop "onesender@${c}"
			/opt/onesender/onesender-x86_64 -c "/opt/onesender/config_${c}.yaml" --update
			sudo systemctl start "onesender@${c}"
			sleep 1
		done


	fi
else
	echo "Aplikasi sudah versi terbaru"
fi
