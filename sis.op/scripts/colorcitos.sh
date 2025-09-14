#!/bin/bash
reset="\e[0m"
rojo="\e[31m"
azul="\e[34m"
amarillo="\e[33m"
blanco="\e[37m"
gris="\e[90m"


function mensajes(){
    if [ -z "$2" ]
    then
        color=$rojo
    else 
        color=${!2}
    fi
    echo -e "${color}${1}${reset}"
}

function mensajeVerde(){
    echo -e "${verde}${1}${reset}"
}

function mensajeRojo(){
    echo -e "${rojo}${1}${reset}"
}

function mensajeAzul(){
    echo -e "${azul}${1}${reset}"
}
