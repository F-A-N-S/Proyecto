#!/bin/bash
clear
set -euo pipefail
CONFIG="/etc/ssh/sshd_config"
if [[ $EUID -ne 0 ]]; then
	echo "Ejecuta con sudo: sudo $0"
	exit 1
fi
cp -a "$CONFIG" "${CONFIG}.bak.$(date +%F_%H%M%S)"
sed -i \
	-e 's/^#\?Port .*/Port 2222/' \
	-e 's/^#\?PermitRootLogin .*/PermitRootLogin no/' \
	-e 's/^#\?PasswordAuthentication .*/PasswordAuthentication yes/' \
	-e 's/^#\?PubkeyAuthentication .*/PubkeyAuthentication no/' \
	-e 's/^#\?ChallengeResponseAuthentication .*/ChallengeResponseAuthentication no/' \
	-e 's/^#\?UsePAM .*/UsePAM yes/' \
	"$CONFIG"
grep -q '^AddressFamily ' "$CONFIG" || echo 'AdressFamily any' >> "$CONFIG"
grep -q '^ListenAddress 0.0.0.0' "$CONFIG" || echo 'ListenAddress 0.0.0.0' >> "$CONFIG"
grep -q '^ListenAddress ::' "$CONFIG" || echo 'ListenAdress ::' >> "$CONFIG"
systemctl restart ssh || systemctl restart sshd || true
if command -v ufw >/dev/null 2>&1; then
	ufw status | grep -q "2222/tcp" || ufw allow 2222/tcp || true
fi
echo "SSH LISTO: Puerto 2222, Password Habilitada, Root Login Deshabilitado, Sin Claves Publicas."
echo "Probar: ssh -p 2222 <usuario>@<ip_del_servidor>"
echo "Prueba de ejemplo con usuario ggmartinez"
ssh -p 2222 ggmartinez@192.168.1.10
