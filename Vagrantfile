# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 sts=2 :

VAGRANTFILE_API_VERSION = "2"

Vagrant.require_version '>= 1.5.1'
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.define 'front1' do |front|
    front.vm.box = 'chef/centos-7.0'
    front.vm.hostname = 'deathstar'
    front.vm.network :private_network, ip: '192.168.50.100'

    front.vm.provision :ansible do |ansible|
      # adjust paths relative to Vagrantfile
      ansible.playbook = './site.yml'
      ansible.groups = {
        'web' => ['default'],
        'development' => ['default']
      }
      ansible.extra_vars = {
        ansible_ssh_user: 'vagrant',
        user: 'vagrant'
      }
      ansible.sudo = true
    end
  end

  config.vm.provider "virtualbox" do |vb|
     # Do not remove it, leave empty
  end

  # Provision with script
  config.vm.provision "shell", path: "docker/provision.sh", privileged: false

end


Vagrant.configure('2') do |config|
  config.vm.box = 'roots/bedrock'

  config.vm.network :private_network, ip: '192.168.50.5'
  config.vm.hostname = 'domain.dev'

  if !Vagrant.has_plugin? 'vagrant-hostsupdater'
    puts 'vagrant-hostsupdater missing, please install the plugin:'
    puts 'vagrant plugin install vagrant-hostsupdater'
  else
    # If you have multiple sites/hosts on a single VM
    # uncomment and add them here
    #config.hostsupdater.aliases = %w(site2.dev)
  end

  # adjust paths relative to Vagrantfile
  # config.vm.synced_folder '.', '/srv/www/domain.dev/current'

  config.vm.provision :ansible do |ansible|
    # adjust paths relative to Vagrantfile
    ansible.playbook = './site.yml'
    ansible.groups = {
      'web' => ['default'],
      'development' => ['default']
    }
    ansible.extra_vars = {
      ansible_ssh_user: 'vagrant',
      user: 'vagrant'
    }
    ansible.sudo = true
  end

  # Fix for slow external network connections
  config.vm.provider :virtualbox do |vb|
    vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
  end
end
