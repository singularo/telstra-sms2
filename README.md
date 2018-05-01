## telstra-sms2
* Send SMS messages using the new Telstra API using a docker container
* Visit https://dev.telstra.com/ for more info

### Setup
* Grab a copy of sms2_send.sh.example and name it whatever you want.
* Adjust the parameters to use your telstra client id and secret
* Call the script when you want to send an SMS

### Build the docker image locally
docker build -t singularo/telstra-sms2 code/
