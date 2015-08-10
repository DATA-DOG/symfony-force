# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 sts=2 :

VAGRANTFILE_API_VERSION = "2"

Vagrant.require_version '>= 1.7.0'
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.define 'front1' do |front|
    front.vm.box = 'chef/centos-7.0'
    front.vm.hostname = 'deathstar'
    front.vm.network :private_network, ip: '192.168.50.100'
  end

  # Fix for slow external network connections
  config.vm.provider :virtualbox do |vb|
    vb.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    vb.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
    vb.memory = 1024
    vb.cpus = 2
  end

  config.vm.provision :ansible do |ansible|
    ansible.playbook = 'ansible/playbook.yml'
    ansible.inventory_path = 'ansible/development'
  end
end

