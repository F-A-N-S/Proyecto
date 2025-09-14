#!/bin/bash
function agregarRepositorioEpel(){
    if [ -e /etc/yum.repos.d/epel.repo ]
    then 
        echo "El repositorio EPEL ya está instalado."
    else
        echo "Instalando repositorio EPEL..."
        dnf install -y epel-release
    fi
    
}

function instalarPaquetes(){
        dnf install -y vim htop git wget curl
}

agregarRepositorioEpel
instalarPaquetes
