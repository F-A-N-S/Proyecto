#!/bin/bash
function apagarFirewall(){
    systemctl stop firewalld
    systemctl disable firewalld
}

apagarFirewall
