#!/bin/bash
function crearUsuario(){
    usuario=$1
    echo "Creando usuario... $usuario"

    grep -q $usuario /etc/passwd
    if [ $? -eq 0 ]; then
        mensajeRojo "El usuario $usuario ya existe."
    else
        useradd -m -g wheel $usuario
    fi
}

crearUsuario "pepito1"
crearUsuario "pepito2" 
crearUsuario "pepito3"
