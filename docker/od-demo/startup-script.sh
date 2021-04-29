#!/bin/bash -e

# This is a script to get OD up and running on a GCP free tier server.
# It should be saved to a storage bucket, e.g. in the gsl-devops project.
# When a new user signs up to GCP, a new project is set up by default.
# They can then spin up a free tier virtual machine by running the following
# command in Google Cloud Shell.

# gcloud compute instances create "od-demo" \
#   --boot-disk-auto-delete \
#   --boot-disk-device-name "od-demo" \
#   --boot-disk-size 30 \
#   --boot-disk-type pd-standard \
#   --description "OD Demo Server" \
#   --image-family ubuntu-2004-lts \
#   --image-project ubuntu-os-cloud \
#   --machine-type f1-micro \
#   --maintenance-policy MIGRATE \
#   --metadata startup-script-url="https://storage.googleapis.com/gsl-od-demo-startup-script/startup-script.sh" \
#   --project splendid-planet-310815 \
#   --scopes cloud-platform \
#   --tags http-server,https-server \
#   --zone us-west1-a

# Function to display a message on stdout with a timestamp.
log() {
  echo "`date "+%Y/%m/%d %H:%M:%S"` $1"
}

log "Info: Running startup-script.sh..."
log "Info: Sleep for 5 mins to give the server time to spin up."
# May not be necessary. Come back to this when the script is finished.
sleep 300
log "Info: Continuing startup-script.sh..."

# Update the apt package list.
sudo DEBIAN_FRONTEND=noninteractive apt-get update -y
# Upgrade the linux distribution.
sudo DEBIAN_FRONTEND=noninteractive apt-get upgrade -y


log "Info: Install git."
sudo add-apt-repository -y ppa:git-core/ppa
sudo apt -y update
sudo apt -y install git

log "Info: Configure the git user."
sudo git config --system user.name "No Body"
sudo git config --system user.email "email@email.com"


log "Info: Install Docker."
# Install Docker's package dependencies.
sudo apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    software-properties-common

# Download and add Docker's official public PGP key.
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

# Verify the fingerprint.
sudo apt-key fingerprint 0EBFCD88

# Add the stable channel's Docker upstream repository.
sudo add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable"

# Update the apt package list (for the new apt repo).
sudo apt update -y

# Install the latest version of Docker CE.
sudo apt install -y docker-ce

# Allow your user to access the Docker CLI without needing root access by adding
# the user to the docker gr
sudo usermod -aG docker "$USER"

log "Info: Install docker-compose."
sudo apt install -y docker-compose

log "Info: Clone the OpenDialog repository."
mkdir -p ~/code
cd ~/code
git clone --depth 1 https://github.com/opendialogai/opendialog.git -b 0.7.x

# Set up a swap file as these containers are quite memory hungry.
# sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=4096
# sudo /sbin/mkswap /var/swap.1
# sudo /sbin/swapon /var/swap.1
# sudo chown root:root /var/swap.1
# sudo chmod 600 /var/swap.1

log "Info: Spin up OpenDialog."
cd opendialog/docker/od-demo
cp .env.example .env
#sudo docker-compose up -d app
#sudo docker-compose exec app bash docker/od-demo/update-docker.sh


# 27/04/2021 The above worked! OD is served at the VM IP address.
# Note that I set up the swap file manually by SSHing into the VM. I've been
# asked to pause this work as the VM spin up time takes a while. So, I didn't
# get a chance to finish the script. Another thing to add would be the opening
# up of the firewall ports, as I had to do this manually in the console aswell.

log "Info: Docker output:"
sudo docker ps

log "Info: Installed versions:"
log "Info: Ubuntu version: $(lsb_release -a)"
log "Info: git: $(git --version)"
log "Info: docker CLI: $(docker --version)"
log "Info: docker-compose: $(docker-compose --version)"
