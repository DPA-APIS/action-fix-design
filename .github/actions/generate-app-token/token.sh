#!/bin/bash

set -e

# Colores para output
RED='\033[31m'
GREEN='\033[32m'
YELLOW='\033[33m'
RESET='\033[0m'

# Parámetros
APP_ID="$1"
PRIVATE_KEY="$2"
ORGANIZATION="$3"

# Validar parámetros
if [ -z "$APP_ID" ] || [ -z "$PRIVATE_KEY" ] || [ -z "$ORGANIZATION" ]; then
    echo -e "${RED}❌ Error: Faltan parámetros requeridos${RESET}"
    echo "Uso: $0 <APP_ID> <PRIVATE_KEY> <ORGANIZATION>"
    exit 1
fi

echo -e "${YELLOW}🔑 Iniciando generación de token JWT...${RESET}"

# Verificar que la clave privada no esté vacía
if [ -z "$PRIVATE_KEY" ]; then
    echo -e "${RED}❌ Error: La clave privada está vacía${RESET}"
    exit 1
fi

# Generar timestamps
now=$(date +%s)
iat=$((now - 60))  # Emitido hace 60 segundos
exp=$((now + 600)) # Expira en 10 minutos

# Función para codificación base64
b64enc() { 
    openssl base64 -e -A | tr -d '=' | tr '/+' '_-' | tr -d '\n'; 
}

# Crear header JWT
header_json='{
    "typ":"JWT",
    "alg":"RS256"
}'
header=$(echo -n "${header_json}" | b64enc)

# Crear payload JWT
payload_json="{
    \"iat\":${iat},
    \"exp\":${exp},
    \"iss\":\"${APP_ID}\"
}"
payload=$(echo -n "${payload_json}" | b64enc)

# Firmar JWT
header_payload="${header}.${payload}"

# Guardar clave privada en archivo temporal
echo -n "${PRIVATE_KEY}" > /tmp/private-key.pem

# Verificar que openssl puede leer la clave
if ! openssl rsa -in /tmp/private-key.pem -check -noout 2>/dev/null; then
    echo -e "${RED}❌ Error: No se puede leer la clave privada${RESET}"
    rm -f /tmp/private-key.pem
    exit 1
fi

# Generar firma
signature=$(echo -n "${header_payload}" | openssl dgst -sha256 -sign /tmp/private-key.pem | b64enc)

# Limpiar archivo temporal
rm -f /tmp/private-key.pem

# Crear JWT completo
jwt_token="${header_payload}.${signature}"

echo -e "${GREEN}✅ JWT generado exitosamente${RESET}"

# Obtener instalaciones de la GitHub App
echo -e "${YELLOW}🔍 Obteniendo instalaciones de la GitHub App...${RESET}"
installations=$(curl -s -H "Authorization: Bearer $jwt_token" \
                     -H "Accept: application/vnd.github.v3+json" \
                     https://api.github.com/app/installations)

# Verificar respuesta de instalaciones
if [ -z "$installations" ] || [ "$installations" = "null" ]; then
    echo -e "${RED}❌ Error: No se pudieron obtener las instalaciones${RESET}"
    exit 1
fi

# Encontrar ID de instalación para la organización
installation_id=$(echo "$installations" | jq -r --arg org "$ORGANIZATION" '.[] | select(.account.login == $org) | .id')

if [ -z "$installation_id" ] || [ "$installation_id" = "null" ]; then
    echo -e "${RED}❌ Error: No se encontró instalación para la organización '$ORGANIZATION'${RESET}"
    echo -e "${YELLOW}Instalaciones disponibles:${RESET}"
    echo "$installations" | jq -r '.[].account.login' || echo "No se pudieron listar las instalaciones"
    exit 1
fi

echo -e "${GREEN}✅ ID de instalación encontrado: $installation_id${RESET}"

# Obtener token de acceso
echo -e "${YELLOW}🎫 Generando token de acceso...${RESET}"
access_token_response=$(curl -s -X POST \
                            -H "Authorization: Bearer $jwt_token" \
                            -H "Accept: application/vnd.github.v3+json" \
                            https://api.github.com/app/installations/$installation_id/access_tokens)

access_token=$(echo "$access_token_response" | jq -r '.token')

if [ -z "$access_token" ] || [ "$access_token" = "null" ]; then
    echo -e "${RED}❌ Error: No se pudo obtener el token de acceso${RESET}"
    echo "Respuesta de la API: $access_token_response"
    exit 1
fi

echo -e "${GREEN}🎉 Token de acceso generado exitosamente${RESET}"

# Exportar outputs
echo "token=$access_token" >> $GITHUB_OUTPUT
echo "installation_id=$installation_id" >> $GITHUB_OUTPUT