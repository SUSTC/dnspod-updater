#/bin/sh

# Get IP address
echo "Getting Public IP address, Please wait a moment..."
IP=`curl -s checkip.dyndns.com | cut -d' ' -f 6  | cut -d'<' -f 1`
if [ $? -ne 0 -o -z $IP ]; then
    IP=`curl -s -4 ipinfo.io | grep "ip" | awk -F\" '{print $4}'`
fi
echo -e "Your main public IP is\t\033[32m$IP\033[0m"
echo ""

cd `dirname $0`
python update_ip.py $IP
